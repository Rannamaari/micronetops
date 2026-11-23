<?php

namespace App\Services;

use App\Models\Job;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Job Desk rules: which alerts a job has, who sees it, and who may act on it.
 *
 * Alerts are computed live from the job's data (no background timer), so
 * they are always current when a page is opened.
 *
 * Alert scopes, from narrowest to widest:
 *   assignee – only the assigned staff see it
 *   team     – every desk user for that business unit sees it
 *   admin    – shown on the admin board as escalated
 */
class JobDesk
{
    public const ALERT_UNASSIGNED = 'unassigned';
    public const ALERT_NOT_ACCEPTED = 'not_accepted';
    public const ALERT_OVERDUE = 'overdue';
    public const ALERT_DUE_SOON = 'due_soon';
    public const ALERT_NO_UPDATE = 'no_update';
    public const ALERT_NOT_INVOICED = 'not_invoiced';
    public const ALERT_CALL_CUSTOMER = 'call_customer';

    public const ALERT_TYPES = [
        self::ALERT_CALL_CUSTOMER,
        self::ALERT_OVERDUE,
        self::ALERT_NOT_ACCEPTED,
        self::ALERT_UNASSIGNED,
        self::ALERT_NO_UPDATE,
        self::ALERT_NOT_INVOICED,
        self::ALERT_DUE_SOON,
    ];

    public const SCOPE_ASSIGNEE = 'assignee';
    public const SCOPE_TEAM = 'team';
    public const SCOPE_ADMIN = 'admin';

    private const SCOPE_RANK = [self::SCOPE_ASSIGNEE => 1, self::SCOPE_TEAM => 2, self::SCOPE_ADMIN => 3];

    /**
     * Relations every desk query should load so alerts need no extra queries.
     */
    public static function withRelations(Builder $query): Builder
    {
        return $query->with('assignees')->withMax('notes', 'created_at');
    }

    /**
     * All alerts for a job, most urgent first.
     *
     * @return array<int, array{type:string, level:string, scope:string, since:?Carbon, meta:array}>
     */
    public function alertsFor(Job $job, ?Carbon $now = null): array
    {
        $now ??= now();
        $alerts = [];
        $active = $job->isActive();
        $assignees = $job->relationLoaded('assignees') ? $job->assignees : $job->assignees()->get();

        // Customer must be called (with escalation over time)
        $callSince = $job->customerCallOwedSince($now);
        if ($callSince !== null) {
            $minutes = $callSince->diffInMinutes($now, false);
            $scope = self::SCOPE_ASSIGNEE;
            if ($minutes >= config('job_desk.call_admin_minutes')) {
                $scope = self::SCOPE_ADMIN;
            } elseif ($minutes >= config('job_desk.call_team_minutes')) {
                $scope = self::SCOPE_TEAM;
            }
            // Nobody assigned: nobody else would see it, so go straight to the team.
            if ($assignees->isEmpty() && $scope === self::SCOPE_ASSIGNEE) {
                $scope = self::SCOPE_TEAM;
            }
            $alerts[] = $this->alert(self::ALERT_CALL_CUSTOMER, 'danger', $scope, $callSince, [
                'reason' => $job->customerCallReason($now),
            ]);
        }

        if ($active) {
            // Overdue
            if ($job->isOverdue($now)) {
                $alerts[] = $this->alert(self::ALERT_OVERDUE, 'danger', self::SCOPE_TEAM, $job->promisedAt());
            } elseif (($promised = $job->promisedAt()) && $promised->gt($now)
                && $promised->diffInMinutes($now, true) <= config('job_desk.due_soon_minutes')) {
                $alerts[] = $this->alert(self::ALERT_DUE_SOON, 'info', self::SCOPE_ASSIGNEE, $promised);
            }

            // Nobody assigned
            if ($assignees->isEmpty()) {
                $alerts[] = $this->alert(self::ALERT_UNASSIGNED, 'warn', self::SCOPE_ADMIN, null);
            }

            // Assigned but not accepted
            $pending = $assignees->filter(fn ($u) => $u->pivot->accepted_at === null);
            if ($pending->isNotEmpty()) {
                $oldest = $pending
                    ->map(fn ($u) => $u->pivot->assigned_at ? Carbon::parse($u->pivot->assigned_at) : null)
                    ->filter()
                    ->min();

                if ($oldest) {
                    $minutes = $oldest->diffInMinutes($now, false);
                    if ($minutes >= config('job_desk.accept_escalate_minutes')) {
                        $alerts[] = $this->alert(self::ALERT_NOT_ACCEPTED, 'danger', self::SCOPE_TEAM, $oldest, [
                            'names' => $pending->pluck('name')->values()->all(),
                        ]);
                    } elseif ($minutes >= config('job_desk.accept_warn_minutes')) {
                        $alerts[] = $this->alert(self::ALERT_NOT_ACCEPTED, 'warn', self::SCOPE_ASSIGNEE, $oldest, [
                            'names' => $pending->pluck('name')->values()->all(),
                        ]);
                    }
                }
            }

            // Work started but no update for a long time
            if (in_array($job->status, [Job::STATUS_IN_PROGRESS, Job::STATUS_WAITING_PARTS], true)) {
                $last = $this->lastActivityAt($job);
                if ($last && $last->diffInHours($now, false) >= config('job_desk.no_update_hours')) {
                    $alerts[] = $this->alert(self::ALERT_NO_UPDATE, 'warn', self::SCOPE_ASSIGNEE, $last);
                }
            }
        }

        // Done, but invoice not given to the customer
        if ($job->status === Job::STATUS_COMPLETED
            && $job->invoiced_at === null
            && $job->payment_status !== 'paid') {
            $completedAt = $job->completed_at ?? $now;
            if ($completedAt->diffInHours($now, false) >= config('job_desk.not_invoiced_hours')) {
                $alerts[] = $this->alert(self::ALERT_NOT_INVOICED, 'warn', self::SCOPE_ASSIGNEE, $completedAt);
            }
        }

        return $alerts;
    }

    /**
     * Widest scope among a job's alerts (null if no alerts).
     */
    public function widestScope(array $alerts): ?string
    {
        $best = null;
        foreach ($alerts as $alert) {
            if ($best === null || self::SCOPE_RANK[$alert['scope']] > self::SCOPE_RANK[$best]) {
                $best = $alert['scope'];
            }
        }

        return $best;
    }

    /**
     * Alerts every desk user should see (customer waiting for a call, overdue, not accepted for an hour).
     */
    public function teamAlerts(array $alerts): array
    {
        return array_values(array_filter($alerts, function ($a) {
            if ($a['type'] === self::ALERT_CALL_CUSTOMER) {
                return $a['scope'] !== self::SCOPE_ASSIGNEE;
            }

            return $a['type'] === self::ALERT_NOT_ACCEPTED && $a['level'] === 'danger';
        }));
    }

    /**
     * Cancelling needs "customer has been told" when a call is owed or we are the reason.
     * "Customer unreachable" is exempt – it already records that we tried.
     */
    public function cancellationNeedsCustomerTold(Job $job, ?string $reason): bool
    {
        if ($reason === Job::CANCEL_CUSTOMER_UNREACHABLE) {
            return false;
        }

        return $job->owesCustomerCall()
            || in_array($reason, Job::cancellationReasonsByUs(), true);
    }

    // ----------------------------------------------------------------
    // Labels (English keys; translated with __() in the views)
    // ----------------------------------------------------------------

    public static function alertLabels(): array
    {
        return [
            self::ALERT_CALL_CUSTOMER => 'Call the customer',
            self::ALERT_OVERDUE => 'Overdue',
            self::ALERT_NOT_ACCEPTED => 'Not accepted',
            self::ALERT_UNASSIGNED => 'Nobody assigned',
            self::ALERT_NO_UPDATE => 'No update for 24 hours',
            self::ALERT_NOT_INVOICED => 'Invoice not given',
            self::ALERT_DUE_SOON => 'Due soon',
        ];
    }

    public static function contactReasonLabels(): array
    {
        return [
            Job::CONTACT_REASON_OVERDUE => 'We are late',
            Job::CONTACT_REASON_WAITING_PARTS => 'Waiting for parts',
            Job::CONTACT_REASON_FAULT => 'Fault reported',
            Job::CONTACT_REASON_CANCELLED_BY_US => 'Cancelled by us',
        ];
    }

    public static function methodLabels(): array
    {
        return [
            'call' => 'Phone call',
            'whatsapp' => 'WhatsApp',
            'visit' => 'In person',
        ];
    }

    public static function outcomeLabels(): array
    {
        return [
            Job::CONTACT_OUTCOME_INFORMED => 'Customer informed',
            Job::CONTACT_OUTCOME_NO_ANSWER => 'No answer',
            Job::CONTACT_OUTCOME_RESCHEDULED => 'New time agreed',
        ];
    }

    public static function explanationLabels(): array
    {
        return [
            'running_late' => 'We are running late',
            'waiting_parts' => 'Waiting for parts',
            'overloaded' => 'Too many jobs today',
            'wrong_time' => 'Wrong time was noted',
            'fault' => 'Fixing a fault',
            'completed' => 'Work is done',
            'other' => 'Other',
        ];
    }

    /**
     * Suggested polite wording for staff to say or send.
     */
    public static function scripts(): array
    {
        return [
            'running_late' => 'Sorry, we are running late today. Our technician will reach you by [time]. Thank you for your patience.',
            'waiting_parts' => 'Sorry for the delay. We are waiting for a part for your job. We will update you as soon as it arrives.',
            'overloaded' => 'Sorry, we have many jobs today and could not come on time. Can we come at [time]?',
            'wrong_time' => 'Sorry, there was a mistake with your appointment time. Can we come at [time]?',
            'fault' => 'Sorry for the trouble. We will check and fix the problem. Our technician will come at [time].',
            'completed' => 'Your job is completed. Thank you for choosing us.',
            'other' => 'Sorry for the inconvenience. We will update you shortly.',
        ];
    }

    public static function unitLabels(): array
    {
        return [
            'moto' => 'Micro Moto',
            'cool' => 'Micro Cool',
            'it' => 'Micronet',
            'easyfix' => 'Easy Fix',
        ];
    }

    /**
     * Resolve staff given as ids or names (for the API / logging from chat).
     * Returns [ids, errors]. A name must match exactly one assignable person.
     *
     * @return array{0: array<int,int>, 1: array<int,string>}
     */
    public function resolveStaff(array $refs): array
    {
        $ids = [];
        $errors = [];

        foreach ($refs as $ref) {
            $ref = trim((string) $ref);
            if ($ref === '') {
                continue;
            }

            $query = User::whereIn('role', User::jobAssignableRoles());

            if (ctype_digit($ref)) {
                $user = (clone $query)->find((int) $ref);
                $user ? $ids[] = $user->id : $errors[] = "No staff member with id {$ref}.";
                continue;
            }

            $needle = mb_strtolower($ref);
            $exact = (clone $query)->whereRaw('lower(name) = ?', [$needle])->get();
            $matches = $exact->isNotEmpty()
                ? $exact
                : (clone $query)->whereRaw('lower(name) like ?', ['%' . $needle . '%'])->get();

            if ($matches->count() === 1) {
                $ids[] = $matches->first()->id;
            } elseif ($matches->isEmpty()) {
                $errors[] = "No staff member named '{$ref}'.";
            } else {
                $errors[] = "'{$ref}' matches more than one person: " . $matches->pluck('name')->join(', ') . '.';
            }
        }

        return [array_values(array_unique($ids)), $errors];
    }

    /**
     * Compact job summary with links, for the API.
     */
    public function apiSummary(Job $job, ?array $alerts = null): array
    {
        $alerts ??= $this->alertsFor($job);
        $labels = self::alertLabels();

        return [
            'id' => $job->id,
            'title' => $job->title,
            'job_type' => $job->job_type,
            'business' => self::unitLabels()[$job->businessUnit()] ?? $job->job_type,
            'status' => $job->status,
            'customer_name' => $job->customer_name,
            'customer_phone' => $job->customer_phone,
            'promised_at' => $job->promisedAt()?->format('Y-m-d H:i'),
            'invoiced_at' => $job->invoiced_at?->format('Y-m-d H:i'),
            'assignees' => $job->assignees->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'accepted' => $u->pivot->accepted_at !== null,
            ])->values()->all(),
            'customer_call_owed' => $job->owesCustomerCall(),
            'alerts' => collect($alerts)->map(fn ($a) => [
                'type' => $a['type'],
                'label' => $labels[$a['type']] ?? $a['type'],
                'level' => $a['level'],
                'scope' => $a['scope'],
                'since' => $a['since']?->format('Y-m-d H:i'),
            ])->values()->all(),
            'desk_url' => route('desk.show', $job),
            'admin_url' => route('jobs.show', $job),
        ];
    }

    // ----------------------------------------------------------------
    // Access rules
    // ----------------------------------------------------------------

    public function unitAllowed(User $user, Job $job): bool
    {
        $unit = $user->allowedBusinessUnit();

        return $unit === null || $unit === $job->businessUnit();
    }

    /**
     * Can open the job card on the desk.
     */
    public function canView(User $user, Job $job): bool
    {
        if (!$user->canUseJobDesk()) {
            return false;
        }
        if ($user->canManageJobDesk() || $job->isAssignedTo($user)) {
            return true;
        }

        return $this->unitAllowed($user, $job)
            && count($this->teamAlerts($this->alertsFor($job))) > 0;
    }

    /**
     * Can change status, accept, mark invoiced, cancel, report a fault.
     */
    public function canWork(User $user, Job $job): bool
    {
        return $user->canUseJobDesk()
            && ($user->canManageJobDesk() || $job->isAssignedTo($user));
    }

    /**
     * Can log a customer call or add a remark (anyone who can see the card).
     */
    public function canContact(User $user, Job $job): bool
    {
        return $this->canView($user, $job);
    }

    // ----------------------------------------------------------------
    // Lists
    // ----------------------------------------------------------------

    /**
     * Jobs that belong on a person's "My Jobs" list.
     */
    public function myJobs(User $user): Collection
    {
        $jobs = self::withRelations(Job::query())
            ->assignedTo($user->id)
            ->where(function ($q) {
                $q->whereIn('status', Job::getActiveStatuses())
                    ->orWhere(function ($q2) {
                        $q2->where('status', Job::STATUS_COMPLETED)
                            ->whereNull('invoiced_at')
                            ->where('payment_status', '!=', 'paid');
                    })
                    ->orWhereNotNull('customer_contact_required_since');
            })
            ->get();

        return $this->decorate($jobs);
    }

    /**
     * Jobs (not assigned to this user) that need the whole team's help.
     */
    public function teamJobs(User $user): Collection
    {
        $jobs = $this->candidateJobs()
            ->reject(fn (Job $job) => $job->isAssignedTo($user))
            ->filter(fn (Job $job) => $this->unitAllowed($user, $job));

        return $this->decorate($jobs)
            ->filter(fn (Job $job) => count($this->teamAlerts($job->deskAlerts)) > 0)
            ->values();
    }

    /**
     * Everything with an alert, for the admin board.
     */
    public function boardJobs(): Collection
    {
        return $this->decorate($this->candidateJobs())
            ->filter(fn (Job $job) => count($job->deskAlerts) > 0)
            ->values();
    }

    /**
     * Jobs that could possibly carry an alert.
     */
    private function candidateJobs(): Collection
    {
        return self::withRelations(Job::query())
            ->where(function ($q) {
                $q->whereIn('status', Job::getActiveStatuses())
                    ->orWhereNotNull('customer_contact_required_since')
                    ->orWhere(function ($q2) {
                        $q2->where('status', Job::STATUS_COMPLETED)
                            ->whereNull('invoiced_at')
                            ->where('payment_status', '!=', 'paid');
                    });
            })
            ->get();
    }

    /**
     * Attach alerts and sort: most urgent first, then by promised time.
     */
    public function decorate(Collection $jobs, ?Carbon $now = null): Collection
    {
        $now ??= now();

        return $jobs->each(function (Job $job) use ($now) {
            $job->deskAlerts = $this->alertsFor($job, $now);
        })->sortBy(function (Job $job) {
            $danger = collect($job->deskAlerts)->contains('level', 'danger') ? 0 : 1;
            $warn = collect($job->deskAlerts)->contains('level', 'warn') ? 0 : 1;
            $promised = $job->promisedAt()?->timestamp ?? PHP_INT_MAX;

            return sprintf('%d%d%020d', $danger, $warn, $promised);
        })->values();
    }

    /**
     * Counts per alert type for a set of decorated jobs.
     */
    public function counts(Collection $jobs): array
    {
        $counts = array_fill_keys(self::ALERT_TYPES, 0);
        foreach ($jobs as $job) {
            foreach (collect($job->deskAlerts)->pluck('type')->unique() as $type) {
                $counts[$type]++;
            }
        }

        return $counts;
    }

    private function lastActivityAt(Job $job): ?Carbon
    {
        $times = array_filter([
            $job->notes_max_created_at ? Carbon::parse($job->notes_max_created_at) : null,
            $job->started_at,
        ]);

        if (!array_key_exists('notes_max_created_at', $job->getAttributes())) {
            $latest = $job->notes()->max('created_at');
            if ($latest) {
                $times[] = Carbon::parse($latest);
            }
        }

        return empty($times) ? null : max($times);
    }

    private function alert(string $type, string $level, string $scope, ?Carbon $since, array $meta = []): array
    {
        return compact('type', 'level', 'scope', 'since', 'meta');
    }
}
