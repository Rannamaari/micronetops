<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Customer;
use App\Models\User;
use App\Models\LeadInteraction;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    /**
     * List leads with search and filters.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $statusFilter = $request->get('status', 'all');
        $priorityFilter = $request->get('priority', 'all');
        $assignedFilter = $request->get('assigned', 'all');

        // Auto-archive leads with no interaction in 60 days (active leads only)
        Lead::whereIn('status', ['new', 'contacted', 'interested', 'qualified'])
            ->where('archived', false)
            ->where(function ($q) {
                $q->where('last_contact_at', '<', now()->subDays(60))
                  ->orWhere(function ($q2) {
                      $q2->whereNull('last_contact_at')
                         ->where('created_at', '<', now()->subDays(60));
                  });
            })
            ->update([
                'archived' => true,
                'archived_at' => now(),
            ]);

        $query = Lead::with(['createdBy', 'convertedToCustomer', 'assignedUser']);

        // Handle archived tab
        if ($statusFilter === 'archived') {
            $query->archived();
        } else {
            $query->notArchived();
            $query->byStatus($statusFilter)
                  ->byPriority($priorityFilter)
                  ->search($search);

            // Filter by assigned user
            if ($assignedFilter && $assignedFilter !== 'all') {
                $query->assignedTo($assignedFilter);
            }
        }

        if ($statusFilter === 'archived') {
            // Still apply search to archived
            $query->search($search);
        }

        $leads = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        // Get status counts (exclude archived from normal counts)
        $statusCounts = [
            'all' => Lead::notArchived()->count(),
            'new' => Lead::notArchived()->where('status', 'new')->count(),
            'contacted' => Lead::notArchived()->where('status', 'contacted')->count(),
            'interested' => Lead::notArchived()->where('status', 'interested')->count(),
            'qualified' => Lead::notArchived()->where('status', 'qualified')->count(),
            'converted' => Lead::notArchived()->where('status', 'converted')->count(),
            'lost' => Lead::notArchived()->where('status', 'lost')->count(),
            'archived' => Lead::archived()->count(),
        ];

        $users = User::whereIn('role', ['admin', 'manager', 'mechanic'])
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('leads.index', compact('leads', 'search', 'statusFilter', 'priorityFilter', 'assignedFilter', 'statusCounts', 'users'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        $users = User::whereIn('role', ['admin', 'manager', 'mechanic'])
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('leads.create', compact('users'));
    }

    /**
     * Store a new lead.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'phone'         => ['required', 'string', 'max:50'],
            'email'         => ['nullable', 'email', 'max:255'],
            'address'       => ['nullable', 'string', 'max:500'],
            'source'        => ['required', 'string', 'in:website,referral,social,walk-in,phone,whatsapp,other'],
            'priority'      => ['required', 'string', 'in:high,medium,low'],
            'interested_in' => ['required', 'string', 'in:moto,ac,both'],
            'notes'         => ['nullable', 'string'],
            'follow_up_date' => ['nullable', 'date'],
            'assigned_user_id' => ['nullable', 'exists:users,id'],
        ]);

        $validated['created_by'] = auth()->id();
        $validated['status'] = 'new';

        // Set follow-up date to 24 hours from now if not provided
        if (empty($validated['follow_up_date'])) {
            $validated['follow_up_date'] = now()->addDay()->format('Y-m-d');
        }

        $lead = Lead::create($validated);

        // Create initial interaction
        $lead->interactions()->create([
            'user_id' => auth()->id(),
            'type' => 'other',
            'notes' => 'Lead created from ' . $validated['source'],
        ]);

        return redirect()
            ->route('leads.show', $lead)
            ->with('success', 'Lead created successfully.');
    }

    /**
     * Show a single lead with interactions.
     */
    public function show(Lead $lead)
    {
        $lead->load(['interactions.user', 'createdBy', 'convertedToCustomer', 'assignedUser', 'lostByUser']);
        $lostReasons = Lead::LOST_REASONS;

        return view('leads.show', compact('lead', 'lostReasons'));
    }

    /**
     * Show edit form.
     */
    public function edit(Lead $lead)
    {
        $users = User::whereIn('role', ['admin', 'manager', 'mechanic'])
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('leads.edit', compact('lead', 'users'));
    }

    /**
     * Update lead.
     */
    public function update(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'phone'         => ['required', 'string', 'max:50'],
            'email'         => ['nullable', 'email', 'max:255'],
            'address'       => ['nullable', 'string', 'max:500'],
            'source'        => ['required', 'string', 'in:website,referral,social,walk-in,phone,whatsapp,other'],
            'status'        => ['required', 'string', 'in:new,contacted,interested,qualified,converted,lost'],
            'priority'      => ['required', 'string', 'in:high,medium,low'],
            'interested_in' => ['required', 'string', 'in:moto,ac,both'],
            'notes'         => ['nullable', 'string'],
            'follow_up_date' => ['nullable', 'date'],
            'assigned_user_id' => ['nullable', 'exists:users,id'],
        ]);

        $lead->update($validated);

        return redirect()
            ->route('leads.show', $lead)
            ->with('success', 'Lead updated successfully.');
    }

    /**
     * Delete a lead.
     */
    public function destroy(Lead $lead)
    {
        // Don't allow deletion of converted leads
        if ($lead->status === 'converted') {
            return back()->with('error', 'Cannot delete a converted lead. Archive it instead.');
        }

        $lead->delete();

        return redirect()
            ->route('leads.index')
            ->with('success', 'Lead deleted successfully.');
    }

    /**
     * Archive a lead.
     */
    public function archive(Lead $lead)
    {
        $lead->archive();

        return back()->with('success', 'Lead archived successfully.');
    }

    /**
     * Unarchive a lead.
     */
    public function unarchive(Lead $lead)
    {
        $lead->unarchive();

        return back()->with('success', 'Lead restored from archive.');
    }

    /**
     * Bulk action on multiple leads.
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', 'string', 'in:archive,delete'],
            'lead_ids' => ['required', 'array'],
            'lead_ids.*' => ['integer', 'exists:leads,id'],
        ]);

        $leads = Lead::whereIn('id', $validated['lead_ids'])->get();
        $count = 0;

        if ($validated['action'] === 'archive') {
            foreach ($leads as $lead) {
                $lead->archive();
                $count++;
            }
            return back()->with('success', $count . ' lead(s) archived.');
        }

        if ($validated['action'] === 'delete') {
            // Only admin can bulk delete
            if (!auth()->user()->canDelete()) {
                return back()->with('error', 'Only admins can delete leads.');
            }

            foreach ($leads as $lead) {
                // Skip converted leads
                if ($lead->status === 'converted') {
                    continue;
                }
                $lead->delete();
                $count++;
            }
            return back()->with('success', $count . ' lead(s) deleted.');
        }

        return back();
    }

    /**
     * Convert lead to customer.
     */
    public function convertToCustomer(Request $request, Lead $lead)
    {
        // Check if already converted
        if ($lead->status === 'converted') {
            return back()->with('error', 'This lead has already been converted.');
        }

        // Check if customer with same phone exists
        $existingCustomer = Customer::where('phone', $lead->phone)->first();

        if ($existingCustomer) {
            // Link to existing customer
            $lead->update([
                'status' => 'converted',
                'converted_to_customer_id' => $existingCustomer->id,
                'converted_at' => now(),
            ]);

            $lead->interactions()->create([
                'user_id' => auth()->id(),
                'type' => 'other',
                'notes' => 'Lead linked to existing customer #' . $existingCustomer->id,
            ]);

            return redirect()
                ->route('customers.show', $existingCustomer)
                ->with('success', 'Lead converted and linked to existing customer.');
        }

        // Create new customer
        $customer = $lead->convertToCustomer();

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', 'Lead converted to customer successfully!');
    }

    /**
     * Record an interaction with the lead.
     */
    public function recordInteraction(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'type'  => ['required', 'string', 'in:call,email,sms,whatsapp,meeting,other'],
            'notes' => ['required', 'string'],
            'next_follow_up' => ['nullable', 'date'],
        ]);

        $lead->interactions()->create([
            'user_id' => auth()->id(),
            'type' => $validated['type'],
            'notes' => $validated['notes'],
        ]);

        // Update last contact timestamp and increment call attempts if it's a call
        $updateData = ['last_contact_at' => now()];

        if ($validated['type'] === 'call') {
            $updateData['call_attempts'] = $lead->call_attempts + 1;
        }

        // Update follow-up date if provided, otherwise set to tomorrow
        if (!empty($validated['next_follow_up'])) {
            $updateData['follow_up_date'] = $validated['next_follow_up'];
        } else {
            $updateData['follow_up_date'] = now()->addDay()->format('Y-m-d');
        }

        $lead->update($updateData);

        // Warn if call attempts are high
        if ($lead->call_attempts >= 3) {
            return back()->with('warning', 'This lead has been called 3+ times. Consider marking as lost if not interested.');
        }

        return back()->with('success', 'Interaction recorded and follow-up scheduled for ' . $lead->fresh()->follow_up_date->format('M d, Y'));
    }

    /**
     * Mark lead as lost with reason.
     */
    public function markAsLost(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'lost_reason_id' => ['required', 'string', 'in:' . implode(',', array_keys(Lead::LOST_REASONS))],
            'lost_notes' => ['nullable', 'string', 'max:500'],
            'do_not_contact' => ['nullable', 'boolean'],
        ]);

        if (!empty($validated['do_not_contact'])) {
            $lead->markAsDoNotContact();
            return redirect()
                ->route('leads.index')
                ->with('success', 'Lead marked as "Do Not Contact".');
        }

        $lead->markAsLost($validated['lost_reason_id'], $validated['lost_notes'] ?? null);

        return redirect()
            ->route('leads.index')
            ->with('success', 'Lead marked as lost.');
    }

    /**
     * Reopen a lost lead.
     */
    public function reopen(Lead $lead)
    {
        $lead->reopen();

        return back()->with('success', 'Lead reopened successfully.');
    }

    /**
     * Quick update lead status.
     */
    public function updateStatus(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:new,contacted,interested,qualified,lost'],
        ]);

        $oldStatus = $lead->status;
        $lead->update(['status' => $validated['status']]);

        // Log the status change
        $lead->interactions()->create([
            'user_id' => auth()->id(),
            'type' => 'other',
            'notes' => 'Status changed from ' . $oldStatus . ' to ' . $validated['status'],
        ]);

        return back()->with('success', 'Lead status updated to ' . ucfirst($validated['status']));
    }
}
