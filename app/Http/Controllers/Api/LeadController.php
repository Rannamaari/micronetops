<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LeadController extends Controller
{
    /**
     * POST /api/leads
     * Create a lead from the OpenClaw bot intake form.
     *
     * Accepts nested structure:
     * {
     *   "customer":     { "name", "phone", "address_text", "google_maps_location_link" },
     *   "lead":         { "business_type", "service_type", "issue_summary",
     *                     "preferred_time", "urgency", "source", "status" },
     *   "asset_details":{ "bike_model", "bike_number_plate", "vehicle_type",
     *                     "stranded_location", "ac_type", "number_of_units" },
     *   "pricing":      { "quoted_price", "currency" },
     *   "tags":         []
     * }
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                // Customer
                'customer.name'                     => ['required', 'string', 'max:255'],
                'customer.phone'                    => ['required', 'string', 'max:50'],
                'customer.address_text'             => ['nullable', 'string', 'max:500'],
                'customer.google_maps_location_link'=> ['nullable', 'string', 'max:1000'],

                // Lead
                'lead.business_type'   => ['nullable', 'string', 'max:100'],
                'lead.service_type'    => ['nullable', 'string', 'max:255'],
                'lead.issue_summary'   => ['nullable', 'string'],
                'lead.preferred_time'  => ['nullable', 'string', 'max:255'],
                'lead.urgency'         => ['nullable', 'in:low,medium,high'],
                'lead.source'          => ['nullable', 'string', 'max:100'],
                'lead.status'          => ['nullable', 'in:new,contacted,interested,qualified'],

                // Asset details
                'asset_details.bike_model'        => ['nullable', 'string', 'max:255'],
                'asset_details.bike_number_plate' => ['nullable', 'string', 'max:100'],
                'asset_details.vehicle_type'      => ['nullable', 'string', 'max:100'],
                'asset_details.stranded_location' => ['nullable', 'string', 'max:500'],
                'asset_details.ac_type'           => ['nullable', 'string', 'max:100'],
                'asset_details.number_of_units'   => ['nullable', 'integer', 'min:1'],

                // Pricing
                'pricing.quoted_price' => ['nullable', 'numeric', 'min:0'],
                'pricing.currency'     => ['nullable', 'string', 'max:10'],

                // Tags
                'tags' => ['nullable', 'array'],
                'tags.*'=> ['string', 'max:100'],
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed.', 'details' => $e->errors()], 422);
        }

        $c      = $validated['customer'];
        $l      = $validated['lead'] ?? [];
        $assets = $validated['asset_details'] ?? [];
        $pricing = $validated['pricing'] ?? [];
        $tags   = $validated['tags'] ?? [];

        // --- Find or create customer ---
        $customer = Customer::firstOrCreate(
            ['phone' => $c['phone']],
            [
                'name'    => $c['name'],
                'address' => $c['address_text'] ?? null,
                'category'=> $this->resolveCategory($l['business_type'] ?? null),
            ]
        );

        // --- Build notes from asset details, pricing, maps link, tags ---
        $notes = $this->buildNotes($l, $assets, $pricing, $c, $tags);

        // --- Determine priority from urgency ---
        $priority = match($l['urgency'] ?? null) {
            'high'   => 'high',
            'medium' => 'medium',
            default  => 'low',
        };

        // --- Resolve admin as creator ---
        $actor = User::where('role', 'admin')->first();

        // --- Create the lead ---
        $lead = Lead::create([
            'name'         => $c['name'],
            'phone'        => $c['phone'],
            'address'      => $c['address_text'] ?? null,
            'source'       => $l['source'] ?? 'telegram_bot',
            'status'       => $l['status'] ?? 'new',
            'priority'     => $priority,
            'interested_in'=> $this->resolveCategory($l['business_type'] ?? null),
            'notes'        => $notes ?: null,
            'follow_up_date'=> now()->addDay()->toDateString(),
            'created_by'   => $actor?->id,
        ]);

        // --- Log an interaction with the full intake summary ---
        $lead->interactions()->create([
            'user_id' => $actor?->id,
            'type'    => 'other',
            'notes'   => 'Lead created via OpenClaw bot intake.' .
                         ($l['issue_summary'] ?? ''),
        ]);

        return response()->json([
            'message'     => 'Lead created successfully.',
            'lead_id'     => $lead->id,
            'customer_id' => $customer->id,
            'customer'    => [
                'name'    => $customer->name,
                'phone'   => $customer->phone,
                'created' => $customer->wasRecentlyCreated,
            ],
            'lead' => [
                'status'   => $lead->status,
                'priority' => $lead->priority,
                'source'   => $lead->source,
                'follow_up'=> $lead->follow_up_date,
            ],
        ], 201);
    }

    /**
     * GET /api/leads/pending
     * List active (unreviewed) leads ordered by priority and follow-up date.
     */
    public function pending(): JsonResponse
    {
        $leads = Lead::active()
            ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END")
            ->orderBy('follow_up_date')
            ->get(['id', 'name', 'phone', 'status', 'priority', 'interested_in',
                   'source', 'follow_up_date', 'notes', 'created_at']);

        return response()->json([
            'total' => $leads->count(),
            'data'  => $leads->map(fn($l) => [
                'id'          => $l->id,
                'name'        => $l->name,
                'phone'       => $l->phone,
                'status'      => $l->status,
                'priority'    => $l->priority,
                'unit'        => $l->interested_in === 'ac' ? 'Micro Cool' : 'Micro Moto',
                'source'      => $l->source,
                'follow_up'   => $l->follow_up_date?->format('Y-m-d'),
                'overdue'     => $l->follow_up_is_overdue,
                'notes'       => $l->notes,
                'created_at'  => $l->created_at->format('Y-m-d H:i'),
            ]),
        ]);
    }

    // -------------------------------------------------------------------------

    private function resolveCategory(?string $businessType): string
    {
        if (!$businessType) return 'moto';
        $bt = strtolower($businessType);
        return (str_contains($bt, 'ac') || str_contains($bt, 'cool') || str_contains($bt, 'air'))
            ? 'ac' : 'moto';
    }

    private function buildNotes(array $l, array $assets, array $pricing, array $c, array $tags): string
    {
        $parts = [];

        if (!empty($l['service_type']))   $parts[] = "Service: {$l['service_type']}";
        if (!empty($l['issue_summary']))  $parts[] = "Issue: {$l['issue_summary']}";
        if (!empty($l['preferred_time'])) $parts[] = "Preferred time: {$l['preferred_time']}";

        if (!empty($assets['bike_model']))        $parts[] = "Bike model: {$assets['bike_model']}";
        if (!empty($assets['bike_number_plate'])) $parts[] = "Number plate: {$assets['bike_number_plate']}";
        if (!empty($assets['vehicle_type']))      $parts[] = "Vehicle type: {$assets['vehicle_type']}";
        if (!empty($assets['stranded_location'])) $parts[] = "Stranded at: {$assets['stranded_location']}";
        if (!empty($assets['ac_type']))           $parts[] = "AC type: {$assets['ac_type']}";
        if (!empty($assets['number_of_units']))   $parts[] = "AC units: {$assets['number_of_units']}";

        if (!empty($pricing['quoted_price'])) {
            $currency = $pricing['currency'] ?? 'MVR';
            $parts[] = "Quoted: {$pricing['quoted_price']} {$currency}";
        }

        if (!empty($c['google_maps_location_link'])) {
            $parts[] = "Maps: {$c['google_maps_location_link']}";
        }

        if (!empty($tags)) {
            $parts[] = "Tags: " . implode(', ', $tags);
        }

        return implode("\n", $parts);
    }
}
