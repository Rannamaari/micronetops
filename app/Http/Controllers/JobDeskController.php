<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
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

        $myJobs = $this->desk->myJobs($user);
        $teamJobs = $this->desk->teamJobs($user);

        $myCalls = $myJobs->filter(
            fn (Job $job) => collect($job->deskAlerts)->contains('type', JobDesk::ALERT_CALL_CUSTOMER)
        )->values();

        return view('desk.index', [
            'myJobs' => $myJobs,
            'myCalls' => $myCalls,
            'teamJobs' => $teamJobs,
            'counts' => $this->desk->counts($myJobs),
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
            'isAssigned' => $job->isAssignedTo($user),
            'hasAccepted' => $job->isAssignedTo($user) && $job->hasAccepted($user),
            'owesCall' => $job->owesCustomerCall(),
            'callReason' => $job->customerCallReason(),
        ]);
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
