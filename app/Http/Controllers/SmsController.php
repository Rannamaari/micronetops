<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\SmsMessage;
use App\Services\SmsMessageSender;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SmsController extends Controller
{
    public function index()
    {
        $recent = SmsMessage::with('user:id,name')
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        return view('sms.index', [
            'recent' => $recent,
            'defaultSource' => (string) config('services.dhiraagu_sms.source', 'Micronet'),
            'dryRun' => (bool) config('services.dhiraagu_sms.dry_run', false),
        ]);
    }

    /**
     * GET /sms/customers/search?q=...
     * Lightweight search for the SMS picker (session-auth).
     */
    public function searchCustomers(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        if ($q === '') {
            return response()->json([
                'total' => 0,
                'data' => [],
            ]);
        }

        $s = mb_strtolower($q);
        $customers = Customer::query()
            ->where(function ($query) use ($s) {
                $query->whereRaw('lower(name) like ?', ["%{$s}%"])
                    ->orWhereRaw('lower(phone) like ?', ["%{$s}%"])
                    ->orWhereRaw('lower(email) like ?', ["%{$s}%"])
                    ->orWhereRaw('lower(address) like ?', ["%{$s}%"]);
            })
            ->orderBy('name')
            ->limit(30)
            ->get(['id', 'name', 'phone', 'email', 'address']);

        return response()->json([
            'total' => $customers->count(),
            'data' => $customers,
        ]);
    }

    /**
     * GET /sms/customers/all
     * Returns a full list of customers for the "All Customers" SMS audience.
     */
    public function allCustomers(Request $request): \Illuminate\Http\JsonResponse
    {
        $customers = $this->filteredCustomersQuery($request)
            ->orderBy('name')
            ->limit(2000)
            ->get(['id', 'name', 'phone', 'email', 'address', 'category', 'created_at']);

        return response()->json([
            'total' => $customers->count(),
            'data' => $customers,
        ]);
    }

    public function send(Request $request, SmsMessageSender $sender)
    {
        $validated = $request->validate([
            'audience' => ['required', 'in:manual,all_customers'],
            'source' => ['nullable', 'string', 'max:50'],
            'content' => ['required', 'string', 'max:1000'],
            'delivery_timing' => ['required', 'in:now,later'],
            'scheduled_for' => ['nullable', 'date', 'required_if:delivery_timing,later', 'after:now'],
            'numbers' => ['nullable', 'string', 'max:50000'],
            'exclude_customer_ids' => ['nullable', 'string', 'max:50000'],
            'customer_category_filter' => ['nullable', 'in:all,moto,ac,it,easyfix'],
            'customer_added_month' => ['nullable', 'date_format:Y-m'],
        ]);

        $content = trim((string) $validated['content']);
        $source = isset($validated['source']) ? trim((string) $validated['source']) : null;
        $audience = (string) $validated['audience'];
        $deliveryTiming = (string) $validated['delivery_timing'];

        [$destinations, $invalid] = $this->resolveDestinations($request, $validated);

        if (count($destinations) === 0) {
            return back()->withInput()->with('error', 'No valid phone numbers found to send SMS.');
        }

        $log = SmsMessage::create([
            'user_id' => auth()->id(),
            'audience' => $audience,
            'status' => $deliveryTiming === 'later' ? SmsMessage::STATUS_SCHEDULED : SmsMessage::STATUS_DRAFT,
            'source' => $source ?: (string) config('services.dhiraagu_sms.source'),
            'content' => $content,
            'scheduled_for' => $deliveryTiming === 'later'
                ? Carbon::parse((string) $validated['scheduled_for'])
                : null,
            'destinations' => $destinations,
            'destinations_count' => count($destinations),
            'invalid_destinations' => $invalid,
            'invalid_count' => count($invalid),
            'responses' => null,
            'sent_count' => 0,
            'failed_count' => 0,
            'error_message' => null,
            'sent_at' => null,
        ]);

        if ($deliveryTiming === 'later') {
            ActivityLog::record(
                'sms.scheduled',
                'SMS scheduled (' . $audience . ') for ' . $log->scheduled_for?->format('Y-m-d H:i'),
                $log
            );

            return redirect()
                ->route('sms.index')
                ->with('success', 'SMS scheduled for ' . $log->scheduled_for?->format('d M Y, h:i A') . '.');
        }

        $result = $sender->send($log);

        return redirect()
            ->route('sms.index')
            ->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    public function cancel(SmsMessage $smsMessage)
    {
        if (!$smsMessage->isScheduled()) {
            return back()->with('error', 'Only scheduled SMS can be cancelled.');
        }

        $smsMessage->update([
            'status' => SmsMessage::STATUS_CANCELLED,
        ]);

        ActivityLog::record(
            'sms.cancelled',
            'Scheduled SMS #' . $smsMessage->id . ' cancelled',
            $smsMessage
        );

        return back()->with('success', 'Scheduled SMS cancelled.');
    }

    /**
     * Split a phone field into tokens (handles comma, slash, newline, etc.).
     *
     * @return array<int,string>
     */
    private function extractPhoneTokens(string $raw): array
    {
        $raw = trim($raw);
        if ($raw === '') return [];

        $parts = preg_split('/[\\n,;\\/|]+/', $raw) ?: [];
        $tokens = [];
        foreach ($parts as $p) {
            $t = trim((string) $p);
            if ($t !== '') $tokens[] = $t;
        }
        return $tokens;
    }

    /**
     * Normalize to Dhiraagu destination format:
     * - 7-digit local number => prefix 960
     * - 960XXXXXXX (10 digits) accepted
     */
    private function normalizeDestination(string $token): ?string
    {
        $token = trim($token);
        if ($token === '') {
            return null;
        }

        if (preg_match('/[[:alpha:]]/u', $token)) {
            return null;
        }

        if (!preg_match('/^[0-9\\s()+-]+$/', $token)) {
            return null;
        }

        $digits = preg_replace('/\\D+/', '', $token);
        $digits = (string) $digits;
        if ($digits === '') return null;

        if (strlen($digits) === 7) {
            $digits = '960' . $digits;
        }

        if (strlen($digits) === 10 && str_starts_with($digits, '960')) {
            return $digits;
        }

        return null;
    }

    private function filteredCustomersQuery(Request $request)
    {
        $query = Customer::query();

        $category = (string) $request->input('customer_category_filter', 'all');
        if ($category !== '' && $category !== 'all') {
            $query->where('category', $category);
        }

        $addedMonth = trim((string) $request->input('customer_added_month', ''));
        if ($addedMonth !== '') {
            try {
                $month = Carbon::createFromFormat('Y-m', $addedMonth)->startOfMonth();
                $query->whereBetween('created_at', [
                    $month->copy()->startOfMonth(),
                    $month->copy()->endOfMonth(),
                ]);
            } catch (\Throwable $e) {
                // Validation already guards the format; ignore invalid values defensively.
            }
        }

        return $query;
    }

    /**
     * @return array{0: array<int,string>, 1: array<int,string>}
     */
    private function resolveDestinations(Request $request, array $validated): array
    {
        $audience = (string) $validated['audience'];
        $destinations = [];
        $invalid = [];

        if ($audience === 'manual') {
            $raw = (string) ($validated['numbers'] ?? '');
            foreach ($this->extractPhoneTokens($raw) as $token) {
                $n = $this->normalizeDestination($token);
                if ($n) {
                    $destinations[] = $n;
                } else {
                    $invalid[] = $token;
                }
            }

            return [array_values(array_unique($destinations)), $invalid];
        }

        $excludedIds = [];
        $excludeRaw = trim((string) ($validated['exclude_customer_ids'] ?? ''));
        if ($excludeRaw !== '') {
            foreach (preg_split('/[\\s,;]+/', $excludeRaw) ?: [] as $p) {
                $id = (int) trim((string) $p);
                if ($id > 0) {
                    $excludedIds[$id] = true;
                }
            }
        }

        foreach ($this->filteredCustomersQuery($request)->select('id', 'phone')->cursor() as $customer) {
            if (!empty($excludedIds[(int) $customer->id])) {
                continue;
            }
            foreach ($this->extractPhoneTokens((string) $customer->phone) as $token) {
                $n = $this->normalizeDestination($token);
                if ($n) {
                    $destinations[] = $n;
                } elseif (count($invalid) < 50) {
                    $invalid[] = $token;
                }
            }
        }

        return [array_values(array_unique($destinations)), $invalid];
    }
}
