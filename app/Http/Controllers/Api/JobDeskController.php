<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\User;
use App\Services\JobDesk;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Job Desk API (bearer token) – lets Hussain log and follow up jobs from chat.
 */
class JobDeskController extends Controller
{
    public function __construct(private JobDesk $desk)
    {
    }

    /**
     * GET /api/desk/staff – people who can be assigned to jobs.
     */
    public function staff(): JsonResponse
    {
        $staff = User::whereIn('role', User::jobAssignableRoles())
            ->orderBy('name')
            ->get(['id', 'name', 'role'])
            ->map(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'role' => $u->role,
                'business' => match ($u->allowedBusinessUnit()) {
                    'moto' => 'Micro Moto',
                    'cool' => 'Micro Cool',
                    default => 'All',
                },
            ]);

        return response()->json(['data' => $staff]);
    }

    /**
     * GET /api/desk/alerts – everything that needs attention, most urgent first.
     */
    public function alerts(): JsonResponse
    {
        $jobs = $this->desk->boardJobs();

        return response()->json([
            'generated_at' => now()->format('Y-m-d H:i'),
            'counts' => $this->desk->counts($jobs),
            'escalated' => $jobs->filter(fn (Job $j) => $this->desk->widestScope($j->deskAlerts) === JobDesk::SCOPE_ADMIN)->count(),
            'board_url' => route('desk.board'),
            'data' => $jobs->map(fn (Job $j) => $this->desk->apiSummary($j, $j->deskAlerts))->values(),
        ]);
    }

    /**
     * POST /api/jobs/{id}/notes – add a remark. Body: { "content": "..." }
     */
    public function addNote(int $id, Request $request): JsonResponse
    {
        $job = Job::find($id);
        if (!$job) {
            return $this->notFound($id);
        }

        $validated = $this->validateOrFail($request, [
            'content' => ['required', 'string', 'max:1000'],
        ]);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $job->addNote($validated['content'], $this->actor());

        return $this->jobResponse($job, 'Remark added.');
    }

    /**
     * POST /api/jobs/{id}/contact – log a call / WhatsApp / visit.
     * Body: { "method": "call|whatsapp|visit", "outcome": "informed|no_answer|rescheduled",
     *         "explanation": "running_late|...", "note": "...", "new_time": "2026-11-24 15:00" }
     */
    public function contact(int $id, Request $request): JsonResponse
    {
        $job = Job::find($id);
        if (!$job) {
            return $this->notFound($id);
        }

        $validated = $this->validateOrFail($request, [
            'method' => ['required', Rule::in(Job::CONTACT_METHODS)],
            'outcome' => ['required', Rule::in(Job::CONTACT_OUTCOMES)],
            'explanation' => ['nullable', Rule::in(Job::CONTACT_EXPLANATIONS)],
            'new_time' => ['nullable', 'required_if:outcome,rescheduled', 'date', 'after:now'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $job->logCustomerContact(
            $this->actor(),
            $validated['method'],
            $validated['outcome'],
            $validated['explanation'] ?? null,
            $validated['note'] ?? null,
            $validated['new_time'] ?? null
        );

        return $this->jobResponse($job, 'Customer contact logged.');
    }

    /**
     * PATCH /api/jobs/{id}/assignees – Body: { "assignees": ["Ali", 12] }
     */
    public function assignees(int $id, Request $request): JsonResponse
    {
        $job = Job::find($id);
        if (!$job) {
            return $this->notFound($id);
        }

        $validated = $this->validateOrFail($request, [
            'assignees' => ['required', 'array', 'min:1'],
        ]);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        [$ids, $errors] = $this->desk->resolveStaff($validated['assignees']);
        if (!empty($errors) || empty($ids)) {
            return response()->json([
                'error' => 'Could not match staff.',
                'details' => ['assignees' => $errors ?: ['No staff given.']],
            ], 422);
        }

        $job->assignTechnicians($ids, $this->actor());

        return $this->jobResponse($job, 'Assigned.');
    }

    /**
     * POST /api/jobs/{id}/cancel
     * Body: { "cancellation_reason": "...", "cancellation_notes": "...",
     *         "customer_informed": true, "contact_method": "call" }
     */
    public function cancel(int $id, Request $request): JsonResponse
    {
        $job = Job::find($id);
        if (!$job) {
            return $this->notFound($id);
        }
        if (!$job->isActive()) {
            return response()->json(['error' => 'Only open jobs can be cancelled.'], 422);
        }

        $validated = $this->validateOrFail($request, [
            'cancellation_reason' => ['required', Rule::in(array_keys(Job::getCancellationReasons()))],
            'cancellation_notes' => ['nullable', 'string', 'max:500'],
            'customer_informed' => ['nullable', 'boolean'],
            'contact_method' => ['nullable', Rule::in(Job::CONTACT_METHODS)],
        ]);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $informed = (bool) ($validated['customer_informed'] ?? false);
        if (!$informed && $this->desk->cancellationNeedsCustomerTold($job, $validated['cancellation_reason'])) {
            return response()->json([
                'error' => 'The customer must be told first. Send customer_informed: true once they have been told.',
            ], 422);
        }

        $actor = $this->actor();
        DB::transaction(function () use ($job, $actor, $validated, $informed) {
            $job->cancel($validated['cancellation_reason'], $validated['cancellation_notes'] ?? null, $actor);
            if ($informed) {
                $job->logCustomerContact($actor, $validated['contact_method'] ?? 'call', Job::CONTACT_OUTCOME_INFORMED, 'other', 'Told about the cancellation');
            }
        });

        return $this->jobResponse($job, 'Job cancelled.');
    }

    private function actor(): ?User
    {
        $actor = User::where('role', User::ROLE_ADMIN)->orderBy('id')->first();
        if ($actor) {
            Auth::setUser($actor);
        }

        return $actor;
    }

    private function validateOrFail(Request $request, array $rules): array|JsonResponse
    {
        try {
            return $request->validate($rules);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed.', 'details' => $e->errors()], 422);
        }
    }

    private function jobResponse(Job $job, string $message): JsonResponse
    {
        $job = $job->fresh(['assignees']);

        return response()->json([
            'message' => $message,
            'data' => $this->desk->apiSummary($job),
        ]);
    }

    private function notFound(int $id): JsonResponse
    {
        return response()->json(['error' => "Job #{$id} not found."], 404);
    }
}
