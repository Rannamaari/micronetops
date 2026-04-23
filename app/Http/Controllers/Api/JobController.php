<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\InventoryLog;
use App\Models\Job;
use App\Models\JobItem;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class JobController extends Controller
{
    /**
     * POST /api/jobs
     * Create a job for a customer (bot-friendly).
     *
     * Body:
     * {
     *   "job_type": "it",                // moto|ac|it|easyfix
     *   "customer_id": 123,              // optional
     *   "customer_name": "Ahmed",        // required if no customer_id
     *   "customer_phone": "7777777",     // required if no customer_id (7 digits ok; stored as given)
     *   "customer_gst_number": "...",    // optional
     *   "title": "Router setup",         // optional
     *   "problem_description": "...",    // optional
     *   "customer_notes": "...",         // optional (printed on quotation/invoice)
     *   "location": "...",               // optional
     *   "priority": "normal",            // urgent|high|normal|low
     *   "scheduled_at": "2026-04-23 14:00:00", // optional
     *   "scheduled_end_at": "...",       // optional
     *   "due_date": "2026-04-30"         // optional; null => due upon receipt
     * }
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'job_type' => ['required', Rule::in(['moto', 'ac', 'it', 'easyfix'])],
                'customer_id' => ['nullable', 'exists:customers,id'],
                'customer_name' => ['nullable', 'string', 'max:255'],
                'customer_phone' => ['nullable', 'string', 'max:50'],
                'customer_gst_number' => ['nullable', 'string', 'max:50'],
                'title' => ['nullable', 'string', 'max:100'],
                'problem_description' => ['nullable', 'string'],
                'customer_notes' => ['nullable', 'string', 'max:2000'],
                'location' => ['nullable', 'string', 'max:255'],
                'priority' => ['nullable', Rule::in(array_keys(Job::getPriorities()))],
                'scheduled_at' => ['nullable', 'date'],
                'scheduled_end_at' => ['nullable', 'date', 'after_or_equal:scheduled_at'],
                'due_date' => ['nullable', 'date'],
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed.', 'details' => $e->errors()], 422);
        }

        if (empty($validated['customer_id']) && (empty($validated['customer_phone']) || empty($validated['customer_name']))) {
            return response()->json([
                'error' => 'Provide either customer_id OR both customer_name and customer_phone.',
            ], 422);
        }

        // API actor (matches existing API style)
        $actor = User::where('role', 'admin')->first();
        if (!$actor) {
            return response()->json(['error' => 'No admin user found to act as API actor.'], 500);
        }
        Auth::setUser($actor);

        // Resolve or create customer
        $customer = null;
        if (!empty($validated['customer_id'])) {
            $customer = Customer::find($validated['customer_id']);
        } elseif (!empty($validated['customer_phone'])) {
            $customer = Customer::firstOrCreate(
                ['phone' => $validated['customer_phone']],
                [
                    'name' => $validated['customer_name'] ?? 'Unknown',
                    'address' => $validated['location'] ?? null,
                    'gst_number' => $validated['customer_gst_number'] ?? null,
                    'category' => match ($validated['job_type']) {
                        'ac' => 'ac',
                        'it' => 'it',
                        'easyfix' => 'easyfix',
                        default => 'moto',
                    },
                ]
            );

            // If customer existed, optionally fill missing GST/address/name (non-destructive).
            if ($customer && !$customer->wasRecentlyCreated) {
                $changed = false;
                if (!empty($validated['customer_gst_number']) && empty($customer->gst_number)) {
                    $customer->gst_number = $validated['customer_gst_number'];
                    $changed = true;
                }
                if (!empty($validated['location']) && empty($customer->address)) {
                    $customer->address = $validated['location'];
                    $changed = true;
                }
                if (!empty($validated['customer_name']) && ($customer->name === 'Unknown' || empty($customer->name))) {
                    $customer->name = $validated['customer_name'];
                    $changed = true;
                }
                if ($changed) $customer->save();
            }
        }

        $status = Job::STATUS_NEW;
        if (!empty($validated['scheduled_at'])) {
            $status = Job::STATUS_SCHEDULED;
        }

        $job = Job::create([
            'title' => $validated['title'] ?? null,
            'scheduled_at' => $validated['scheduled_at'] ?? null,
            'scheduled_end_at' => $validated['scheduled_end_at'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'location' => $validated['location'] ?? null,
            'priority' => $validated['priority'] ?? Job::PRIORITY_NORMAL,

            'job_date' => now()->toDateString(),
            'job_type' => $validated['job_type'],
            'job_category' => 'general',
            'customer_id' => $customer?->id,

            'customer_name' => $customer?->name ?? $validated['customer_name'] ?? 'Walk-in',
            'customer_phone' => $customer?->phone ?? $validated['customer_phone'] ?? null,
            'customer_email' => $customer?->email ?? null,

            'vehicle_id' => null,
            'ac_unit_id' => null,

            'address' => $customer?->address ?? $validated['location'] ?? null,
            'pickup_location' => null,
            'problem_description' => $validated['problem_description'] ?? null,
            'customer_notes' => $validated['customer_notes'] ?? null,

            'status' => $status,
            'payment_status' => 'unpaid',
            'labour_total' => 0,
            'parts_total' => 0,
            'travel_charges' => 0,
            'discount' => 0,
            'gst_amount' => 0,
            'total_amount' => 0,
        ]);

        // Add a creation note (optional; relies on Job::addNote())
        try {
            $job->addNote('Job created (API)', $actor, 'system');
        } catch (\Throwable $e) {
            // Ignore if note system not available.
        }

        ActivityLog::record('job.created', "API: Job #{$job->id} created for '{$job->customer_name}'", $job, [], $actor?->id, 'api');

        return response()->json([
            'message' => 'Job created.',
            'job_id' => $job->id,
            'job_type' => $job->job_type,
            'status' => $job->status,
            'customer' => $customer ? [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
            ] : null,
            'print' => [
                'quotation_api' => url('/api/jobs/' . $job->id . '/quotation'),
                'invoice_api' => url('/api/jobs/' . $job->id . '/invoice'),
            ],
        ], 201);
    }

    /**
     * GET /api/jobs/{id}
     */
    public function show(int $id): JsonResponse
    {
        $job = Job::with('customer:id,name,phone,gst_number', 'items.inventoryItem:id,name,sku', 'payments')->find($id);
        if (!$job) {
            return response()->json(['error' => "Job #{$id} not found."], 404);
        }

        return response()->json([
            'data' => [
                'id' => $job->id,
                'job_type' => $job->job_type,
                'status' => $job->status,
                'priority' => $job->priority,
                'scheduled_at' => $job->scheduled_at?->format('Y-m-d H:i:s'),
                'due_date' => $job->due_date?->format('Y-m-d'),
                'customer' => $job->customer ? [
                    'id' => $job->customer->id,
                    'name' => $job->customer->name,
                    'phone' => $job->customer->phone,
                    'gst_number' => $job->customer->gst_number,
                ] : [
                    'name' => $job->customer_name,
                    'phone' => $job->customer_phone,
                ],
                'title' => $job->title,
                'problem_description' => $job->problem_description,
                'customer_notes' => $job->customer_notes,
                'totals' => [
                    'labour_total' => number_format((float) $job->labour_total, 2),
                    'parts_total' => number_format((float) $job->parts_total, 2),
                    'gst_amount' => number_format((float) $job->gst_amount, 2),
                    'total_amount' => number_format((float) $job->total_amount, 2),
                    'payment_status' => $job->payment_status,
                ],
                'items' => $job->items->map(fn (JobItem $i) => [
                    'id' => $i->id,
                    'inventory_item_id' => $i->inventory_item_id,
                    'item_name' => $i->item_name,
                    'item_description' => $i->item_description,
                    'is_service' => (bool) $i->is_service,
                    'is_gst_applicable' => (bool) $i->is_gst_applicable,
                    'quantity' => (float) $i->quantity,
                    'unit_price' => (float) $i->unit_price,
                    'subtotal' => (float) $i->subtotal,
                ]),
            ],
        ]);
    }

    /**
     * POST /api/jobs/{id}/items
     * Add an inventory item to a job (deducts stock for non-service items).
     *
     * Body:
     * { "inventory_item_id": 123, "quantity": 1, "unit_price": 500 }
     * OR
     * { "identifier": "SKU123", "quantity": 1, "unit_price": 500 } // resolves SKU/name
     */
    public function addItem(int $id, Request $request): JsonResponse
    {
        $job = Job::find($id);
        if (!$job) {
            return response()->json(['error' => "Job #{$id} not found."], 404);
        }

        try {
            $validated = $request->validate([
                'inventory_item_id' => ['nullable', 'exists:inventory_items,id'],
                'identifier' => ['nullable', 'string', 'max:255'],
                'quantity' => ['required', 'integer', 'min:1'],
                'unit_price' => ['nullable', 'numeric', 'min:0'],
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed.', 'details' => $e->errors()], 422);
        }

        if (empty($validated['inventory_item_id']) && empty($validated['identifier'])) {
            return response()->json(['error' => 'Provide inventory_item_id or identifier.'], 422);
        }

        $actor = User::where('role', 'admin')->first();
        if (!$actor) {
            return response()->json(['error' => 'No admin user found to act as API actor.'], 500);
        }
        Auth::setUser($actor);

        $inventoryItem = null;
        if (!empty($validated['inventory_item_id'])) {
            $inventoryItem = InventoryItem::find($validated['inventory_item_id']);
        } elseif (!empty($validated['identifier'])) {
            $inventoryItem = InventoryItem::where('sku', $validated['identifier'])->first()
                ?? InventoryItem::whereRaw('lower(name) like ?', ['%' . strtolower($validated['identifier']) . '%'])->first();
        }

        if (!$inventoryItem) {
            return response()->json(['error' => 'Inventory item not found.'], 404);
        }

        $qty = (int) $validated['quantity'];
        $unitPrice = $validated['unit_price'] !== null ? (float) $validated['unit_price'] : (float) $inventoryItem->sell_price;
        $subtotal = round($qty * $unitPrice, 2);

        $itemDescription = trim(
            ($inventoryItem->brand ?? '') .
            ($inventoryItem->brand && $inventoryItem->sku ? ' - ' : '') .
            ($inventoryItem->sku ?? '')
        );

        $jobItem = JobItem::create([
            'job_id' => $job->id,
            'inventory_item_id' => $inventoryItem->id,
            'item_name' => $inventoryItem->name,
            'item_description' => $itemDescription ?: null,
            'is_service' => (bool) $inventoryItem->is_service,
            'is_gst_applicable' => (bool) $inventoryItem->has_gst,
            'quantity' => $qty,
            'unit_price' => $unitPrice,
            'subtotal' => $subtotal,
        ]);

        if (!$inventoryItem->is_service) {
            $inventoryItem->quantity = (int) $inventoryItem->quantity - $qty;
            $inventoryItem->save();

            InventoryLog::create([
                'inventory_item_id' => $inventoryItem->id,
                'job_id' => $job->id,
                'quantity_change' => -$qty,
                'type' => 'sale',
                'user_id' => Auth::id(),
                'notes' => 'Used on job #' . $job->id . ' (API)',
            ]);
        }

        $job->recalculateTotals();

        return response()->json([
            'message' => 'Item added.',
            'job_id' => $job->id,
            'job_item_id' => $jobItem->id,
            'totals' => [
                'gst_amount' => number_format((float) $job->fresh()->gst_amount, 2),
                'total_amount' => number_format((float) $job->fresh()->total_amount, 2),
            ],
        ], 201);
    }

    /**
     * DELETE /api/jobs/{id}/items/{itemId}
     * Removes a job item (returns stock for non-service items).
     */
    public function removeItem(int $id, int $itemId): JsonResponse
    {
        $job = Job::find($id);
        if (!$job) {
            return response()->json(['error' => "Job #{$id} not found."], 404);
        }

        $item = JobItem::with('inventoryItem')->find($itemId);
        if (!$item || (int) $item->job_id !== (int) $job->id) {
            return response()->json(['error' => "Job item #{$itemId} not found for job #{$id}."], 404);
        }

        $actor = User::where('role', 'admin')->first();
        if ($actor) Auth::setUser($actor);

        $inventoryItem = $item->inventoryItem;
        $qty = (float) $item->quantity;
        if ($inventoryItem && !$item->is_service) {
            $inventoryItem->quantity = (float) $inventoryItem->quantity + $qty;
            $inventoryItem->save();

            InventoryLog::create([
                'inventory_item_id' => $inventoryItem->id,
                'job_id' => $job->id,
                'quantity_change' => $qty,
                'type' => 'return',
                'user_id' => Auth::id(),
                'notes' => 'Removed from job #' . $job->id . ' (API)',
            ]);
        }

        $item->delete();
        $job->recalculateTotals();

        return response()->json([
            'message' => 'Item removed.',
            'job_id' => $job->id,
            'job_item_id' => $itemId,
        ]);
    }

    /**
     * GET /api/jobs/{id}/quotation
     * Returns HTML content of the quotation (for bot export/print).
     */
    public function quotationHtml(int $id): JsonResponse
    {
        $job = Job::with(['customer', 'vehicle', 'acUnit', 'items.inventoryItem'])->find($id);
        if (!$job) {
            return response()->json(['error' => "Job #{$id} not found."], 404);
        }

        $brand = $this->brandFor($job);
        $quotationNumber = 'QUO-' . str_pad($job->id, 5, '0', STR_PAD_LEFT);

        $html = view('jobs.quotation', [
            'job' => $job,
            'brand' => $brand,
            'quotationNumber' => $quotationNumber,
        ])->render();

        return response()->json([
            'job_id' => $job->id,
            'type' => 'quotation',
            'number' => $quotationNumber,
            'html' => $html,
        ]);
    }

    /**
     * GET /api/jobs/{id}/invoice
     * Returns HTML content of the invoice (for bot export/print).
     */
    public function invoiceHtml(int $id): JsonResponse
    {
        $job = Job::with(['customer', 'vehicle', 'acUnit', 'items.inventoryItem', 'payments'])->find($id);
        if (!$job) {
            return response()->json(['error' => "Job #{$id} not found."], 404);
        }

        $brand = $this->brandFor($job);
        $invoiceNumber = 'JOB-' . str_pad($job->id, 5, '0', STR_PAD_LEFT);

        $html = view('jobs.invoice', [
            'job' => $job,
            'brand' => $brand,
            'invoiceNumber' => $invoiceNumber,
        ])->render();

        return response()->json([
            'job_id' => $job->id,
            'type' => 'invoice',
            'number' => $invoiceNumber,
            'html' => $html,
        ]);
    }

    private function brandFor(Job $job): array
    {
        return match ($job->job_type) {
            'ac' => [
                'name' => 'Micro Cool',
                'tagline' => 'We Fix, You Chill',
                'address' => 'Janavaree Hingun, Near Dharubaaruge',
                'phone' => '+960 9996210',
                'email' => 'hello@micronet.mv',
                'website' => 'cool.micronet.mv',
            ],
            'it' => [
                'name' => 'Micronet',
                'tagline' => 'IT & Technical Services',
                'address' => 'Janavaree Hingun, Near Dharubaaruge',
                'phone' => '+960 9996210',
                'email' => 'hello@micronet.mv',
                'website' => 'micronet.mv',
            ],
            'easyfix' => [
                'name' => 'Micronet - Easy Fix',
                'tagline' => 'Handyman Services in Greater Male Area',
                'address' => 'Janavaree Hingun, Near Dharubaaruge',
                'phone' => '+960 9996210',
                'email' => 'hello@micronet.mv',
                'website' => 'easyfix.mv',
            ],
            default => [
                'name' => 'Micro Moto Garage',
                'tagline' => 'Affordable & Reliable Motorbike Care',
                'address' => 'Janavaree Hingun, Near Dharubaaruge',
                'phone' => '+960 9996210',
                'email' => 'hello@micronet.mv',
                'website' => 'garage.micronet.mv',
            ],
        };
    }
}
