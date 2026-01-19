<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\AcUnit;
use App\Models\User;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class JobController extends Controller
{
    /**
     * List recent jobs with enhanced filtering.
     */
    public function index(Request $request)
    {
        $view = $request->query('view', 'current'); // current | completed | my_jobs
        $type = $request->query('type'); // ac | moto
        $priority = $request->query('priority');
        $search = $request->query('search');
        $when = $request->query('when'); // today | tomorrow | week | later | unscheduled

        $query = Job::with(['customer', 'vehicle', 'acUnit', 'assignedUser', 'assignees'])
            ->orderByRaw("CASE priority
                WHEN 'urgent' THEN 1
                WHEN 'high' THEN 2
                WHEN 'normal' THEN 3
                WHEN 'low' THEN 4
                ELSE 5 END")
            ->orderBy('scheduled_at', 'asc');

        // View filter
        if ($view === 'current') {
            $query->active();
        } elseif ($view === 'completed') {
            $query->whereIn('status', [Job::STATUS_COMPLETED, Job::STATUS_CANCELLED]);
        } elseif ($view === 'my_jobs') {
            $query->active()->assignedTo(auth()->id());
        }

        // Date filter
        if ($when) {
            $today = Carbon::today();
            $tomorrow = Carbon::tomorrow();

            switch ($when) {
                case 'today':
                    $query->whereDate('scheduled_at', $today);
                    break;
                case 'tomorrow':
                    $query->whereDate('scheduled_at', $tomorrow);
                    break;
                case 'week':
                    $query->whereBetween('scheduled_at', [$today, $today->copy()->addDays(7)]);
                    break;
                case 'later':
                    $query->where('scheduled_at', '>', $today->copy()->addDays(7));
                    break;
                case 'unscheduled':
                    $query->whereNull('scheduled_at');
                    break;
            }
        }

        // Type filter
        if ($type) {
            $query->ofType($type);
        }

        // Priority filter
        if ($priority) {
            $query->byPriority($priority);
        }

        // Search
        if ($search) {
            $query->search($search);
        }

        $jobs = $query->paginate(20)->withQueryString();

        // Status counts for tabs
        $statusCounts = [
            'current' => Job::active()->count(),
            'completed' => Job::whereIn('status', [Job::STATUS_COMPLETED, Job::STATUS_CANCELLED])->count(),
            'my_jobs' => Job::active()->assignedTo(auth()->id())->count(),
        ];

        // Date counts for quick filters
        $today = Carbon::today();
        $dateCounts = [
            'today' => Job::active()->whereDate('scheduled_at', $today)->count(),
            'tomorrow' => Job::active()->whereDate('scheduled_at', Carbon::tomorrow())->count(),
            'week' => Job::active()->whereBetween('scheduled_at', [$today, $today->copy()->addDays(7)])->count(),
            'unscheduled' => Job::active()->whereNull('scheduled_at')->count(),
        ];

        // Get technicians for filter dropdown
        $technicians = User::whereIn('role', [User::ROLE_MECHANIC, User::ROLE_MANAGER, User::ROLE_ADMIN])->get();

        return view('jobs.index', compact('jobs', 'view', 'type', 'priority', 'search', 'when', 'statusCounts', 'dateCounts', 'technicians'));
    }

    /**
     * Show the create job form (quick job creation).
     */
    public function create(Request $request)
    {
        // Pre-fill from query params (for calendar click-to-create)
        $scheduledAt = $request->query('scheduled_at');
        $preselectedCustomerId = $request->query('customer_id');
        $preselectedCustomer = null;

        // Load only the 5 most recent customers by default
        $customers = Customer::with([
            'vehicles:id,customer_id,brand,model,registration_number,year,mileage',
            'acUnits:id,customer_id,brand,btu,gas_type,location_description',
        ])->latest()->limit(5)->get();

        // If a customer is pre-selected, make sure they're in the list
        if ($preselectedCustomerId) {
            $preselectedCustomer = Customer::with([
                'vehicles:id,customer_id,brand,model,registration_number,year,mileage',
                'acUnits:id,customer_id,brand,btu,gas_type,location_description',
            ])->find($preselectedCustomerId);

            if ($preselectedCustomer && !$customers->contains('id', $preselectedCustomerId)) {
                $customers->prepend($preselectedCustomer);
            }
        }

        // Get technicians for assignment
        $technicians = User::whereIn('role', [User::ROLE_MECHANIC, User::ROLE_MANAGER, User::ROLE_ADMIN])
            ->orderBy('name')
            ->get();

        return view('jobs.create', compact('customers', 'preselectedCustomer', 'technicians', 'scheduledAt'));
    }

    /**
     * Search customers (for AJAX dropdown).
     */
    public function searchCustomers(Request $request)
    {
        $search = $request->get('q', '');

        $customers = Customer::with([
            'vehicles:id,customer_id,brand,model,registration_number,year,mileage',
            'acUnits:id,customer_id,brand,btu,gas_type,location_description',
        ])
            ->where(function ($query) use ($search) {
                $query->where('name', 'ilike', "%{$search}%")
                    ->orWhere('phone', 'ilike', "%{$search}%");
            })
            ->latest()
            ->limit(20)
            ->get();

        return response()->json([
            'results' => $customers->map(function ($customer) {
                return [
                    'id' => $customer->id,
                    'text' => $customer->name . ' (' . $customer->phone . ')',
                    'address' => $customer->address ?? '',
                    'vehicles' => $customer->vehicles->map(function ($v) {
                        return [
                            'id' => $v->id,
                            'label' => trim(($v->brand ?? '') . ' ' . ($v->model ?? '') . ' ' . ($v->registration_number ? '(' . $v->registration_number . ')' : ''))
                        ];
                    })->values()->all(),
                    'ac_units' => $customer->acUnits->map(function ($a) {
                        return [
                            'id' => $a->id,
                            'label' => trim(($a->brand ?? 'AC') . ' ' . ($a->btu ? $a->btu . ' BTU ' : '') . ($a->location_description ? '(' . $a->location_description . ')' : ''))
                        ];
                    })->values()->all(),
                ];
            })
        ]);
    }

    /**
     * Store a new job (optimized for quick creation).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Required fields for quick creation
            'job_type' => ['required', Rule::in(['moto', 'ac'])],
            'customer_phone' => ['required', 'string', 'max:20'],
            'customer_name' => ['required', 'string', 'max:100'],

            // Scheduling
            'scheduled_at' => ['nullable', 'date'],
            'scheduled_end_at' => ['nullable', 'date', 'after_or_equal:scheduled_at'],

            // Job details
            'title' => ['nullable', 'string', 'max:100'],
            'problem_description' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'priority' => ['nullable', Rule::in(array_keys(Job::getPriorities()))],

            // Optional - link to existing customer/equipment
            'customer_id' => ['nullable', 'exists:customers,id'],
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'ac_unit_id' => ['nullable', 'exists:ac_units,id'],

            // Assignment
            'assignees' => ['nullable', 'array'],
            'assignees.*' => ['exists:users,id'],

            // Legacy fields for backwards compatibility
            'job_date' => ['nullable', 'date'],
            'job_category' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'pickup_location' => ['nullable', 'string', 'max:255'],
        ]);

        // Try to find or create customer from phone
        $customer = null;
        if (!empty($validated['customer_id'])) {
            $customer = Customer::find($validated['customer_id']);
        }

        if (!$customer && $validated['customer_phone']) {
            $customer = Customer::where('phone', $validated['customer_phone'])->first();
        }

        // Determine status based on scheduling
        $status = Job::STATUS_NEW;
        if (!empty($validated['scheduled_at'])) {
            $status = Job::STATUS_SCHEDULED;
        }

        $job = Job::create([
            // New fields
            'title' => $validated['title'] ?? null,
            'scheduled_at' => $validated['scheduled_at'] ?? null,
            'scheduled_end_at' => $validated['scheduled_end_at'] ?? null,
            'location' => $validated['location'] ?? $validated['address'] ?? null,
            'priority' => $validated['priority'] ?? Job::PRIORITY_NORMAL,

            // Core fields
            'job_date' => $validated['job_date'] ?? now()->toDateString(),
            'job_type' => $validated['job_type'],
            'job_category' => $validated['job_category'] ?? 'general',
            'customer_id' => $customer?->id,

            // Snapshot customer data (phone-first workflow)
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'],
            'customer_email' => $customer?->email,

            // Equipment
            'vehicle_id' => $validated['vehicle_id'] ?? null,
            'ac_unit_id' => $validated['ac_unit_id'] ?? null,

            // Other
            'address' => $validated['address'] ?? $customer?->address ?? $validated['location'],
            'pickup_location' => $validated['pickup_location'] ?? null,
            'problem_description' => $validated['problem_description'] ?? null,

            'status' => $status,
            'payment_status' => 'unpaid',
            'labour_total' => 0,
            'parts_total' => 0,
            'travel_charges' => 0,
            'discount' => 0,
            'total_amount' => 0,
        ]);

        // Assign technicians if provided
        if (!empty($validated['assignees'])) {
            $job->assignTechnicians($validated['assignees'], auth()->user());
        }

        // Add creation note
        $job->addNote('Job created', auth()->user(), 'system');

        return redirect()
            ->route('jobs.show', $job)
            ->with('success', 'Job created successfully.');
    }

    /**
     * Show a single job with timeline.
     */
    public function show(Job $job)
    {
        $job->load([
            'customer',
            'vehicle',
            'acUnit',
            'assignedUser',
            'assignees',
            'notes.user',
            'items.inventoryItem',
            'payments'
        ]);

        // Filter inventory items based on job type
        $inventoryItems = InventoryItem::where('is_active', true)
            ->where('category', $job->job_type)
            ->orderBy('name')
            ->get();

        $serviceItems = $inventoryItems->where('is_service', true);
        $partItems = $inventoryItems->where('is_service', false);

        // Get technicians for reassignment
        $technicians = User::whereIn('role', [User::ROLE_MECHANIC, User::ROLE_MANAGER, User::ROLE_ADMIN])
            ->orderBy('name')
            ->get();

        return view('jobs.show', compact('job', 'inventoryItems', 'serviceItems', 'partItems', 'technicians'));
    }

    /**
     * Update job charges.
     */
    public function update(Request $request, Job $job)
    {
        $validated = $request->validate([
            'travel_charges' => ['required', 'numeric', 'min:0'],
            'discount' => ['required', 'numeric', 'min:0'],
        ]);

        $job->travel_charges = $validated['travel_charges'];
        $job->discount = $validated['discount'];
        $job->save();

        // Labour + parts will be recalculated from items
        $job->recalculateTotals();

        return back()->with('success', 'Charges updated successfully.');
    }

    /**
     * Show invoice for a job.
     */
    public function invoice(Job $job)
    {
        $job->load(['customer', 'vehicle', 'acUnit', 'items.inventoryItem', 'payments']);

        // Brand selection based on job type
        if ($job->job_type === 'ac') {
            $brand = [
                'name' => 'Micro Cool',
                'tagline' => 'We Fix, You Chill',
                'address' => 'Janavaree Hingun, Near Dharubaaruge',
                'phone' => '+960 9996210',
                'email' => 'hello@micronet.mv',
                'website' => 'cool.micronet.mv',
            ];
        } else {
            $brand = [
                'name' => 'Micro Moto Garage',
                'tagline' => 'Affordable & Reliable Motorbike Care',
                'address' => 'Janavaree Hingun, Near Dharubaaruge',
                'phone' => '+960 9996210',
                'email' => 'hello@micronet.mv',
                'website' => 'garage.micronet.mv',
            ];
        }

        // Simple invoice number pattern: JOB-<id>
        $invoiceNumber = 'JOB-' . str_pad($job->id, 5, '0', STR_PAD_LEFT);

        return view('jobs.invoice', [
            'job' => $job,
            'brand' => $brand,
            'invoiceNumber' => $invoiceNumber,
        ]);
    }

    /**
     * Update job status with automatic logging.
     */
    public function updateStatus(Request $request, Job $job)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(Job::getStatuses()))],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $job->updateStatus($validated['status'], auth()->user(), $validated['notes'] ?? null);

        $statusLabels = Job::getStatuses();
        $statusMessage = "Job status updated to: {$statusLabels[$validated['status']]}";

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $statusMessage]);
        }

        return redirect()
            ->route('jobs.show', $job)
            ->with('success', $statusMessage);
    }

    /**
     * Add a note to a job.
     */
    public function addNote(Request $request, Job $job)
    {
        $validated = $request->validate([
            'content' => ['required', 'string', 'max:1000'],
        ]);

        $job->addNote($validated['content'], auth()->user());

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Note added.');
    }

    /**
     * Update job assignees.
     */
    public function updateAssignees(Request $request, Job $job)
    {
        $validated = $request->validate([
            'assignees' => ['required', 'array'],
            'assignees.*' => ['exists:users,id'],
        ]);

        $job->assignTechnicians($validated['assignees'], auth()->user());

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Technicians assigned.');
    }

    /**
     * Reschedule a job (for calendar drag & drop).
     */
    public function reschedule(Request $request, Job $job)
    {
        $validated = $request->validate([
            'scheduled_at' => ['required', 'date'],
            'scheduled_end_at' => ['nullable', 'date', 'after_or_equal:scheduled_at'],
        ]);

        $oldDate = $job->scheduled_at?->format('M j, Y g:i A') ?? 'unscheduled';

        $job->scheduled_at = $validated['scheduled_at'];
        $job->scheduled_end_at = $validated['scheduled_end_at'] ?? null;

        // Auto-update status if moving from new to scheduled
        if ($job->status === Job::STATUS_NEW) {
            $job->status = Job::STATUS_SCHEDULED;
        }

        $job->save();

        $newDate = $job->scheduled_at->format('M j, Y g:i A');
        $job->addNote("Rescheduled from {$oldDate} to {$newDate}", auth()->user(), 'system');

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Job rescheduled.');
    }

    /**
     * Show quotation (for pending jobs).
     */
    public function quotation(Job $job)
    {
        // Reuse invoice view but change title to "QUOTATION"
        $job->load(['customer', 'vehicle', 'acUnit', 'items.inventoryItem']);

        if ($job->job_type === 'ac') {
            $brand = [
                'name' => 'Micro Cool',
                'tagline' => 'Air Conditioning Service',
                'address' => 'Janavaree Hingun, Near Dharubaaruge',
                'phone' => '+960 9996210',
                'email' => 'hello@micronet.mv',
                'website' => 'cool.micronet.mv',
            ];
        } else {
            $brand = [
                'name' => 'Micro Moto Garage',
                'tagline' => 'Motorcycle Service & Repair',
                'address' => 'Janavaree Hingun, Near Dharubaaruge',
                'phone' => '+960 9996210',
                'email' => 'hello@micronet.mv',
                'website' => 'garage.micronet.mv',
            ];
        }

        $quotationNumber = 'QUO-' . str_pad($job->id, 5, '0', STR_PAD_LEFT);

        return view('jobs.quotation', [
            'job' => $job,
            'brand' => $brand,
            'quotationNumber' => $quotationNumber,
        ]);
    }

    /**
     * Delete a job.
     */
    public function destroy(Job $job)
    {
        // Prevent deletion if job is in progress or completed (unless user is admin)
        if (!auth()->user()->isAdmin() && \in_array($job->status, ['in_progress', 'completed'])) {
            return back()->with('error', 'Cannot delete a job that is in progress or completed.');
        }

        $job->delete();

        return redirect()
            ->route('jobs.index')
            ->with('success', 'Job deleted successfully.');
    }

    // ========================================
    // Calendar Endpoints
    // ========================================

    /**
     * Show calendar view.
     */
    public function calendar(Request $request)
    {
        $technicians = User::whereIn('role', [User::ROLE_MECHANIC, User::ROLE_MANAGER, User::ROLE_ADMIN])
            ->orderBy('name')
            ->get();

        return view('jobs.calendar', compact('technicians'));
    }

    /**
     * Get jobs as calendar events (JSON API for FullCalendar).
     */
    public function calendarEvents(Request $request)
    {
        $start = $request->query('start');
        $end = $request->query('end');
        $type = $request->query('type'); // ac | moto
        $assignee = $request->query('assignee'); // user id

        $query = Job::with('assignees')
            ->whereNotNull('scheduled_at');

        // Date range filter (required by FullCalendar)
        if ($start && $end) {
            $query->where(function ($q) use ($start, $end) {
                $q->whereBetween('scheduled_at', [$start, $end])
                  ->orWhereBetween('scheduled_end_at', [$start, $end]);
            });
        }

        // Type filter
        if ($type) {
            $query->where('job_type', $type);
        }

        // Assignee filter
        if ($assignee) {
            $query->assignedTo($assignee);
        }

        $jobs = $query->get();

        $events = $jobs->map(fn ($job) => $job->toCalendarEvent());

        return response()->json($events);
    }

    /**
     * Quick create job from calendar (AJAX).
     */
    public function quickCreate(Request $request)
    {
        $validated = $request->validate([
            'job_type' => ['required', Rule::in(['moto', 'ac'])],
            'customer_phone' => ['required', 'string', 'max:20'],
            'customer_name' => ['required', 'string', 'max:100'],
            'title' => ['nullable', 'string', 'max:100'],
            'scheduled_at' => ['required', 'date'],
            'priority' => ['nullable', Rule::in(array_keys(Job::getPriorities()))],
        ]);

        $job = Job::create([
            'title' => $validated['title'] ?? null,
            'job_type' => $validated['job_type'],
            'job_date' => Carbon::parse($validated['scheduled_at'])->toDateString(),
            'job_category' => 'general',
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'],
            'scheduled_at' => $validated['scheduled_at'],
            'priority' => $validated['priority'] ?? Job::PRIORITY_NORMAL,
            'status' => Job::STATUS_SCHEDULED,
            'payment_status' => 'unpaid',
            'labour_total' => 0,
            'parts_total' => 0,
            'travel_charges' => 0,
            'discount' => 0,
            'total_amount' => 0,
        ]);

        $job->addNote('Job created from calendar', auth()->user(), 'system');

        return response()->json([
            'success' => true,
            'job' => $job->toCalendarEvent(),
            'redirect' => route('jobs.show', $job),
        ]);
    }
}
