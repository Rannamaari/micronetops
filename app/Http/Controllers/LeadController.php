<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Customer;
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

        $query = Lead::with(['createdBy', 'convertedToCustomer']);

        // Apply filters
        $query->byStatus($statusFilter)
              ->byPriority($priorityFilter)
              ->search($search);

        $leads = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        // Get status counts for filter badges
        $statusCounts = [
            'all' => Lead::count(),
            'new' => Lead::where('status', 'new')->count(),
            'contacted' => Lead::where('status', 'contacted')->count(),
            'interested' => Lead::where('status', 'interested')->count(),
            'qualified' => Lead::where('status', 'qualified')->count(),
            'converted' => Lead::where('status', 'converted')->count(),
            'lost' => Lead::where('status', 'lost')->count(),
        ];

        return view('leads.index', compact('leads', 'search', 'statusFilter', 'priorityFilter', 'statusCounts'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        return view('leads.create');
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
        $lead->load(['interactions.user', 'createdBy', 'convertedToCustomer']);

        return view('leads.show', compact('lead'));
    }

    /**
     * Show edit form.
     */
    public function edit(Lead $lead)
    {
        return view('leads.edit', compact('lead'));
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

        $lead->update($updateData);

        // Warn if call attempts are high
        if ($lead->call_attempts >= 3) {
            return back()->with('warning', 'This lead has been called 3+ times. Consider marking as lost if not interested.');
        }

        return back()->with('success', 'Interaction recorded successfully.');
    }

    /**
     * Mark lead as lost with reason.
     */
    public function markAsLost(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'lost_reason' => ['required', 'string', 'max:500'],
            'do_not_contact' => ['nullable', 'boolean'],
        ]);

        if (!empty($validated['do_not_contact'])) {
            $lead->markAsDoNotContact();
            return redirect()
                ->route('leads.index')
                ->with('success', 'Lead marked as "Do Not Contact".');
        }

        $lead->markAsLost($validated['lost_reason']);

        return redirect()
            ->route('leads.index')
            ->with('success', 'Lead marked as lost.');
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
