<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Vendor;
use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\InventoryItem;
use App\Models\InventoryCategory;
use App\Models\InventoryPurchase;
use App\Models\ActivityLog;
use App\Models\InventoryLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with(['category', 'vendorEntity', 'account']);

        $businessUnit = $request->query('business_unit', 'all');
        $type = $request->query('type', 'all');
        $search = $request->query('search');
        $period = $request->query('period', 'all');

        if ($businessUnit !== 'all') {
            $query->where('business_unit', $businessUnit);
        }

        if ($type !== 'all') {
            $query->whereHas('category', function ($q) use ($type) {
                $q->where('type', $type);
            });
        }

        if ($search) {
            $s = mb_strtolower($search);
            $query->where(function ($q) use ($s) {
                $q->whereRaw('lower(vendor) like ?', ["%{$s}%"])
                    ->orWhereRaw('lower(reference) like ?', ["%{$s}%"])
                    ->orWhereRaw('lower(notes) like ?', ["%{$s}%"]);
            });
        }

        if ($period !== 'all') {
            $today = now()->startOfDay();
            match ($period) {
                'today' => $query->whereDate('incurred_at', $today),
                'yesterday' => $query->whereDate('incurred_at', $today->copy()->subDay()),
                'week' => $query->whereBetween('incurred_at', [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()]),
                'month' => $query->whereBetween('incurred_at', [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()]),
                default => null,
            };
        }

        $expenses = $query->orderByDesc('incurred_at')->paginate(10)->withQueryString();
        $categories = ExpenseCategory::where('is_active', true)->orderBy('name')->get();
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();
        $businessUnits = Expense::getBusinessUnits();
        $types = ExpenseCategory::getTypes();

        return view('expenses.index', compact('expenses', 'categories', 'vendors', 'businessUnits', 'types', 'businessUnit', 'type', 'search', 'period'));
    }

    public function show(Expense $expense)
    {
        $expense->load(['category', 'vendorEntity', 'account', 'creator', 'updater', 'inventoryPurchases.inventoryItem']);
        $businessUnits = Expense::getBusinessUnits();

        return view('expenses.show', compact('expense', 'businessUnits'));
    }

    public function create()
    {
        $categories = ExpenseCategory::where('is_active', true)->orderBy('name')->get();
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();
        $businessUnits = Expense::getBusinessUnits();
        $accounts = Account::where('is_active', true)->where('is_system', false)->orderBy('name')->get();
        $inventoryItems = InventoryItem::where('is_active', true)->where('is_service', false)->orderBy('name')->get();
        $inventoryCategories = InventoryCategory::where('is_active', true)->orderBy('name')->get();
        $vendorsJson = $vendors->map(function ($vendor) {
            return [
                'name' => $vendor->name,
                'phone' => $vendor->phone,
                'contact_name' => $vendor->contact_name,
                'address' => $vendor->address,
            ];
        })->values()->toJson();

        return view('expenses.create', compact('categories', 'vendors', 'businessUnits', 'vendorsJson', 'accounts', 'inventoryItems', 'inventoryCategories'));
    }

    public function createCogs()
    {
        $categories = ExpenseCategory::where('is_active', true)
            ->where('type', ExpenseCategory::TYPE_COGS)
            ->orderBy('name')
            ->get();
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();
        $businessUnits = Expense::getBusinessUnits();
        $accounts = Account::where('is_active', true)->where('is_system', false)->orderBy('name')->get();
        $inventoryItems = InventoryItem::where('is_active', true)->where('is_service', false)->orderBy('name')->get();
        $inventoryCategories = InventoryCategory::where('is_active', true)->orderBy('name')->get();
        $vendorsJson = $vendors->map(function ($vendor) {
            return [
                'name' => $vendor->name,
                'phone' => $vendor->phone,
                'contact_name' => $vendor->contact_name,
                'address' => $vendor->address,
            ];
        })->values()->toJson();

        return view('expenses.create-cogs', compact('categories', 'vendors', 'businessUnits', 'vendorsJson', 'accounts', 'inventoryItems', 'inventoryCategories'));
    }

    public function createOperating()
    {
        $categories = ExpenseCategory::where('is_active', true)
            ->whereIn('type', [ExpenseCategory::TYPE_OPERATING, ExpenseCategory::TYPE_OTHER])
            ->orderBy('name')
            ->get();
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();
        $businessUnits = Expense::getBusinessUnits();
        $accounts = Account::where('is_active', true)->where('is_system', false)->orderBy('name')->get();
        $inventoryItems = InventoryItem::where('is_active', true)->where('is_service', false)->orderBy('name')->get();
        $inventoryCategories = InventoryCategory::where('is_active', true)->orderBy('name')->get();
        $vendorsJson = $vendors->map(function ($vendor) {
            return [
                'name' => $vendor->name,
                'phone' => $vendor->phone,
                'contact_name' => $vendor->contact_name,
                'address' => $vendor->address,
            ];
        })->values()->toJson();

        return view('expenses.create-operating', compact('categories', 'vendors', 'businessUnits', 'vendorsJson', 'accounts', 'inventoryItems', 'inventoryCategories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'vendor_id' => ['required', 'exists:vendors,id'],
            'account_id' => ['required', 'exists:accounts,id'],
            'business_unit' => ['required', 'in:' . implode(',', array_keys(Expense::getBusinessUnits()))],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'incurred_at' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'purchases' => ['nullable', 'array'],
            'purchases.*.inventory_item_id' => ['nullable', 'exists:inventory_items,id'],
            'purchases.*.name' => ['nullable', 'string', 'max:255'],
            'purchases.*.category' => ['nullable', 'in:moto,ac'],
            'purchases.*.inventory_category_id' => ['nullable', 'exists:inventory_categories,id'],
            'purchases.*.unit' => ['nullable', 'string', 'max:50'],
            'purchases.*.quantity' => ['nullable', 'numeric', 'min:0.01'],
            'purchases.*.unit_cost' => ['nullable', 'numeric', 'min:0'],
            'purchases.*.sell_price' => ['nullable', 'numeric', 'min:0'],
            'purchases.*.brand' => ['nullable', 'string', 'max:255'],
            'purchases.*.sku' => ['nullable', 'string', 'max:255'],
        ]);

        foreach ($validated['purchases'] ?? [] as $index => $row) {
            $hasItem = !empty($row['inventory_item_id']);
            $hasName = !empty($row['name']);
            if ($hasItem && $hasName) {
                return back()
                    ->withErrors(["purchases.$index.name" => 'Choose an existing item or enter a new item name, not both.'])
                    ->withInput();
            }
        }

        $purchaseResult = $this->sanitizePurchases($request);
        if ($purchaseResult['error']) {
            return back()
                ->withErrors(["purchases.{$purchaseResult['error']['index']}.name" => $purchaseResult['error']['message']])
                ->withInput();
        }
        $validated['purchases'] = $purchaseResult['rows'];
        $category = ExpenseCategory::find($validated['expense_category_id']);
        if ($category && $category->type === ExpenseCategory::TYPE_COGS && empty($validated['purchases'])) {
            return back()
                ->withErrors(['purchases' => 'COGS expenses require at least one inventory item.'])
                ->withInput();
        }
        $validated['created_by'] = Auth::id();
        $validated['updated_by'] = Auth::id();
        $vendor = Vendor::find($validated['vendor_id']);
        $validated['vendor'] = $vendor?->name;

        try {
            DB::transaction(function () use ($validated) {
                $expense = Expense::create($validated);

                $account = Account::lockForUpdate()->find($validated['account_id']);
                $amount = (float) $validated['amount'];

                if ($account->balance < $amount) {
                    throw new \RuntimeException('Selected account has no available balance.');
                }

                $account->balance = (float) $account->balance - $amount;
                $account->save();

                AccountTransaction::create([
                    'account_id' => $account->id,
                    'type' => 'expense',
                    'amount' => -$amount,
                    'occurred_at' => $validated['incurred_at'],
                    'description' => 'Expense: ' . ($expense->category?->name ?? 'Expense'),
                    'related_type' => Expense::class,
                    'related_id' => $expense->id,
                    'created_by' => Auth::id(),
                ]);

                $this->applyInventoryPurchases($expense, $validated);
            });
        } catch (\RuntimeException $e) {
            return back()->withErrors(['account_id' => $e->getMessage()])->withInput();
        }

        ActivityLog::record('expense.created', "Expense recorded — {$validated['amount']} MVR via " . (Vendor::find($validated['vendor_id'])?->name ?? 'unknown vendor'));

        return redirect()->route('expenses.index')
            ->with('success', 'Expense recorded successfully.');
    }

    public function edit(Expense $expense)
    {
        $categories = ExpenseCategory::where('is_active', true)->orderBy('name')->get();
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();
        $businessUnits = Expense::getBusinessUnits();
        $accounts = Account::where('is_active', true)->where('is_system', false)->orderBy('name')->get();
        $inventoryItems = InventoryItem::where('is_active', true)->where('is_service', false)->orderBy('name')->get();
        $inventoryCategories = InventoryCategory::where('is_active', true)->orderBy('name')->get();
        $vendorsJson = $vendors->map(function ($vendor) {
            return [
                'name' => $vendor->name,
                'phone' => $vendor->phone,
                'contact_name' => $vendor->contact_name,
                'address' => $vendor->address,
            ];
        })->values()->toJson();

        $expense->load('inventoryPurchases.inventoryItem');

        return view('expenses.edit', compact('expense', 'categories', 'vendors', 'businessUnits', 'vendorsJson', 'accounts', 'inventoryItems', 'inventoryCategories'));
    }

    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'vendor_id' => ['required', 'exists:vendors,id'],
            'account_id' => ['required', 'exists:accounts,id'],
            'business_unit' => ['required', 'in:' . implode(',', array_keys(Expense::getBusinessUnits()))],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'incurred_at' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'purchases' => ['nullable', 'array'],
            'purchases.*.inventory_item_id' => ['nullable', 'exists:inventory_items,id'],
            'purchases.*.name' => ['nullable', 'string', 'max:255'],
            'purchases.*.category' => ['nullable', 'in:moto,ac'],
            'purchases.*.inventory_category_id' => ['nullable', 'exists:inventory_categories,id'],
            'purchases.*.unit' => ['nullable', 'string', 'max:50'],
            'purchases.*.quantity' => ['nullable', 'numeric', 'min:0.01'],
            'purchases.*.unit_cost' => ['nullable', 'numeric', 'min:0'],
            'purchases.*.sell_price' => ['nullable', 'numeric', 'min:0'],
            'purchases.*.brand' => ['nullable', 'string', 'max:255'],
            'purchases.*.sku' => ['nullable', 'string', 'max:255'],
        ]);

        foreach ($validated['purchases'] ?? [] as $index => $row) {
            $hasItem = !empty($row['inventory_item_id']);
            $hasName = !empty($row['name']);
            if ($hasItem && $hasName) {
                return back()
                    ->withErrors(["purchases.$index.name" => 'Choose an existing item or enter a new item name, not both.'])
                    ->withInput();
            }
        }

        $purchaseResult = $this->sanitizePurchases($request);
        if ($purchaseResult['error']) {
            return back()
                ->withErrors(["purchases.{$purchaseResult['error']['index']}.name" => $purchaseResult['error']['message']])
                ->withInput();
        }
        $validated['purchases'] = $purchaseResult['rows'];
        $category = ExpenseCategory::find($validated['expense_category_id']);
        if ($category && $category->type === ExpenseCategory::TYPE_COGS && empty($validated['purchases'])) {
            return back()
                ->withErrors(['purchases' => 'COGS expenses require at least one inventory item.'])
                ->withInput();
        }
        $validated['updated_by'] = Auth::id();
        $vendor = Vendor::find($validated['vendor_id']);
        $validated['vendor'] = $vendor?->name;

        try {
            DB::transaction(function () use ($validated, $expense) {
                $previousAccountId = $expense->account_id;
                $previousAmount = (float) $expense->amount;

                $expense->update($validated);

                // Revert previous account impact if it existed
                if ($previousAccountId) {
                    $prevAccount = Account::lockForUpdate()->find($previousAccountId);
                    if ($prevAccount) {
                        $prevAccount->balance = (float) $prevAccount->balance + $previousAmount;
                        $prevAccount->save();
                    }
                }

                $account = Account::lockForUpdate()->find($validated['account_id']);
                $amount = (float) $validated['amount'];

                if ($account->balance < $amount) {
                    throw new \RuntimeException('Selected account has no available balance.');
                }

                $account->balance = (float) $account->balance - $amount;
                $account->save();

                AccountTransaction::create([
                    'account_id' => $account->id,
                    'type' => 'expense',
                    'amount' => -$amount,
                    'occurred_at' => $validated['incurred_at'],
                    'description' => 'Expense update: ' . ($expense->category?->name ?? 'Expense'),
                    'related_type' => Expense::class,
                    'related_id' => $expense->id,
                    'created_by' => Auth::id(),
                ]);

                $this->applyInventoryPurchases($expense, $validated);
            });
        } catch (\RuntimeException $e) {
            return back()->withErrors(['account_id' => $e->getMessage()])->withInput();
        }

        return redirect()->route('expenses.index')
            ->with('success', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Only admins can delete expenses.');
        }

        $expenseId  = $expense->id;
        $amount     = (float) $expense->amount;
        $categoryName = $expense->category?->name ?? 'Expense';
        $accountName  = $expense->account?->name;

        DB::transaction(function () use ($expense, $expenseId, $amount, $categoryName, $accountName) {
            // Restore account balance and write a reversal transaction (keeps audit trail)
            if ($expense->account_id) {
                $account = Account::lockForUpdate()->find($expense->account_id);
                if ($account) {
                    $account->balance = (float) $account->balance + $amount;
                    $account->save();

                    AccountTransaction::create([
                        'account_id'   => $account->id,
                        'type'         => 'expense_reversal',
                        'amount'       => $amount,
                        'occurred_at'  => now(),
                        'description'  => "Expense deleted: {$categoryName} (Expense #{$expenseId})",
                        'related_type' => Expense::class,
                        'related_id'   => $expenseId,
                        'created_by'   => Auth::id(),
                    ]);
                }
            }

            // Reverse COGS inventory purchases
            $purchases = InventoryPurchase::where('expense_id', $expenseId)->get();
            foreach ($purchases as $purchase) {
                $item = InventoryItem::find($purchase->inventory_item_id);
                if ($item) {
                    $item->quantity = max(0, (float) $item->quantity - (float) $purchase->quantity);
                    $item->save();
                }

                InventoryLog::where('inventory_item_id', $purchase->inventory_item_id)
                    ->where('notes', 'Purchase for expense #' . $expenseId)
                    ->delete();

                $purchase->delete();
            }

            $expense->delete();
        });

        ActivityLog::record(
            'expense.deleted',
            "Deleted expense #{$expenseId} ({$categoryName}, MVR " . number_format($amount, 2) . ($accountName ? ", from {$accountName}" : '') . ')',
            null,
            ['expense_id' => $expenseId, 'amount' => $amount, 'category' => $categoryName, 'account' => $accountName]
        );

        return redirect()->route('expenses.index')
            ->with('success', 'Expense deleted and account balance restored.');
    }

    private function applyInventoryPurchases(Expense $expense, array $validated): void
    {
        $category = ExpenseCategory::find($validated['expense_category_id']);
        if (!$category || $category->type !== ExpenseCategory::TYPE_COGS) {
            return;
        }

        $rows = $validated['purchases'] ?? [];
        foreach ($rows as $row) {
            $quantity = (float) ($row['quantity'] ?? 0);
            $unitCost = (float) ($row['unit_cost'] ?? 0);
            if ($quantity <= 0) {
                continue;
            }

            $inventoryItem = null;
            if (!empty($row['inventory_item_id'])) {
                $inventoryItem = InventoryItem::find($row['inventory_item_id']);
            } elseif (!empty($row['name'])) {
                $inventoryItem = InventoryItem::create([
                    'name' => $row['name'],
                    'inventory_category_id' => $row['inventory_category_id'] ?? null,
                    'category' => $row['category'] ?? $validated['business_unit'],
                    'unit' => $row['unit'] ?? 'pcs',
                    'brand' => $row['brand'] ?? null,
                    'sku' => $row['sku'] ?? null,
                    'cost_price' => $unitCost,
                    'sell_price' => (float) ($row['sell_price'] ?? 0),
                    'quantity' => 0,
                    'is_service' => false,
                    'is_active' => true,
                ]);
            }

            if (!$inventoryItem) {
                continue;
            }

            $totalCost = round($quantity * $unitCost, 2);

            InventoryPurchase::create([
                'inventory_item_id' => $inventoryItem->id,
                'expense_id' => $expense->id,
                'business_unit' => $validated['business_unit'],
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
                'purchased_at' => $validated['incurred_at'],
                'vendor' => $expense->vendor,
                'reference' => $validated['reference'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            $oldQty = (int) $inventoryItem->quantity;
            $oldCost = (float) $inventoryItem->cost_price;
            $newQty = $oldQty + $quantity;
            if ($newQty > 0) {
                $weightedCost = (($oldQty * $oldCost) + ($quantity * $unitCost)) / $newQty;
                $inventoryItem->cost_price = round($weightedCost, 2);
            }
            $inventoryItem->quantity = $newQty;
            if (!empty($row['sell_price']) && (float) $row['sell_price'] > 0) {
                $inventoryItem->sell_price = (float) $row['sell_price'];
            }
            $inventoryItem->save();

            InventoryLog::create([
                'inventory_item_id' => $inventoryItem->id,
                'job_id' => null,
                'quantity_change' => $quantity,
                'type' => 'purchase',
                'user_id' => Auth::id(),
                'notes' => trim('Purchase for expense #' . $expense->id),
            ]);
        }
    }

    private function sanitizePurchases(Request $request): array
    {
        $rows = $request->input('purchases', []);
        $clean = [];

        foreach ($rows as $index => $row) {
            $hasAny = false;
            foreach ($row as $value) {
                if ($value !== null && $value !== '') {
                    $hasAny = true;
                    break;
                }
            }
            if (!$hasAny) {
                continue;
            }

            $hasItem = !empty($row['inventory_item_id']);
            $hasName = !empty($row['name']);
            if ($hasItem && $hasName) {
                return $this->purchaseError($index, 'Choose an existing item or enter a new item name, not both.');
            }
            if (!$hasItem && !$hasName) {
                return $this->purchaseError($index, 'Select an inventory item or enter a new item name.');
            }
            if ($hasName) {
                if (empty($row['sku'])) {
                    return $this->purchaseError($index, 'SKU is required for new items.');
                }
                if (empty($row['inventory_category_id'])) {
                    return $this->purchaseError($index, 'Category is required for new items.');
                }
            }

            $quantity = (float) ($row['quantity'] ?? 0);
            if ($quantity <= 0) {
                return $this->purchaseError($index, 'Quantity must be greater than 0.');
            }

            if (!array_key_exists('unit_cost', $row) || $row['unit_cost'] === '' || $row['unit_cost'] === null) {
                return $this->purchaseError($index, 'Unit cost is required.');
            }

            $clean[] = $row;
        }

        return ['rows' => $clean, 'error' => null];
    }

    private function purchaseError(int $index, string $message): array
    {
        return ['rows' => [], 'error' => ['index' => $index, 'message' => $message]];
    }

}
