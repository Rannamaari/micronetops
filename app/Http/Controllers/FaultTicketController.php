<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\FaultTicket;
use App\Models\Job;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FaultTicketController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = FaultTicket::with(['customer', 'assignee', 'creator']);

        // Business unit filter — mechanics see their own unit only
        $unitFilter = $request->get('unit');
        if ($unitFilter) {
            $query->forUnit($unitFilter);
        } elseif ($user->allowedBusinessUnit()) {
            $query->forUnit($user->allowedBusinessUnit());
        }

        // Status tab filter
        $tab = $request->get('tab', 'open');
        switch ($tab) {
            case 'open':
                $query->where('status', 'open');
                break;
            case 'in_progress':
                $query->where('status', 'in_progress');
                break;
            case 'overdue':
                $query->overdue();
                break;
            case 'resolved':
                $query->where('status', 'resolved');
                break;
            case 'closed':
                $query->where('status', 'closed');
                break;
            case 'all':
                break;
            default:
                $query->where('status', 'open');
        }

        // Priority filter
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Search
        if ($request->filled('search')) {
            $s = mb_strtolower($request->search);
            $query->where(function ($q) use ($s) {
                $q->whereRaw('lower(ticket_number) like ?', ["%{$s}%"])
                  ->orWhereRaw('lower(customer_name) like ?', ["%{$s}%"])
                  ->orWhereRaw('lower(customer_phone) like ?', ["%{$s}%"])
                  ->orWhereRaw('lower(title) like ?', ["%{$s}%"]);
            });
        }

        $tickets = $query->latest()->paginate(20)->withQueryString();

        // Counts for tab badges (apply same unit filter)
        $countBase = FaultTicket::query();
        if ($unitFilter) {
            $countBase->forUnit($unitFilter);
        } elseif ($user->allowedBusinessUnit()) {
            $countBase->forUnit($user->allowedBusinessUnit());
        }

        $counts = [
            'open' => (clone $countBase)->where('status', 'open')->count(),
            'in_progress' => (clone $countBase)->where('status', 'in_progress')->count(),
            'overdue' => (clone $countBase)->overdue()->count(),
            'resolved' => (clone $countBase)->where('status', 'resolved')->count(),
            'closed' => (clone $countBase)->where('status', 'closed')->count(),
            'all' => (clone $countBase)->count(),
        ];

        return view('faults.index', compact('tickets', 'counts', 'tab'));
    }

    public function create(Request $request)
    {
        $user = auth()->user();
        $users = User::whereIn('role', ['admin', 'manager', 'moto_mechanic', 'ac_mechanic'])
            ->orderBy('name')
            ->get();

        $customers = Customer::orderBy('name')->limit(50)->get();

        // Pre-fill customer if passed via query string
        $selectedCustomer = null;
        if ($request->filled('customer_id')) {
            $selectedCustomer = Customer::find($request->customer_id);
        }

        return view('faults.create', compact('users', 'customers', 'selectedCustomer'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'business_unit' => ['required', Rule::in(['moto', 'cool'])],
            'priority' => ['required', Rule::in(['urgent', 'normal'])],
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:255',
            'customer_id' => 'nullable|exists:customers,id',
            'job_id' => 'nullable|exists:jobs,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $deadlineHours = $validated['priority'] === 'urgent' ? 24 : 48;

        $ticket = FaultTicket::create([
            ...$validated,
            'status' => 'open',
            'ticket_number' => 'FT-TEMP', // Will be updated in boot
            'deadline_at' => Carbon::now()->addHours($deadlineHours),
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('faults.show', $ticket)
            ->with('success', "Fault ticket {$ticket->ticket_number} created successfully.");
    }

    public function show(FaultTicket $faultTicket)
    {
        $faultTicket->load(['customer', 'job', 'creator', 'assignee', 'resolver']);

        $users = User::whereIn('role', ['admin', 'manager', 'moto_mechanic', 'ac_mechanic'])
            ->orderBy('name')
            ->get();

        return view('faults.show', compact('faultTicket', 'users'));
    }

    public function update(Request $request, FaultTicket $faultTicket)
    {
        $action = $request->input('action');

        switch ($action) {
            case 'assign':
                $request->validate(['assigned_to' => 'required|exists:users,id']);
                $faultTicket->update(['assigned_to' => $request->assigned_to]);
                $message = 'Ticket assigned successfully.';
                break;

            case 'start_work':
                $faultTicket->update([
                    'status' => 'in_progress',
                    'assigned_to' => $faultTicket->assigned_to ?? auth()->id(),
                ]);
                $message = 'Work started on ticket.';
                break;

            case 'resolve':
                $request->validate(['resolution_notes' => 'required|string']);
                $faultTicket->update([
                    'status' => 'resolved',
                    'resolution_notes' => $request->resolution_notes,
                    'resolved_at' => Carbon::now(),
                    'resolved_by' => auth()->id(),
                ]);
                $message = 'Ticket resolved.';
                break;

            case 'close':
                if (!auth()->user()->hasAnyRole(['admin', 'manager'])) {
                    abort(403);
                }
                $faultTicket->update(['status' => 'closed']);
                $message = 'Ticket closed.';
                break;

            case 'reopen':
                $faultTicket->update([
                    'status' => 'open',
                    'resolved_at' => null,
                    'resolved_by' => null,
                    'resolution_notes' => null,
                ]);
                $message = 'Ticket reopened.';
                break;

            default:
                return back()->with('error', 'Unknown action.');
        }

        return back()->with('success', $message);
    }

    public function customerJobs(Customer $customer)
    {
        $jobs = Job::where('customer_id', $customer->id)
            ->orderByDesc('id')
            ->limit(30)
            ->get(['id', 'job_type', 'job_category', 'status', 'problem_description', 'created_at']);

        return response()->json([
            'jobs' => $jobs->map(fn ($j) => [
                'id' => $j->id,
                'label' => "#{$j->id} — " . ucfirst($j->job_type) . ' ' . ucfirst(str_replace('_', ' ', $j->job_category))
                    . ($j->problem_description ? ' — ' . \Illuminate\Support\Str::limit($j->problem_description, 40) : ''),
                'job_type' => $j->job_type,
            ]),
        ]);
    }

    public function destroy(FaultTicket $faultTicket)
    {
        $number = $faultTicket->ticket_number;
        $faultTicket->delete();

        return redirect()->route('faults.index')
            ->with('success', "Ticket {$number} deleted.");
    }
}
