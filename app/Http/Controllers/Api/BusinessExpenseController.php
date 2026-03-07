<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\InventoryLog;
use App\Models\InventoryPurchase;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BusinessExpenseController extends Controller
{
    /**
     * GET /api/expenses/categories
     * List expense categories, optionally filtered by type.
     *
     * Query: ?type=cogs|operating|other
     */
    public function categories(Request $request): JsonResponse
    {
        $query = ExpenseCategory::where('is_active', true)->orderBy('name');

        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }

        return response()->json([
            'data' => $query->get(['id', 'name', 'type']),
        ]);
    }

    /**
     * GET /api/expenses/vendors
     * List all active vendors.
     */
    public function vendors(): JsonResponse
    {
        $vendors = Vendor::where('is_active', true)->orderBy('name')
            ->get(['id', 'name', 'phone', 'contact_name', 'address']);

        return response()->json([
            'total' => $vendors->count(),
            'data'  => $vendors,
        ]);
    }

    /**
     * POST /api/expenses/vendors
     * Create a new vendor.
     *
     * Body: { "name": "STELCO", "phone": "3321234", "contact_name": "Ali" }
     */
    public function createVendor(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name'         => ['required', 'string', 'max:255'],
                'phone'        => ['nullable', 'string', 'max:50'],
                'contact_name' => ['nullable', 'string', 'max:255'],
                'address'      => ['nullable', 'string', 'max:500'],
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed.', 'details' => $e->errors()], 422);
        }

        $existing = Vendor::whereRaw('lower(name) = ?', [strtolower($validated['name'])])->first();
        if ($existing) {
            return response()->json([
                'created' => false,
                'message' => 'Vendor already exists.',
                'data'    => $existing->only(['id', 'name', 'phone', 'contact_name']),
            ]);
        }

        $vendor = Vendor::create([
            'name'         => $validated['name'],
            'phone'        => $validated['phone'] ?? '',
            'contact_name' => $validated['contact_name'] ?? null,
            'address'      => $validated['address'] ?? null,
            'is_active'    => true,
        ]);

        return response()->json([
            'created' => true,
            'message' => "Vendor \"{$vendor->name}\" created.",
            'data'    => $vendor->only(['id', 'name', 'phone', 'contact_name']),
        ], 201);
    }

    /**
     * GET /api/expenses/accounts
     * List available accounts the bot can charge expenses against.
     */
    public function accounts(): JsonResponse
    {
        $accounts = Account::where('is_active', true)
            ->where('is_system', false)
            ->orderBy('name')
            ->get(['id', 'name', 'balance']);

        return response()->json([
            'data' => $accounts->map(fn($a) => [
                'id'      => $a->id,
                'name'    => $a->name,
                'balance' => number_format((float) $a->balance, 2),
            ]),
        ]);
    }

    /**
     * GET /api/expenses
     * List expenses filtered by type, unit, or period.
     *
     * Query: ?type=cogs|operating  &business_unit=moto|ac|shared  &period=today|week|month
     */
    public function index(Request $request): JsonResponse
    {
        $query = Expense::with('category:id,name,type', 'vendorEntity:id,name')->orderByDesc('incurred_at');

        if ($unit = $request->query('business_unit')) {
            $query->where('business_unit', $unit);
        }

        if ($type = $request->query('type')) {
            $query->whereHas('category', fn($q) => $q->where('type', $type));
        }

        $period = $request->query('period', 'month');
        $today  = now()->startOfDay();

        match ($period) {
            'today'     => $query->whereDate('incurred_at', $today),
            'week'      => $query->whereBetween('incurred_at', [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()]),
            'month'     => $query->whereBetween('incurred_at', [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()]),
            default     => null,
        };

        $expenses = $query->get();

        return response()->json([
            'period'      => $period,
            'total'       => $expenses->count(),
            'grand_total' => number_format($expenses->sum('amount'), 2),
            'data'        => $expenses->map(fn($e) => [
                'id'            => $e->id,
                'type'          => $e->category?->type,
                'category'      => $e->category?->name,
                'vendor'        => $e->vendor,
                'business_unit' => $e->business_unit,
                'amount'        => number_format((float) $e->amount, 2),
                'incurred_at'   => $e->incurred_at->format('Y-m-d'),
                'reference'     => $e->reference,
                'notes'         => $e->notes,
            ]),
        ]);
    }

    /**
     * POST /api/expenses
     * Record a COGS or Operating expense.
     *
     * Body:
     * {
     *   "type":          "cogs",              // "cogs" or "operating"
     *   "category":      "Parts Purchase",    // category name (matched by name/type)
     *   "vendor":        "Anam Traders",      // vendor name — found or created
     *   "vendor_phone":  "7001234",           // required only if vendor is new
     *   "account":       "Cash",              // account name to debit
     *   "business_unit": "moto",             // "moto", "ac", or "shared"
     *   "amount":        1500,
     *   "incurred_at":   "2026-03-07",        // defaults to today
     *   "reference":     "INV-001",
     *   "notes":         "Monthly parts restock",
     *
     *   // COGS only — inventory items being purchased:
     *   "items": [
     *     {
     *       "identifier":  "BP001",          // SKU or name of existing item (optional)
     *       "name":        "Brake Pad Gen2", // new item name if not in inventory
     *       "sku":         "BP002",          // required for new items
     *       "quantity":    10,
     *       "unit_cost":   120,
     *       "sell_price":  250,              // optional, updates sell price
     *       "unit":        "pcs"
     *     }
     *   ]
     * }
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'type'          => ['required', 'in:cogs,operating,other'],
                'category'      => ['required', 'string', 'max:255'],
                'vendor'        => ['required', 'string', 'max:255'],
                'vendor_phone'  => ['nullable', 'string', 'max:50'],
                'vendor_contact'=> ['nullable', 'string', 'max:255'],
                'account'       => ['required', 'string', 'max:255'],
                'business_unit' => ['required', 'in:moto,ac,shared'],
                'amount'        => ['required', 'numeric', 'min:0.01'],
                'incurred_at'   => ['nullable', 'date'],
                'reference'     => ['nullable', 'string', 'max:255'],
                'notes'         => ['nullable', 'string', 'max:1000'],
                'items'         => ['nullable', 'array'],
                'items.*.identifier' => ['nullable', 'string', 'max:255'],
                'items.*.name'       => ['nullable', 'string', 'max:255'],
                'items.*.sku'        => ['nullable', 'string', 'max:255'],
                'items.*.quantity'   => ['required_with:items.*.name', 'nullable', 'numeric', 'min:0.01'],
                'items.*.unit_cost'  => ['required_with:items.*.name', 'nullable', 'numeric', 'min:0'],
                'items.*.sell_price' => ['nullable', 'numeric', 'min:0'],
                'items.*.unit'       => ['nullable', 'string', 'max:50'],
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed.', 'details' => $e->errors()], 422);
        }

        // COGS must have items
        if ($validated['type'] === 'cogs' && empty($validated['items'])) {
            return response()->json(['error' => 'COGS expenses require at least one item in the "items" array.'], 422);
        }

        // --- Resolve category ---
        $category = ExpenseCategory::where('is_active', true)
            ->where('type', $validated['type'])
            ->whereRaw('lower(name) like ?', ['%' . strtolower($validated['category']) . '%'])
            ->first()
            ?? ExpenseCategory::where('is_active', true)->where('type', $validated['type'])->first();

        if (!$category) {
            return response()->json([
                'error' => "No active expense category found for type \"{$validated['type']}\". Check GET /api/expenses/categories.",
            ], 404);
        }

        // --- Resolve or create vendor ---
        $vendor = Vendor::whereRaw('lower(name) like ?', ['%' . strtolower($validated['vendor']) . '%'])
            ->where('is_active', true)->first();

        if (!$vendor) {
            $vendor = Vendor::create([
                'name'         => $validated['vendor'],
                'phone'        => $validated['vendor_phone'] ?? '',
                'contact_name' => $validated['vendor_contact'] ?? null,
                'is_active'    => true,
            ]);
        }

        // --- Resolve account ---
        $account = Account::where('is_active', true)
            ->where('is_system', false)
            ->whereRaw('lower(name) like ?', ['%' . strtolower($validated['account']) . '%'])
            ->first();

        if (!$account) {
            return response()->json([
                'error' => "Account \"{$validated['account']}\" not found. Check GET /api/expenses/accounts for available accounts.",
            ], 404);
        }

        $amount = (float) $validated['amount'];

        if ($account->balance < $amount) {
            return response()->json([
                'error'           => "Insufficient account balance. \"{$account->name}\" has " . number_format($account->balance, 2) . " MVR available.",
                'account_balance' => number_format((float) $account->balance, 2),
            ], 422);
        }

        $actor = User::where('role', 'admin')->first();
        Auth::setUser($actor);

        try {
            $expense = DB::transaction(function () use ($validated, $category, $vendor, $account, $amount, $actor) {
                $expense = Expense::create([
                    'expense_category_id' => $category->id,
                    'vendor_id'           => $vendor->id,
                    'vendor'              => $vendor->name,
                    'account_id'          => $account->id,
                    'business_unit'       => $validated['business_unit'],
                    'amount'              => $amount,
                    'incurred_at'         => $validated['incurred_at'] ?? now()->toDateString(),
                    'reference'           => $validated['reference'] ?? null,
                    'notes'               => $validated['notes'] ?? null,
                    'created_by'          => $actor?->id,
                    'updated_by'          => $actor?->id,
                ]);

                // Debit the account
                $account->balance = (float) $account->balance - $amount;
                $account->save();

                AccountTransaction::create([
                    'account_id'   => $account->id,
                    'type'         => 'expense',
                    'amount'       => -$amount,
                    'occurred_at'  => $expense->incurred_at->format('Y-m-d'),
                    'description'  => 'Expense: ' . $category->name . ' — ' . $vendor->name,
                    'related_type' => Expense::class,
                    'related_id'   => $expense->id,
                    'created_by'   => $actor?->id,
                ]);

                // Apply inventory purchases for COGS
                if ($category->type === ExpenseCategory::TYPE_COGS && !empty($validated['items'])) {
                    $this->applyItems($expense, $validated);
                }

                return $expense;
            });
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Expense could not be saved.', 'details' => $e->getMessage()], 500);
        }

        $vendorCreated = $vendor->wasRecentlyCreated;

        return response()->json([
            'message'        => 'Expense recorded successfully.',
            'expense_id'     => $expense->id,
            'type'           => $category->type,
            'category'       => $category->name,
            'vendor'         => $vendor->name . ($vendorCreated ? ' (new vendor created)' : ''),
            'account'        => $account->name,
            'business_unit'  => $expense->business_unit,
            'amount'         => number_format($amount, 2),
            'incurred_at'    => $expense->incurred_at->format('Y-m-d'),
            'account_balance_after' => number_format((float) $account->fresh()->balance, 2),
        ], 201);
    }

    // -------------------------------------------------------------------------

    private function applyItems(Expense $expense, array $validated): void
    {
        $categoryMap = $validated['business_unit'] === 'ac' ? 'ac' : 'moto';

        foreach ($validated['items'] as $row) {
            $quantity = (float) ($row['quantity'] ?? 0);
            $unitCost = (float) ($row['unit_cost'] ?? 0);
            if ($quantity <= 0) continue;

            // Resolve or create inventory item
            $item = null;
            if (!empty($row['identifier'])) {
                $item = InventoryItem::where('sku', $row['identifier'])->first()
                    ?? InventoryItem::whereRaw('lower(name) like ?', ['%' . strtolower($row['identifier']) . '%'])->first();
            }

            if (!$item && !empty($row['name'])) {
                $item = InventoryItem::create([
                    'name'       => $row['name'],
                    'sku'        => $row['sku'] ?? null,
                    'category'   => $categoryMap,
                    'unit'       => $row['unit'] ?? 'pcs',
                    'cost_price' => $unitCost,
                    'sell_price' => (float) ($row['sell_price'] ?? 0),
                    'quantity'   => 0,
                    'is_service' => false,
                    'is_active'  => true,
                ]);
            }

            if (!$item) continue;

            $totalCost = round($quantity * $unitCost, 2);

            InventoryPurchase::create([
                'inventory_item_id' => $item->id,
                'expense_id'        => $expense->id,
                'business_unit'     => $validated['business_unit'],
                'quantity'          => $quantity,
                'unit_cost'         => $unitCost,
                'total_cost'        => $totalCost,
                'purchased_at'      => $validated['incurred_at'] ?? now()->toDateString(),
                'vendor'            => $expense->vendor,
                'reference'         => $validated['reference'] ?? null,
                'notes'             => $validated['notes'] ?? null,
                'created_by'        => Auth::id(),
            ]);

            // Weighted average cost update + stock increase
            $oldQty  = (int) $item->quantity;
            $oldCost = (float) $item->cost_price;
            $newQty  = $oldQty + $quantity;
            if ($newQty > 0) {
                $item->cost_price = round((($oldQty * $oldCost) + ($quantity * $unitCost)) / $newQty, 2);
            }
            $item->quantity = $newQty;
            if (!empty($row['sell_price']) && (float) $row['sell_price'] > 0) {
                $item->sell_price = (float) $row['sell_price'];
            }
            $item->save();

            InventoryLog::create([
                'inventory_item_id' => $item->id,
                'quantity_change'   => $quantity,
                'type'              => 'purchase',
                'user_id'           => Auth::id(),
                'notes'             => 'Purchase via expense #' . $expense->id,
            ]);
        }
    }

    /**
     * DELETE /api/expenses/{id}
     * Delete an expense and reverse all its effects:
     *   - Refunds the amount back to the account
     *   - Removes the account transaction
     *   - For COGS: reverses inventory stock additions
     *   - Deletes inventory purchase records
     */
    public function destroy(int $id): JsonResponse
    {
        $expense = Expense::with('category', 'inventoryPurchases')->find($id);

        if (!$expense) {
            return response()->json(['error' => "Expense #{$id} not found."], 404);
        }

        $actor = User::where('role', 'admin')->first();
        Auth::setUser($actor);

        try {
            DB::transaction(function () use ($expense, $actor) {
                // --- Reverse account balance ---
                if ($expense->account_id) {
                    $account = Account::find($expense->account_id);
                    if ($account) {
                        $account->balance = (float) $account->balance + (float) $expense->amount;
                        $account->save();
                    }

                    // Remove account transaction record
                    AccountTransaction::where('related_type', Expense::class)
                        ->where('related_id', $expense->id)
                        ->delete();
                }

                // --- Reverse COGS inventory additions ---
                foreach ($expense->inventoryPurchases as $purchase) {
                    $item = InventoryItem::find($purchase->inventory_item_id);
                    if ($item) {
                        $item->quantity = max(0, (int) $item->quantity - (float) $purchase->quantity);
                        $item->save();
                    }

                    // Remove inventory log entries for this purchase
                    InventoryLog::where('inventory_item_id', $purchase->inventory_item_id)
                        ->where('notes', 'like', '%expense #' . $expense->id . '%')
                        ->delete();
                }

                $expense->inventoryPurchases()->delete();
                $expense->delete();
            });
        } catch (\Throwable $e) {
            return response()->json([
                'error'   => 'Expense could not be deleted.',
                'details' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message'    => "Expense #{$id} deleted and all effects reversed.",
            'expense_id' => $id,
            'amount_refunded' => number_format((float) $expense->amount, 2),
        ]);
    }
}
