<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\InventoryItem;
use App\Models\PurchaseOrder;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizePurchaseOrders();

        $query = PurchaseOrder::query()->with(['vendor', 'creator'])->latestFirst();

        if ($request->filled('business_unit')) {
            $query->where('business_unit', $request->string('business_unit')->toString());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('vendor')) {
            $vendor = mb_strtolower(trim((string) $request->get('vendor')));
            $query->where(function ($builder) use ($vendor) {
                $builder->whereRaw('LOWER(vendor_name) LIKE ?', ['%' . $vendor . '%'])
                    ->orWhereHas('vendor', function ($vendorQuery) use ($vendor) {
                        $vendorQuery->whereRaw('LOWER(name) LIKE ?', ['%' . $vendor . '%']);
                    });
            });
        }

        $purchaseOrders = $query->paginate(20)->withQueryString();

        return view('sales.purchase-orders.index', [
            'purchaseOrders' => $purchaseOrders,
            'businessUnits' => $this->businessUnits(),
            'statuses' => $this->statuses(),
            'filters' => [
                'business_unit' => (string) $request->get('business_unit', ''),
                'status' => (string) $request->get('status', ''),
                'vendor' => (string) $request->get('vendor', ''),
            ],
        ]);
    }

    public function create()
    {
        $this->authorizePurchaseOrders();

        return view('sales.purchase-orders.create', $this->formViewData(new PurchaseOrder([
            'order_date' => now()->toDateString(),
            'status' => PurchaseOrder::STATUS_DRAFT,
            'terms' => 'Please supply the items listed below as per agreed pricing and availability.',
        ])));
    }

    public function store(Request $request)
    {
        $this->authorizePurchaseOrders();

        $validated = $this->validatePurchaseOrder($request);

        $purchaseOrder = DB::transaction(function () use ($validated) {
            $vendor = !empty($validated['vendor_id']) ? Vendor::find($validated['vendor_id']) : null;

            $purchaseOrder = PurchaseOrder::create([
                'po_number' => !blank($validated['po_number'] ?? null) ? trim((string) $validated['po_number']) : null,
                'business_unit' => $validated['business_unit'],
                'vendor_id' => $vendor?->id,
                'vendor_name' => $vendor?->name ?? trim((string) ($validated['vendor_name'] ?? '')),
                'vendor_phone' => $validated['vendor_phone'] ?? $vendor?->phone,
                'vendor_contact_name' => $validated['vendor_contact_name'] ?? $vendor?->contact_name,
                'vendor_address' => $validated['vendor_address'] ?? $vendor?->address,
                'order_date' => $validated['order_date'],
                'expected_date' => $validated['expected_date'] ?? null,
                'status' => PurchaseOrder::STATUS_DRAFT,
                'reference' => $validated['reference'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'terms' => $validated['terms'] ?? null,
                'subtotal' => 0,
                'total_amount' => 0,
                'created_by' => Auth::id(),
            ]);

            foreach ($validated['lines'] as $line) {
                $description = trim((string) $line['description']);
                $quantity = round((float) $line['quantity'], 2);
                $unitCost = round((float) $line['unit_cost'], 2);

                $purchaseOrder->lines()->create([
                    'inventory_item_id' => $line['inventory_item_id'] ?? null,
                    'description' => $description,
                    'quantity' => $quantity,
                    'unit' => !empty($line['unit']) ? trim((string) $line['unit']) : null,
                    'unit_cost' => $unitCost,
                    'line_total' => round($quantity * $unitCost, 2),
                    'notes' => $line['notes'] ?? null,
                ]);
            }

            $purchaseOrder->ensureNumber();
            $purchaseOrder->recalculateTotals();

            return $purchaseOrder->fresh(['lines.inventoryItem', 'vendor', 'creator']);
        });

        ActivityLog::record('purchase_order.created', "Purchase order {$purchaseOrder->po_number} created", $purchaseOrder);

        return redirect()
            ->route('sales.purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase order created successfully.');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $this->authorizePurchaseOrders();

        $purchaseOrder->load(['lines.inventoryItem', 'vendor', 'creator']);

        return view('sales.purchase-orders.show', [
            'purchaseOrder' => $purchaseOrder,
            'brand' => $this->brandProfile($purchaseOrder->business_unit),
        ]);
    }

    public function print(PurchaseOrder $purchaseOrder)
    {
        $this->authorizePurchaseOrders();

        $purchaseOrder->load(['lines.inventoryItem', 'vendor', 'creator']);

        return view('sales.purchase-orders.print', [
            'purchaseOrder' => $purchaseOrder,
            'brand' => $this->brandProfile($purchaseOrder->business_unit),
        ]);
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        $this->authorizePurchaseOrders();

        if (!$purchaseOrder->canEdit()) {
            return back()->with('error', 'Only draft purchase orders can be edited.');
        }

        $purchaseOrder->load('lines.inventoryItem');

        return view('sales.purchase-orders.edit', $this->formViewData($purchaseOrder));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->authorizePurchaseOrders();

        if (!$purchaseOrder->canEdit()) {
            return back()->with('error', 'Only draft purchase orders can be edited.');
        }

        $validated = $this->validatePurchaseOrder($request);

        DB::transaction(function () use ($purchaseOrder, $validated) {
            $vendor = !empty($validated['vendor_id']) ? Vendor::find($validated['vendor_id']) : null;

            $purchaseOrder->update([
                'po_number' => !blank($validated['po_number'] ?? null) ? trim((string) $validated['po_number']) : $purchaseOrder->po_number,
                'business_unit' => $validated['business_unit'],
                'vendor_id' => $vendor?->id,
                'vendor_name' => $vendor?->name ?? trim((string) ($validated['vendor_name'] ?? '')),
                'vendor_phone' => $validated['vendor_phone'] ?? $vendor?->phone,
                'vendor_contact_name' => $validated['vendor_contact_name'] ?? $vendor?->contact_name,
                'vendor_address' => $validated['vendor_address'] ?? $vendor?->address,
                'order_date' => $validated['order_date'],
                'expected_date' => $validated['expected_date'] ?? null,
                'reference' => $validated['reference'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'terms' => $validated['terms'] ?? null,
            ]);

            $purchaseOrder->lines()->delete();

            foreach ($validated['lines'] as $line) {
                $quantity = round((float) $line['quantity'], 2);
                $unitCost = round((float) $line['unit_cost'], 2);

                $purchaseOrder->lines()->create([
                    'inventory_item_id' => $line['inventory_item_id'] ?? null,
                    'description' => trim((string) $line['description']),
                    'quantity' => $quantity,
                    'unit' => !empty($line['unit']) ? trim((string) $line['unit']) : null,
                    'unit_cost' => $unitCost,
                    'line_total' => round($quantity * $unitCost, 2),
                    'notes' => $line['notes'] ?? null,
                ]);
            }

            $purchaseOrder->recalculateTotals();
        });

        ActivityLog::record('purchase_order.updated', "Purchase order {$purchaseOrder->po_number} updated", $purchaseOrder);

        return redirect()
            ->route('sales.purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase order updated successfully.');
    }

    public function updateNumber(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->authorizePurchaseOrders();

        $validated = $request->validate([
            'po_number' => [
                'required',
                'string',
                'max:100',
                Rule::unique('purchase_orders', 'po_number')->ignore($purchaseOrder->id),
            ],
        ]);

        $purchaseOrder->update([
            'po_number' => trim((string) $validated['po_number']),
        ]);

        ActivityLog::record('purchase_order.number_updated', "Purchase order {$purchaseOrder->po_number} number amended", $purchaseOrder);

        return back()->with('success', 'Purchase order number updated.');
    }

    public function resubmit(PurchaseOrder $purchaseOrder)
    {
        $this->authorizePurchaseOrders();

        if ($purchaseOrder->status !== PurchaseOrder::STATUS_CANCELLED) {
            return back()->with('error', 'Only cancelled purchase orders can be resubmitted as a new PO.');
        }

        $purchaseOrder->loadMissing('lines');

        $newPurchaseOrder = DB::transaction(function () use ($purchaseOrder) {
            $newPurchaseOrder = PurchaseOrder::create([
                'business_unit' => $purchaseOrder->business_unit,
                'vendor_id' => $purchaseOrder->vendor_id,
                'vendor_name' => $purchaseOrder->vendor_name,
                'vendor_phone' => $purchaseOrder->vendor_phone,
                'vendor_contact_name' => $purchaseOrder->vendor_contact_name,
                'vendor_address' => $purchaseOrder->vendor_address,
                'order_date' => now()->toDateString(),
                'expected_date' => $purchaseOrder->expected_date,
                'status' => PurchaseOrder::STATUS_DRAFT,
                'reference' => $purchaseOrder->reference,
                'notes' => $purchaseOrder->notes,
                'terms' => $purchaseOrder->terms,
                'subtotal' => 0,
                'total_amount' => 0,
                'created_by' => Auth::id(),
                'issued_at' => null,
            ]);

            foreach ($purchaseOrder->lines as $line) {
                $newPurchaseOrder->lines()->create([
                    'inventory_item_id' => $line->inventory_item_id,
                    'description' => $line->description,
                    'quantity' => $line->quantity,
                    'unit' => $line->unit,
                    'unit_cost' => $line->unit_cost,
                    'line_total' => $line->line_total,
                    'notes' => $line->notes,
                ]);
            }

            $newPurchaseOrder->ensureNumber();
            $newPurchaseOrder->recalculateTotals();

            return $newPurchaseOrder;
        });

        ActivityLog::record(
            'purchase_order.resubmitted',
            "Purchase order {$purchaseOrder->po_number} resubmitted as {$newPurchaseOrder->po_number}",
            $newPurchaseOrder
        );

        return redirect()
            ->route('sales.purchase-orders.show', $newPurchaseOrder)
            ->with('success', "Cancelled PO copied into new draft {$newPurchaseOrder->po_number}.");
    }

    public function issue(PurchaseOrder $purchaseOrder)
    {
        $this->authorizePurchaseOrders();

        if ($purchaseOrder->status !== PurchaseOrder::STATUS_DRAFT) {
            return back()->with('error', 'Only draft purchase orders can be marked as issued.');
        }

        $purchaseOrder->update([
            'status' => PurchaseOrder::STATUS_ISSUED,
            'issued_at' => now(),
        ]);

        ActivityLog::record('purchase_order.issued', "Purchase order {$purchaseOrder->po_number} marked as issued", $purchaseOrder);

        return back()->with('success', 'Purchase order marked as issued.');
    }

    public function cancel(PurchaseOrder $purchaseOrder)
    {
        $this->authorizePurchaseOrders();

        if ($purchaseOrder->status === PurchaseOrder::STATUS_CANCELLED) {
            return back()->with('error', 'This purchase order is already cancelled.');
        }

        $purchaseOrder->update([
            'status' => PurchaseOrder::STATUS_CANCELLED,
        ]);

        ActivityLog::record('purchase_order.cancelled', "Purchase order {$purchaseOrder->po_number} cancelled", $purchaseOrder);

        return back()->with('success', 'Purchase order cancelled.');
    }

    private function authorizePurchaseOrders(): void
    {
        abort_unless(Auth::user()?->hasAnyRole(['admin', 'manager']), 403);
    }

    private function formViewData(PurchaseOrder $purchaseOrder): array
    {
        return [
            'purchaseOrder' => $purchaseOrder,
            'businessUnits' => $this->businessUnits(),
            'vendors' => Vendor::where('is_active', true)->orderBy('name')->get(),
            'inventoryItems' => InventoryItem::active()->parts()->orderBy('name')->get(),
        ];
    }

    private function validatePurchaseOrder(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'po_number' => ['nullable', 'string', 'max:100', Rule::unique('purchase_orders', 'po_number')->ignore($request->route('purchaseOrder')?->id)],
            'business_unit' => ['required', Rule::in(array_keys($this->businessUnits()))],
            'vendor_id' => ['nullable', 'integer', 'exists:vendors,id'],
            'vendor_name' => ['nullable', 'string', 'max:255'],
            'vendor_phone' => ['nullable', 'string', 'max:50'],
            'vendor_contact_name' => ['nullable', 'string', 'max:255'],
            'vendor_address' => ['nullable', 'string', 'max:500'],
            'order_date' => ['required', 'date'],
            'expected_date' => ['nullable', 'date', 'after_or_equal:order_date'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'terms' => ['nullable', 'string', 'max:2000'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.inventory_item_id' => ['nullable', 'integer', 'exists:inventory_items,id'],
            'lines.*.description' => ['required', 'string', 'max:255'],
            'lines.*.quantity' => ['required', 'numeric', 'gt:0'],
            'lines.*.unit' => ['nullable', 'string', 'max:50'],
            'lines.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'lines.*.notes' => ['nullable', 'string', 'max:500'],
        ]);

        $validator->after(function ($validator) use ($request) {
            if (!$request->filled('vendor_id') && blank($request->input('vendor_name'))) {
                $validator->errors()->add('vendor_name', 'Select a vendor or enter a supplier name.');
            }
        });

        $validated = $validator->validate();

        $validated['lines'] = collect($validated['lines'])
            ->filter(fn (array $line) => !blank($line['description'] ?? null))
            ->values()
            ->all();

        if (count($validated['lines']) === 0) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'lines' => 'Add at least one purchase order line.',
            ]);
        }

        return $validated;
    }

    private function businessUnits(): array
    {
        return [
            'moto' => 'Micro Moto',
            'cool' => 'Micro Cool',
            'it' => 'Micronet',
            'easyfix' => 'Micronet - Easy Fix',
        ];
    }

    private function statuses(): array
    {
        return [
            PurchaseOrder::STATUS_DRAFT => 'Draft',
            PurchaseOrder::STATUS_ISSUED => 'Issued',
            PurchaseOrder::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    private function brandProfile(string $businessUnit): array
    {
        return [
            'name' => 'Micronet',
            'tagline' => 'IT & Technical Services',
            'address' => 'Janavaree Hingun, Near Dharubaaruge',
            'phone' => '+960 9996210',
            'email' => 'hello@micronet.mv',
            'website' => 'micronet.mv',
        ];
    }
}
