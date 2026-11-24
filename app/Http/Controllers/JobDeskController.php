<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\FaultTicket;
use App\Models\Job;
use App\Models\JobNote;
use App\Models\User;
use App\Services\JobDesk;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Job Desk: simple staff screens for daily work.
 * Uses the existing jobs, notes and fault tickets – no separate job records.
 */
class JobDeskController extends Controller
{
    public function __construct(private JobDesk $desk)
    {
    }

    /**
     * "My Jobs" – the staff landing page.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $callJobs = $this->desk->callJobs($user);
        $allJobs = $this->desk->allOpenJobs();
        $myJobs = $allJobs->filter(fn (Job $job) => $job->isAssignedTo($user))->values();
        $openJobs = $allJobs->filter(fn (Job $job) => $job->assignees->isEmpty())->values();

        // Three simple tabs. Start on "My jobs" when the person has work, otherwise "All jobs".
        $tab = $request->query('show');
        if (!in_array($tab, ['mine', 'all', 'open'], true)) {
            $tab = $myJobs->isNotEmpty() ? 'mine' : 'all';
        }
        $list = match ($tab) {
            'mine' => $myJobs,
            'open' => $openJobs,
            default => $allJobs,
        };

        return view('desk.index', [
            'callJobs' => $callJobs,
            'tab' => $tab,
            'tabCounts' => ['mine' => $myJobs->count(), 'all' => $allJobs->count(), 'open' => $openJobs->count()],
            'groups' => $this->desk->groupByDay($list),
        ]);
    }

    /**
     * Completed jobs, grouped by the day they were finished.
     */
    public function done(Request $request)
    {
        $user = $request->user();
        $mine = $request->query('show') === 'mine';
        $jobs = $this->desk->completedJobs(30, $mine ? $user : null);

        $groups = $jobs->groupBy(fn (Job $job) => ($job->completed_at ?? $job->job_date)?->toDateString() ?? 'unknown');

        return view('desk.done', [
            'groups' => $groups,
            'mine' => $mine,
            'total' => $jobs->count(),
        ]);
    }

    /**
     * Admin / manager board: every job with an alert.
     */
    public function board(Request $request)
    {
        $jobs = $this->desk->boardJobs();
        $counts = $this->desk->counts($jobs);

        $alert = $request->query('alert');
        if ($alert && in_array($alert, JobDesk::ALERT_TYPES, true)) {
            $jobs = $jobs->filter(fn (Job $job) => collect($job->deskAlerts)->contains('type', $alert))->values();
        }

        $staffId = $request->integer('staff') ?: null;
        if ($staffId) {
            $jobs = $jobs->filter(fn (Job $job) => $job->assignees->contains('id', $staffId))->values();
        }

        $unit = $request->query('unit');
        if ($unit && array_key_exists($unit, JobDesk::unitLabels())) {
            $jobs = $jobs->filter(fn (Job $job) => $job->businessUnit() === $unit)->values();
        }

        $escalated = $jobs->filter(
            fn (Job $job) => $this->desk->widestScope($job->deskAlerts) === JobDesk::SCOPE_ADMIN
        )->count();

        $staff = User::whereIn('role', User::jobAssignableRoles())->orderBy('name')->get();

        return view('desk.board', compact('jobs', 'counts', 'staff', 'escalated'));
    }

    /**
     * The job card.
     */
    public function show(Request $request, Job $job)
    {
        $user = $request->user();
        $job->load(['assignees', 'notes.user', 'customer']);
        abort_unless($this->desk->canView($user, $job), 403);

        $alerts = $this->desk->alertsFor($job);
        $faults = FaultTicket::where('job_id', $job->id)->latest('id')->get();

        return view('desk.show', [
            'job' => $job,
            'alerts' => $alerts,
            'faults' => $faults,
            'canWork' => $this->desk->canWork($user, $job),
            'canContact' => $this->desk->canContact($user, $job),
            'canSelfAssign' => $this->desk->canSelfAssign($user, $job),
            'nameMissing' => Customer::isPlaceholderName($job->customer_name),
            'isAssigned' => $job->isAssignedTo($user),
            'hasAccepted' => $job->isAssignedTo($user) && $job->hasAccepted($user),
            'owesCall' => $job->owesCustomerCall(),
            'callReason' => $job->customerCallReason(),
        ]);
    }

    /**
     * Simple "New job" form for staff.
     */
    public function create(Request $request)
    {
        return view('desk.create', [
            'types' => $this->allowedJobTypes($request->user()),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $types = $this->allowedJobTypes($user);

        $validated = $request->validate([
            'job_type' => ['required', Rule::in(array_keys($types))],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:20', 'regex:/^[0-9\\s+()-]+$/'],
            'location' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:100'],
            'problem_description' => ['required', 'string', 'max:2000'],
            'scheduled_at' => ['nullable', 'date'],
            'priority' => ['nullable', Rule::in(array_keys(Job::getPriorities()))],
            'assign_me' => ['nullable', 'boolean'],
        ], [
            'customer_phone.regex' => __('Phone can only contain numbers.'),
        ]);

        // Same customer for the same number: drop spaces/dashes and the +960 country code.
        $phone = preg_replace('/[^0-9]/', '', $validated['customer_phone']);
        if (strlen($phone) > 7 && str_starts_with($phone, '960')) {
            $phone = substr($phone, 3);
        }

        $job = DB::transaction(function () use ($validated, $phone, $user) {
            $name = trim((string) ($validated['customer_name'] ?? '')) ?: Customer::placeholderName($phone);

            $customer = Customer::firstOrCreate(
                ['phone' => $phone],
                [
                    'name' => $name,
                    'address' => $validated['location'],
                    'category' => $validated['job_type'] === 'ac' ? 'ac' : $validated['job_type'],
                ]
            );

            // An existing customer with only a placeholder name gets the real name now.
            if (!$customer->wasRecentlyCreated && Customer::isPlaceholderName($customer->name)
                && !Customer::isPlaceholderName($name)) {
                $customer->update(['name' => $name]);
            }

            $job = Job::create([
                'job_type' => $validated['job_type'],
                'job_category' => 'general',
                'job_date' => now()->toDateString(),
                'title' => $validated['title'],
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone,
                'customer_email' => $customer->email,
                'location' => $validated['location'],
                'address' => $validated['location'],
                'problem_description' => $validated['problem_description'],
                'priority' => $validated['priority'] ?? Job::PRIORITY_NORMAL,
                'scheduled_at' => $validated['scheduled_at'] ?? null,
                'status' => !empty($validated['scheduled_at']) ? Job::STATUS_SCHEDULED : Job::STATUS_NEW,
                'payment_status' => 'unpaid',
                'labour_total' => 0,
                'parts_total' => 0,
                'travel_charges' => 0,
                'discount' => 0,
                'gst_amount' => 0,
                'total_amount' => 0,
            ]);

            $job->addNote("Job logged on Job Desk by {$user->name}", $user, JobNote::TYPE_SYSTEM);

            if (!empty($validated['assign_me'])) {
                $job->assignTechnicians([$user->id], $user);
                $job->markAcceptedBy($user, false);
            }

            return $job;
        });

        ActivityLog::record('job.created', "Job #{$job->id} logged on Job Desk by {$user->name} for '{$job->customer_name}'", $job);

        return redirect()->route('desk.show', $job)->with('success', __('Job #:id logged.', ['id' => $job->id]));
    }

    /**
     * Fill in the customer's name once it is known (any staff who can see the card).
     */
    public function customerName(Request $request, Job $job)
    {
        $user = $request->user();
        abort_unless($this->desk->canContact($user, $job), 403);

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
        ]);
        $name = trim($validated['customer_name']);

        DB::transaction(function () use ($job, $user, $name) {
            $old = $job->customer_name;
            $job->customer_name = $name;
            $job->save();

            // Only replace a placeholder on the customer record, never a real name.
            if ($job->customer && Customer::isPlaceholderName($job->customer->name)) {
                $job->customer->update(['name' => $name]);

                // Other jobs for the same customer still showing the placeholder get the name too.
                Job::where('customer_id', $job->customer_id)
                    ->where('id', '!=', $job->id)
                    ->where('customer_name', $old)
                    ->update(['customer_name' => $name]);
            }

            $job->addNote("Customer name set to {$name}", $user, JobNote::TYPE_SYSTEM);
        });

        return $this->backToCard($job, __('Customer name saved.'));
    }

    /**
     * Mechanics log jobs for their own business; everyone else for any.
     */
    private function allowedJobTypes(User $user): array
    {
        $all = [
            Job::TYPE_AC => 'Micro Cool',
            Job::TYPE_BIKE => 'Micro Moto',
            Job::TYPE_IT => 'Micronet',
            Job::TYPE_EASYFIX => 'Easy Fix',
        ];

        return match ($user->allowedBusinessUnit()) {
            'cool' => [Job::TYPE_AC => $all[Job::TYPE_AC]],
            'moto' => [Job::TYPE_BIKE => $all[Job::TYPE_BIKE]],
            default => $all,
        };
    }

    /**
     * "Assign to me" – staff put themselves on the job (others stay assigned).
     */
    public function take(Request $request, Job $job)
    {
        $user = $request->user();
        $job->load('assignees');
        abort_unless($this->desk->canSelfAssign($user, $job), 403);

        // Whoever takes a job with no promised time decides when they will reach the customer.
        $needsTime = $job->scheduled_at === null;
        $validated = $request->validate([
            'scheduled_at' => [$needsTime ? 'required' : 'nullable', 'date', 'after:-10 minutes'],
        ], [
            'scheduled_at.required' => __('Set the time you will reach the customer.'),
            'scheduled_at.after' => __('The time must be now or later.'),
        ]);

        DB::transaction(function () use ($job, $user, $needsTime, $validated) {
            $ids = $job->assignees->pluck('id')->push($user->id)->all();
            $job->assignTechnicians($ids, $user);
            // Choosing the job themselves counts as accepting it.
            $job->markAcceptedBy($user, false);

            if ($needsTime) {
                $job->scheduled_at = Carbon::parse($validated['scheduled_at']);
                if ($job->status === Job::STATUS_NEW) {
                    $job->status = Job::STATUS_SCHEDULED;
                }
                $job->save();
                $job->addNote('Promised time set to ' . $job->scheduled_at->format('D j M, g:i A') . " by {$user->name}", $user, JobNote::TYPE_SYSTEM);
            }
        });

        ActivityLog::record('job.self_assigned', "Job #{$job->id}: {$user->name} assigned themselves (Job Desk)", $job);

        return $this->backToCard($job, __('You are now assigned to this job.'));
    }

    public function accept(Request $request, Job $job)
    {
        $user = $request->user();
        abort_unless($job->isAssignedTo($user), 403);

        $job->markAcceptedBy($user);

        return $this->backToCard($job, __('Job accepted.'));
    }

    public function status(Request $request, Job $job)
    {
        $user = $request->user();
        abort_unless($this->desk->canWork($user, $job), 403);

        $validated = $request->validate([
            'action' => ['required', Rule::in(['start', 'hold', 'resume', 'complete'])],
        ]);

        $transitions = [
            'start' => [[Job::STATUS_NEW, Job::STATUS_SCHEDULED], Job::STATUS_IN_PROGRESS],
            'hold' => [[Job::STATUS_IN_PROGRESS], Job::STATUS_WAITING_PARTS],
            'resume' => [[Job::STATUS_WAITING_PARTS], Job::STATUS_IN_PROGRESS],
            'complete' => [Job::getActiveStatuses(), Job::STATUS_COMPLETED],
        ];

        [$from, $to] = $transitions[$validated['action']];

        if (!in_array($job->status, $from, true)) {
            return $this->backToCard($job, null, __('This action is not available for this job.'));
        }

        $job->updateStatus($to, $user);

        ActivityLog::record('job.status_updated', "Job #{$job->id} status → {$to} (Job Desk)", $job);

        $message = match ($validated['action']) {
            'hold' => __('Job put on hold. Please call the customer and log it.'),
            'complete' => __('Job completed. Remember to give the invoice.'),
            default => __('Status updated.'),
        };

        return $this->backToCard($job, $message);
    }

    public function invoiced(Request $request, Job $job)
    {
        abort_unless($this->desk->canWork($request->user(), $job), 403);

        if ($job->status !== Job::STATUS_COMPLETED) {
            return $this->backToCard($job, null, __('Complete the job first.'));
        }

        $job->markInvoiced($request->user());

        return $this->backToCard($job, __('Marked as invoice given.'));
    }

    public function contact(Request $request, Job $job)
    {
        $job->load('assignees');
        abort_unless($this->desk->canContact($request->user(), $job), 403);

        $validated = $request->validate([
            'method' => ['required', Rule::in(Job::CONTACT_METHODS)],
            'outcome' => ['required', Rule::in(Job::CONTACT_OUTCOMES)],
            'explanation' => ['nullable', Rule::in(Job::CONTACT_EXPLANATIONS)],
            'new_time' => ['nullable', 'required_if:outcome,rescheduled', 'date', 'after:now'],
            'note' => ['nullable', 'string', 'max:1000'],
        ], [
            'new_time.required_if' => __('Enter the new time agreed with the customer.'),
            'new_time.after' => __('The new time must be in the future.'),
        ]);

        $job->logCustomerContact(
            $request->user(),
            $validated['method'],
            $validated['outcome'],
            $validated['explanation'] ?? null,
            $validated['note'] ?? null,
            $validated['new_time'] ?? null
        );

        $message = $validated['outcome'] === Job::CONTACT_OUTCOME_NO_ANSWER
            ? __('Logged. The customer still needs to be reached – try again soon.')
            : __('Customer contact logged.');

        return $this->backToCard($job, $message);
    }

    public function remark(Request $request, Job $job)
    {
        $job->load('assignees');
        abort_unless($this->desk->canContact($request->user(), $job), 403);

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:1000'],
        ]);

        $job->addNote($validated['content'], $request->user());

        return $this->backToCard($job, __('Remark added.'));
    }

    public function fault(Request $request, Job $job)
    {
        $user = $request->user();
        abort_unless($this->desk->canWork($user, $job), 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],
            'priority' => ['required', Rule::in(['urgent', 'normal'])],
        ]);

        $ticket = DB::transaction(function () use ($job, $user, $validated) {
            $ticket = FaultTicket::create([
                'ticket_number' => 'FT-TEMP-' . uniqid(),
                'business_unit' => $job->businessUnit(),
                'priority' => $validated['priority'],
                'status' => 'open',
                'customer_id' => $job->customer_id,
                'customer_name' => $job->customer_name ?: 'Unknown',
                'customer_phone' => $job->customer_phone ?: '-',
                'job_id' => $job->id,
                'title' => $validated['title'],
                'description' => $validated['description'],
                'deadline_at' => Carbon::now()->addHours($validated['priority'] === 'urgent' ? 24 : 48),
                'created_by' => $user->id,
                'assigned_to' => $job->assignees()->value('users.id'),
            ]);

            // The ticket's created hook also flags the job: customer must be called.
            $ticket->refresh();
            $job->addNote("Fault reported: {$ticket->ticket_number} – {$validated['title']}", $user, JobNote::TYPE_FAULT);

            return $ticket;
        });

        ActivityLog::record('fault.created', "Fault ticket {$ticket->ticket_number} created from Job #{$job->id} (Job Desk)", $ticket);

        return $this->backToCard($job, __('Fault :number reported. Please call the customer and agree a time.', ['number' => $ticket->ticket_number]));
    }

    public function cancel(Request $request, Job $job)
    {
        $user = $request->user();
        abort_unless($this->desk->canWork($user, $job), 403);

        if (!$job->isActive()) {
            return $this->backToCard($job, null, __('Only open jobs can be cancelled.'));
        }

        $validated = $request->validate([
            'cancellation_reason' => ['required', Rule::in(array_keys(Job::getCancellationReasons()))],
            'cancellation_notes' => ['nullable', 'string', 'max:500'],
            'customer_informed' => ['nullable', 'boolean'],
            'contact_method' => ['nullable', Rule::in(Job::CONTACT_METHODS)],
        ]);

        $informed = (bool) ($validated['customer_informed'] ?? false);

        if (!$informed && $this->desk->cancellationNeedsCustomerTold($job, $validated['cancellation_reason'])) {
            return back()->withErrors([
                'customer_informed' => __('Tell the customer first, then tick "Customer has been told".'),
            ])->withInput();
        }

        DB::transaction(function () use ($job, $user, $validated, $informed) {
            $job->cancel($validated['cancellation_reason'], $validated['cancellation_notes'] ?? null, $user);

            if ($informed) {
                $job->logCustomerContact(
                    $user,
                    $validated['contact_method'] ?? 'call',
                    Job::CONTACT_OUTCOME_INFORMED,
                    'other',
                    'Told about the cancellation'
                );
            }
        });

        return $this->backToCard($job, __('Job cancelled.'));
    }

    public function locale(Request $request)
    {
        $validated = $request->validate([
            'locale' => ['required', Rule::in(array_keys(config('job_desk.locales')))],
        ]);

        $request->user()->forceFill(['locale' => $validated['locale']])->save();

        return back();
    }

    private function backToCard(Job $job, ?string $success = null, ?string $error = null)
    {
        // A teammate who helped (e.g. logged the call) may no longer have access
        // once the alert is cleared – send them back to their list instead of a 403.
        $fresh = $job->fresh(['assignees']);
        $user = request()->user();
        $redirect = $fresh && $user && $this->desk->canView($user, $fresh)
            ? redirect()->route('desk.show', $job)
            : redirect()->route('desk.index');

        if ($success) {
            $redirect->with('success', $success);
        }
        if ($error) {
            $redirect->with('error', $error);
        }

        return $redirect;
    }
}
