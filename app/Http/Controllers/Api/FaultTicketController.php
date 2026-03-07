<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\FaultTicket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FaultTicketController extends Controller
{
    /**
     * GET /api/faults
     * List tickets. Defaults to open only. Filter by status or unit.
     *
     * Query: ?status=open|in_progress|resolved|closed|all  &business_unit=moto|cool
     */
    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status', 'open');
        $unit   = $request->query('business_unit');

        $query = FaultTicket::with('assignee:id,name', 'customer:id,name,phone');

        if ($status === 'open') {
            $query->open();
        } elseif ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($unit) {
            $query->forUnit($unit);
        }

        $tickets = $query->orderByRaw("CASE priority WHEN 'urgent' THEN 1 ELSE 2 END")
            ->orderBy('deadline_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'total'  => $tickets->count(),
            'status' => $status,
            'data'   => $tickets->map(fn($t) => $this->format($t)),
        ]);
    }

    /**
     * POST /api/faults
     * Create a new fault ticket.
     *
     * Body:
     * {
     *   "title":         "AC unit not cooling",
     *   "description":   "Customer reports unit installed last week is not cooling",
     *   "business_unit": "cool",
     *   "priority":      "urgent",
     *   "customer_name": "Ahmed Ali",
     *   "customer_phone":"7001234",
     *   "deadline_hours": 24,           // optional, hours from now (default: 48)
     *   "assign_to":     "Ali Mohamed"  // optional, staff name or user ID
     * }
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'title'          => ['required', 'string', 'max:255'],
                'description'    => ['required', 'string'],
                'business_unit'  => ['required', 'in:moto,cool'],
                'priority'       => ['nullable', 'in:urgent,normal'],
                'customer_name'  => ['required', 'string', 'max:255'],
                'customer_phone' => ['required', 'string', 'max:50'],
                'deadline_hours' => ['nullable', 'integer', 'min:1'],
                'assign_to'      => ['nullable', 'string', 'max:255'],
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed.', 'details' => $e->errors()], 422);
        }

        $actor    = User::where('role', 'admin')->first();
        $customer = Customer::firstOrCreate(
            ['phone' => $validated['customer_phone']],
            [
                'name'     => $validated['customer_name'],
                'category' => $validated['business_unit'] === 'cool' ? 'ac' : 'moto',
            ]
        );

        $assignee   = $this->resolveAssignee($validated['assign_to'] ?? null, $validated['business_unit']);
        $deadlineHrs = $validated['deadline_hours'] ?? 48;

        $ticket = FaultTicket::create([
            'business_unit'  => $validated['business_unit'],
            'priority'       => $validated['priority'] ?? 'normal',
            'status'         => 'open',
            'customer_id'    => $customer->id,
            'customer_name'  => $customer->name,
            'customer_phone' => $customer->phone,
            'title'          => $validated['title'],
            'description'    => $validated['description'],
            'deadline_at'    => Carbon::now()->addHours($deadlineHrs),
            'created_by'     => $actor?->id,
            'assigned_to'    => $assignee?->id,
        ]);

        return response()->json([
            'message'       => "Fault ticket {$ticket->ticket_number} created.",
            'ticket_number' => $ticket->ticket_number,
            'ticket_id'     => $ticket->id,
            'customer_id'   => $customer->id,
            'assigned_to'   => $assignee?->name ?? 'Unassigned',
            'deadline'      => $ticket->deadline_at->format('Y-m-d H:i'),
            'status'        => $ticket->status,
            'priority'      => $ticket->priority,
        ], 201);
    }

    /**
     * PATCH /api/faults/{id}/assign
     * Assign or reassign a ticket to a staff member.
     *
     * Body: { "assign_to": "Ali Mohamed" }  (name or user ID)
     */
    public function assign(int $id, Request $request): JsonResponse
    {
        $ticket = FaultTicket::find($id);
        if (!$ticket) {
            return response()->json(['error' => "Ticket #{$id} not found."], 404);
        }

        try {
            $validated = $request->validate([
                'assign_to' => ['required', 'string', 'max:255'],
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed.', 'details' => $e->errors()], 422);
        }

        $assignee = $this->resolveAssignee($validated['assign_to'], $ticket->business_unit);
        if (!$assignee) {
            return response()->json([
                'error' => "Could not find staff member \"{$validated['assign_to']}\". Check the name and try again.",
            ], 404);
        }

        $ticket->update([
            'assigned_to' => $assignee->id,
            'status'      => $ticket->status === 'open' ? 'in_progress' : $ticket->status,
        ]);

        return response()->json([
            'message'       => "Ticket {$ticket->ticket_number} assigned to {$assignee->name}.",
            'ticket_number' => $ticket->ticket_number,
            'assigned_to'   => $assignee->name,
            'status'        => $ticket->status,
        ]);
    }

    /**
     * PATCH /api/faults/{id}/resolve
     * Mark a ticket as resolved.
     *
     * Body: { "resolution_notes": "Replaced compressor unit." }
     */
    public function resolve(int $id, Request $request): JsonResponse
    {
        $ticket = FaultTicket::find($id);
        if (!$ticket) {
            return response()->json(['error' => "Ticket #{$id} not found."], 404);
        }

        if (in_array($ticket->status, ['resolved', 'closed'])) {
            return response()->json(['error' => "Ticket {$ticket->ticket_number} is already {$ticket->status}."], 422);
        }

        try {
            $validated = $request->validate([
                'resolution_notes' => ['required', 'string'],
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed.', 'details' => $e->errors()], 422);
        }

        $actor = User::where('role', 'admin')->first();

        $ticket->update([
            'status'           => 'resolved',
            'resolution_notes' => $validated['resolution_notes'],
            'resolved_at'      => now(),
            'resolved_by'      => $actor?->id,
        ]);

        $metSla = $ticket->metSla();

        return response()->json([
            'message'          => "Ticket {$ticket->ticket_number} marked as resolved.",
            'ticket_number'    => $ticket->ticket_number,
            'resolved_at'      => $ticket->resolved_at->format('Y-m-d H:i'),
            'resolution_hours' => $ticket->getResolutionHours(),
            'met_sla'          => $metSla === true ? 'Yes' : ($metSla === false ? 'No' : 'N/A'),
        ]);
    }

    /**
     * PATCH /api/faults/{id}/close
     * Close a resolved ticket.
     */
    public function close(int $id): JsonResponse
    {
        $ticket = FaultTicket::find($id);
        if (!$ticket) {
            return response()->json(['error' => "Ticket #{$id} not found."], 404);
        }

        if ($ticket->status === 'closed') {
            return response()->json(['error' => "Ticket {$ticket->ticket_number} is already closed."], 422);
        }

        $ticket->update(['status' => 'closed']);

        return response()->json([
            'message'       => "Ticket {$ticket->ticket_number} closed.",
            'ticket_number' => $ticket->ticket_number,
            'status'        => 'closed',
        ]);
    }

    /**
     * GET /api/faults/{id}
     * Get full details of a single ticket.
     */
    public function show(int $id): JsonResponse
    {
        $ticket = FaultTicket::with('assignee:id,name', 'customer:id,name,phone', 'resolver:id,name')->find($id);
        if (!$ticket) {
            return response()->json(['error' => "Ticket #{$id} not found."], 404);
        }

        return response()->json(['data' => $this->format($ticket, detailed: true)]);
    }

    // -------------------------------------------------------------------------

    private function format(FaultTicket $t, bool $detailed = false): array
    {
        $base = [
            'id'             => $t->id,
            'ticket_number'  => $t->ticket_number,
            'title'          => $t->title,
            'business_unit'  => $t->business_unit,
            'unit_label'     => $t->business_unit === 'cool' ? 'Micro Cool' : 'Micro Moto',
            'priority'       => $t->priority,
            'status'         => $t->status,
            'overdue'        => $t->isOverdue(),
            'customer'       => $t->customer_name . ' (' . $t->customer_phone . ')',
            'assigned_to'    => $t->assignee?->name ?? 'Unassigned',
            'deadline'       => $t->deadline_at?->format('Y-m-d H:i'),
            'created_at'     => $t->created_at->format('Y-m-d H:i'),
        ];

        if ($detailed) {
            $base['description']      = $t->description;
            $base['resolution_notes'] = $t->resolution_notes;
            $base['resolved_at']      = $t->resolved_at?->format('Y-m-d H:i');
            $base['resolved_by']      = $t->resolver?->name;
            $base['resolution_hours'] = $t->getResolutionHours();
            $base['met_sla']          = $t->metSla() === true ? 'Yes' : ($t->metSla() === false ? 'No' : 'N/A');
        }

        return $base;
    }

    private function resolveAssignee(?string $nameOrId, string $unit): ?User
    {
        if (!$nameOrId) return null;

        // Try by ID first
        if (is_numeric($nameOrId)) {
            return User::find((int) $nameOrId);
        }

        // Try exact name match
        $user = User::whereRaw('lower(name) = ?', [strtolower($nameOrId)])->first();
        if ($user) return $user;

        // Try partial name match scoped to relevant mechanic role
        $mechRole = $unit === 'cool' ? 'ac_mechanic' : 'moto_mechanic';
        return User::whereRaw('lower(name) like ?', ['%' . strtolower($nameOrId) . '%'])
            ->whereIn('role', [$mechRole, 'admin', 'manager'])
            ->first();
    }
}
