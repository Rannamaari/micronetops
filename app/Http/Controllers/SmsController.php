<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\SmsMessage;
use App\Services\DhiraaguSmsClient;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SmsController extends Controller
{
    public function index()
    {
        $recent = SmsMessage::with('user:id,name')
            ->orderByDesc('id')
            ->limit(25)
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

    public function send(Request $request, DhiraaguSmsClient $sms)
    {
        $validated = $request->validate([
            'audience' => ['required', 'in:manual,all_customers'],
            'source' => ['nullable', 'string', 'max:50'],
            'content' => ['required', 'string', 'max:1000'],
            'numbers' => ['nullable', 'string', 'max:50000'],
            'exclude_customer_ids' => ['nullable', 'string', 'max:50000'],
            'customer_category_filter' => ['nullable', 'in:all,moto,ac,it,easyfix'],
            'customer_added_month' => ['nullable', 'date_format:Y-m'],
        ]);

        $content = trim((string) $validated['content']);
        $source = isset($validated['source']) ? trim((string) $validated['source']) : null;
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
        } else {
            $excludedIds = [];
            $excludeRaw = trim((string) ($validated['exclude_customer_ids'] ?? ''));
            if ($excludeRaw !== '') {
                foreach (preg_split('/[\\s,;]+/', $excludeRaw) ?: [] as $p) {
                    $id = (int) trim((string) $p);
                    if ($id > 0) $excludedIds[$id] = true;
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
                    } else {
                        // keep only a small sample of invalids to avoid huge logs
                        if (count($invalid) < 50) {
                            $invalid[] = $token;
                        }
                    }
                }
            }
        }

        $destinations = array_values(array_unique($destinations));

        if (count($destinations) === 0) {
            return back()->withInput()->with('error', 'No valid phone numbers found to send SMS.');
        }

        $log = SmsMessage::create([
            'user_id' => auth()->id(),
            'audience' => $audience,
            'source' => $source ?: (string) config('services.dhiraagu_sms.source'),
            'content' => $content,
            'destinations' => $destinations,
            'destinations_count' => count($destinations),
            'invalid_destinations' => $invalid,
            'invalid_count' => count($invalid),
            'responses' => null,
            'sent_count' => 0,
            'failed_count' => 0,
            'sent_at' => null,
        ]);

        try {
            $result = $sms->send($destinations, $content, $source);
        } catch (\Throwable $e) {
            $log->update([
                'responses' => [[
                    'transactionId' => null,
                    'transactionStatus' => 'false',
                    'transactionDescription' => $e->getMessage(),
                    'referenceNumber' => '',
                ]],
                'failed_count' => count($destinations),
                'sent_at' => now(),
            ]);

            return back()->withInput()->with('error', 'SMS failed: ' . $e->getMessage());
        }

        $log->update([
            'responses' => $result['responses'],
            'sent_count' => (int) $result['sent'],
            'failed_count' => (int) $result['failed'],
            'sent_at' => now(),
        ]);

        ActivityLog::record(
            'sms.sent',
            'SMS sent (' . $audience . '): ' . $result['sent'] . ' sent, ' . $result['failed'] . ' failed',
            $log
        );

        $msg = $result['ok']
            ? 'SMS sent successfully. Sent: ' . $result['sent'] . ', Failed: ' . $result['failed']
            : 'SMS sent with errors. Sent: ' . $result['sent'] . ', Failed: ' . $result['failed'];

        return redirect()
            ->route('sms.index')
            ->with($result['ok'] ? 'success' : 'error', $msg);
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
}
