<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\DailySalesLog;
use App\Models\InventoryItem;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SalesController extends Controller
{
    /**
     * POST /api/sales
     * Create a complete sale for Micro Moto (moto), Micro Cool (cool), Micronet (it), or Micronet - Easy Fix (easyfix).
     *
     * Body:
     * {
     *   "business_unit": "moto",          // "moto" = Micro Moto, "cool" = Micro Cool, "it" = Micronet, "easyfix" = Micronet - Easy Fix
     *   "customer_name":  "Ahmed Ali",     // optional (walk-in if omitted)
     *   "customer_phone": "7001234",       // optional, required if customer_name given
     *   "payment_method": "cash",          // "cash" or "transfer"
     *   "date": "2026-03-06",             // optional, defaults to today
     *   "items": [
     *     { "description": "Engine Oil Change", "qty": 1, "unit_price": 90 },
     *     { "identifier": "B1001", "qty": 2, "unit_price": 650 }
     *   ]
     * }
     *
     * Each item can have:
     *   - "identifier" (SKU or name) → resolved to inventory item automatically
     *   - OR "description" → free-text line (not linked to inventory)
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'business_unit'  => ['required', 'in:moto,cool,it,easyfix'],
                'customer_name'  => ['nullable', 'string', 'max:255'],
                'customer_phone' => ['nullable', 'string', 'max:50'],
                'payment_method' => ['required', 'in:cash,transfer'],
                'date'           => ['nullable', 'date'],
                'notes'          => ['nullable', 'string', 'max:1000'],
                'items'          => ['required', 'array', 'min:1'],
                'items.*.identifier'  => ['nullable', 'string', 'max:255'],
                'items.*.description' => ['nullable', 'string', 'max:255'],
                'items.*.qty'         => ['required', 'integer', 'min:1'],
                'items.*.unit_price'  => ['required', 'numeric', 'min:0'],
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed.', 'details' => $e->errors()], 422);
        }

        // Each item needs either identifier or description
        foreach ($validated['items'] as $i => $item) {
            if (empty($item['identifier']) && empty($item['description'])) {
                return response()->json([
                    'error' => "Item at index {$i} must have either \"identifier\" (SKU/name) or \"description\".",
                ], 422);
            }
        }

        // We need an admin user to act as the sale creator/submitter
        $actor = User::where('role', 'admin')->first();
        if (!$actor) {
            return response()->json(['error' => 'No admin user found on the system to record the sale.'], 500);
        }

        $date = $validated['date'] ?? now()->toDateString();

        DB::beginTransaction();
        try {
            // --- Resolve customer ---
            $customer = null;
            if (!empty($validated['customer_phone'])) {
                $customer = Customer::firstOrCreate(
                    ['phone' => $validated['customer_phone']],
                    [
                        'name'     => $validated['customer_name'] ?? 'Unknown',
                        'category' => match ($validated['business_unit']) {
                            'cool' => 'ac',
                            'it' => 'it',
                            'easyfix' => 'easyfix',
                            default => 'moto',
                        },
                    ]
                );
            }

            // --- Create draft sales log ---
            $log = DailySalesLog::create([
                'date'          => $date,
                'business_unit' => $validated['business_unit'],  // "moto" → Micro Moto, "cool" → Micro Cool
                'created_by'    => $actor->id,
                'status'        => 'draft',
                'customer_id'   => $customer?->id,
                'notes'         => $validated['notes'] ?? null,
            ]);

            // --- Resolve and add lines ---
            $resolvedLines = [];
            $categoryMap = match ($validated['business_unit']) {
                'cool' => 'ac',
                'it' => 'it',
                'easyfix' => 'easyfix',
                default => 'moto',
            };

            foreach ($validated['items'] as $item) {
                $inventoryItem = null;
                $description   = $item['description'] ?? null;
                $isStockItem   = false;

                if (!empty($item['identifier'])) {
                    // Try SKU first, then name (within the correct business unit category)
                    $inventoryItem = InventoryItem::where('sku', $item['identifier'])->first()
                        ?? InventoryItem::where('name', 'like', $item['identifier'])
                                        ->where('category', $categoryMap)
                                        ->first()
                        ?? InventoryItem::where('name', 'like', $item['identifier'])->first();

                    if ($inventoryItem) {
                        $description = $inventoryItem->name;
                        $isStockItem = !$inventoryItem->is_service;
                    } else {
                        // Identifier given but no inventory match → use as free-text
                        $description = $item['identifier'];
                    }
                }

                $qty       = (int) $item['qty'];
                $unitPrice = (float) $item['unit_price'];
                $lineTotal = $qty * $unitPrice;

                $line = $log->lines()->create([
                    'inventory_item_id' => $inventoryItem?->id,
                    'description'       => $description,
                    'qty'               => $qty,
                    'unit_price'        => $unitPrice,
                    'line_total'        => $lineTotal,
                    'payment_method'    => $validated['payment_method'],
                    'is_stock_item'     => $isStockItem,
                    'is_gst_applicable' => false,
                    'gst_amount'        => 0,
                ]);

                $resolvedLines[] = [
                    'description'    => $description,
                    'inventory_item' => $inventoryItem ? $inventoryItem->name . ' (id:' . $inventoryItem->id . ')' : null,
                    'qty'            => $qty,
                    'unit_price'     => $unitPrice,
                    'line_total'     => $lineTotal,
                ];
            }

            // --- Submit (deducts stock, creates job + payment) ---
            // Set the actor as the authenticated user so Auth::id() works inside submit()
            Auth::setUser($actor);
            $log->submit($validated['payment_method']);

            DB::commit();

            $totals = $log->fresh('lines')->totals;
            $unit   = match ($validated['business_unit']) {
                'moto' => 'Micro Moto',
                'cool' => 'Micro Cool',
                'it' => 'Micronet',
                default => $validated['business_unit'],
            };

            ActivityLog::record('sale.created', "API: Sale #{$log->id} recorded for {$unit} on {$date}", $log, [], $actor?->id, 'api');

            return response()->json([
                'message'       => "Sale recorded successfully for {$unit}.",
                'sale_id'       => $log->id,
                'business_unit' => $validated['business_unit'],
                'unit_label'    => $unit,
                'date'          => $date,
                'customer'      => $customer ? [
                    'id'    => $customer->id,
                    'name'  => $customer->name,
                    'phone' => $customer->phone,
                ] : 'Walk-in',
                'payment_method' => $validated['payment_method'],
                'lines'          => $resolvedLines,
                'totals'         => [
                    'subtotal' => number_format($totals['subtotal'], 2),
                    'gst'      => number_format($totals['gst'], 2),
                    'grand'    => number_format($totals['grand'], 2),
                ],
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'error'   => 'Sale could not be recorded.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/sales
     * Fetch sales by date or date range, optionally filtered by business_unit.
     *
     * Query params:
     *   date=2026-03-05                      → single day
     *   from=2026-03-01&to=2026-03-05        → date range
     *   business_unit=moto|cool              → optional filter
     *
     * Defaults to today if no date params given.
     */
    public function index(Request $request): JsonResponse
    {
        $date = $request->query('date');
        $from = $request->query('from');
        $to   = $request->query('to');
        $unit = $request->query('business_unit');

        // Resolve date range
        if ($date) {
            $from = $to = $date;
        } elseif (!$from && !$to) {
            $from = $to = now()->toDateString();
        } elseif ($from && !$to) {
            $to = now()->toDateString();
        } elseif (!$from && $to) {
            $from = $to;
        }

        $query = DailySalesLog::with('customer')
            ->submitted()
            ->whereBetween('date', [$from, $to]);

        if ($unit) {
            $query->forUnit($unit);
        }

        $logs = $query->orderBy('date', 'desc')->get();

        $data = $logs->map(function ($log) {
            $totals = $log->totals;
            return [
                'sale_id'        => $log->id,
                'date'           => $log->date->format('Y-m-d'),
                'business_unit'  => $log->business_unit,
                'unit_label'     => match ($log->business_unit) {
                    'moto' => 'Micro Moto',
                    'cool' => 'Micro Cool',
                    'it' => 'Micronet',
                    'easyfix' => 'Micronet - Easy Fix',
                    default => $log->business_unit,
                },
                'customer'       => $log->customer?->name ?? 'Walk-in',
                'payment_method' => $log->payment_method,
                'lines_count'    => $log->lines()->count(),
                'grand_total'    => number_format($totals['grand'], 2),
            ];
        });

        $grandTotal = $logs->sum(fn($l) => $l->totals['grand']);

        return response()->json([
            'from'        => $from,
            'to'          => $to,
            'sales_count' => $logs->count(),
            'grand_total' => number_format($grandTotal, 2),
            'data'        => $data,
        ]);
    }

    /**
     * DELETE /api/sales/{id}
     * Delete a sale (reopens it first to reverse stock, then deletes).
     */
    public function destroy(int $id): JsonResponse
    {
        $log = DailySalesLog::find($id);

        if (!$log) {
            return response()->json(['error' => "Sale #{$id} not found."], 404);
        }

        $actor = User::where('role', 'admin')->first();
        if (!$actor) {
            return response()->json(['error' => 'No admin user found on the system.'], 500);
        }

        DB::beginTransaction();
        try {
            Auth::setUser($actor);

            // If submitted, reopen first to reverse stock deductions, job, and payment
            if ($log->isSubmitted()) {
                $log->reopen();
            }

            $log->lines()->delete();
            $log->delete();

            DB::commit();

            ActivityLog::record('sale.deleted', "API: Sale #{$id} deleted and stock reversed", null, ['sale_id' => $id], null, 'api');

            return response()->json([
                'message' => "Sale #{$id} deleted and stock reversed successfully.",
                'sale_id' => $id,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'error'   => 'Sale could not be deleted.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/sales/today
     * Summary of today's submitted sales, optionally filtered by business_unit.
     *
     * Query: ?business_unit=moto   (or cool, or omit for all)
     */
    public function today(Request $request): JsonResponse
    {
        $unit  = $request->query('business_unit');
        $date  = now()->toDateString();

        $query = DailySalesLog::with('lines', 'customer')
            ->submitted()
            ->forDate($date);

        if ($unit) {
            $query->forUnit($unit);
        }

        $logs = $query->get();

        $summary = $logs->map(function ($log) {
            $totals = $log->totals;
            return [
                'sale_id'        => $log->id,
                'business_unit'  => $log->business_unit,
                'unit_label'     => match ($log->business_unit) {
                    'moto' => 'Micro Moto',
                    'cool' => 'Micro Cool',
                    'it' => 'Micronet',
                    'easyfix' => 'Micronet - Easy Fix',
                    default => $log->business_unit,
                },
                'customer'       => $log->customer?->name ?? 'Walk-in',
                'payment_method' => $log->payment_method,
                'lines_count'    => $log->lines->count(),
                'grand_total'    => number_format($totals['grand'], 2),
            ];
        });

        $grandTotal = $logs->sum(fn($l) => $l->totals['grand']);

        return response()->json([
            'date'        => $date,
            'sales_count' => $logs->count(),
            'grand_total' => number_format($grandTotal, 2),
            'data'        => $summary,
        ]);
    }
}
