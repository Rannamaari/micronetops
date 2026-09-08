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
use App\Services\PettyCashAccountService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ExpenseController extends Controller
{
    public function __construct(private PettyCashAccountService $pettyCashAccounts)
    {
    }

    public function index(Request $request)
    {
        $filters = $this->expenseFilters($request, 'all');
        $query = $this->buildExpenseQuery($filters)->with(['category', 'vendorEntity', 'account']);

        $expenses = $query
            ->orderByDesc('incurred_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();
        $categories = ExpenseCategory::where('is_active', true)->orderBy('name')->get();
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();
        $businessUnits = Expense::getBusinessUnits();
        $types = ExpenseCategory::getTypes();
        $dueExpenseCount = Expense::where('is_paid', false)->count();
        $dueExpenseTotal = round((float) Expense::where('is_paid', false)->sum('amount'), 2);

        return view('expenses.index', array_merge(
            compact('expenses', 'categories', 'vendors', 'businessUnits', 'types', 'dueExpenseCount', 'dueExpenseTotal'),
            $filters
        ));
    }

    public function reports(Request $request)
    {
        if (!Gate::allows('view-reports')) {
            abort(403, 'Unauthorized. You do not have permission to view reports.');
        }

        $filters = $this->expenseFilters($request, 'month');
        $expenses = $this->buildExpenseQuery($filters)
            ->with(['category', 'vendorEntity', 'account', 'inventoryPurchases'])
            ->orderByDesc('incurred_at')
            ->orderByDesc('id')
            ->get();

        if ($request->query('export') === 'gst_csv') {
            return $this->exportGstCsv($expenses);
        }

        $businessUnits = Expense::getBusinessUnits();
        $types = ExpenseCategory::getTypes();
        $categories = ExpenseCategory::where('is_active', true)->orderBy('name')->get();
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();

        $totalExpenses = round((float) $expenses->sum('amount'), 2);
        $netExpenseTotal = round((float) $expenses->sum(fn (Expense $expense) => (float) ($expense->subtotal_amount ?: $expense->amount)), 2);
        $gstExpenses = $expenses->where('is_gst_applicable', true)->values();
        $gstExpenseCount = $gstExpenses->count();
        $gstSubtotal = round((float) $gstExpenses->sum('subtotal_amount'), 2);
        $gstTotal = round((float) $gstExpenses->sum('gst_amount'), 2);
        $gstGrossTotal = round((float) $gstExpenses->sum('amount'), 2);
        $cogsTotal = round((float) $expenses->filter(fn (Expense $expense) => $expense->category?->type === ExpenseCategory::TYPE_COGS)->sum('amount'), 2);
        $operatingTotal = round((float) $expenses->filter(fn (Expense $expense) => $expense->category?->type === ExpenseCategory::TYPE_OPERATING)->sum('amount'), 2);
        $otherTotal = round((float) $expenses->filter(fn (Expense $expense) => $expense->category?->type === ExpenseCategory::TYPE_OTHER)->sum('amount'), 2);
        $averageExpense = $expenses->count() > 0 ? round($totalExpenses / $expenses->count(), 2) : 0;
        $cogsPurchaseTotal = round((float) $expenses->flatMap->inventoryPurchases->sum('total_cost'), 2);

        $unitSummary = $expenses
            ->groupBy('business_unit')
            ->map(function ($group, $unit) use ($businessUnits) {
                return [
                    'label' => $businessUnits[$unit] ?? strtoupper((string) $unit),
                    'count' => $group->count(),
                    'amount' => round((float) $group->sum('amount'), 2),
                ];
            })
            ->sortByDesc('amount')
            ->values();

        $categorySummary = $expenses
            ->groupBy(fn (Expense $expense) => $expense->category?->name ?? 'Uncategorized')
            ->map(function ($group, $name) {
                $first = $group->first();

                return [
                    'name' => $name,
                    'type' => $first?->category?->type ?? ExpenseCategory::TYPE_OTHER,
                    'count' => $group->count(),
                    'amount' => round((float) $group->sum('amount'), 2),
                ];
            })
            ->sortByDesc('amount')
            ->values();

        $vendorSummary = $expenses
            ->groupBy(fn (Expense $expense) => $expense->vendorEntity?->name ?? $expense->vendor ?? 'Unknown Vendor')
            ->map(function ($group, $name) {
                $lastDate = $group
                    ->pluck('incurred_at')
                    ->filter()
                    ->sortDesc()
                    ->first();

                return [
                    'name' => $name,
                    'count' => $group->count(),
                    'amount' => round((float) $group->sum('amount'), 2),
                    'last_incurred_at' => $lastDate ? $lastDate->format('d M Y') : '-',
                ];
            })
            ->sortByDesc('amount')
            ->values();

        $accountSummary = $expenses
            ->groupBy(fn (Expense $expense) => $expense->account?->name ?? 'No Account')
            ->map(function ($group, $name) {
                return [
                    'name' => $name,
                    'count' => $group->count(),
                    'amount' => round((float) $group->sum('amount'), 2),
                ];
            })
            ->sortByDesc('amount')
            ->values();

        $monthlySummary = $expenses
            ->groupBy(fn (Expense $expense) => optional($expense->incurred_at)->format('Y-m'))
            ->map(function ($group, $monthKey) {
                $month = $group->pluck('incurred_at')->filter()->sortDesc()->first();

                return [
                    'month_key' => $monthKey,
                    'label' => $month ? $month->copy()->startOfMonth()->format('M Y') : 'Unknown',
                    'count' => $group->count(),
                    'amount' => round((float) $group->sum('amount'), 2),
                    'cogs_amount' => round((float) $group->filter(fn (Expense $expense) => $expense->category?->type === ExpenseCategory::TYPE_COGS)->sum('amount'), 2),
                    'operating_amount' => round((float) $group->filter(fn (Expense $expense) => $expense->category?->type === ExpenseCategory::TYPE_OPERATING)->sum('amount'), 2),
                    'other_amount' => round((float) $group->filter(fn (Expense $expense) => $expense->category?->type === ExpenseCategory::TYPE_OTHER)->sum('amount'), 2),
                ];
            })
            ->sortByDesc('month_key')
            ->values();

        $unitChart = [
            'labels' => $unitSummary->pluck('label')->values()->all(),
            'amounts' => $unitSummary->pluck('amount')->map(fn ($value) => round((float) $value, 2))->values()->all(),
            'entries' => $unitSummary->pluck('count')->values()->all(),
        ];

        $monthlyUnitGroups = $expenses
            ->groupBy(fn (Expense $expense) => optional($expense->incurred_at)->format('Y-m'))
            ->sortKeys();

        $monthlyUnitChart = [
            'labels' => $monthlyUnitGroups->keys()->map(function ($monthKey) {
                return Carbon::createFromFormat('Y-m', $monthKey)->format('M Y');
            })->values()->all(),
            'datasets' => [],
        ];

        $chartPalette = [
            Expense::UNIT_MOTO => ['label' => Expense::getBusinessUnits()[Expense::UNIT_MOTO], 'background' => 'rgba(249, 115, 22, 0.75)', 'border' => 'rgb(249, 115, 22)'],
            Expense::UNIT_AC => ['label' => Expense::getBusinessUnits()[Expense::UNIT_AC], 'background' => 'rgba(16, 185, 129, 0.75)', 'border' => 'rgb(16, 185, 129)'],
            Expense::UNIT_IT => ['label' => Expense::getBusinessUnits()[Expense::UNIT_IT], 'background' => 'rgba(59, 130, 246, 0.75)', 'border' => 'rgb(59, 130, 246)'],
            Expense::UNIT_EASYFIX => ['label' => Expense::getBusinessUnits()[Expense::UNIT_EASYFIX], 'background' => 'rgba(139, 92, 246, 0.75)', 'border' => 'rgb(139, 92, 246)'],
            Expense::UNIT_SHARED => ['label' => Expense::getBusinessUnits()[Expense::UNIT_SHARED], 'background' => 'rgba(107, 114, 128, 0.75)', 'border' => 'rgb(107, 114, 128)'],
        ];

        foreach ($chartPalette as $unitKey => $style) {
            $values = $monthlyUnitGroups->map(function ($group) use ($unitKey) {
                return round((float) $group->where('business_unit', $unitKey)->sum('amount'), 2);
            })->values()->all();

            if (collect($values)->sum() <= 0) {
                continue;
            }

            $monthlyUnitChart['datasets'][] = [
                'label' => $style['label'],
                'data' => $values,
                'backgroundColor' => $style['background'],
                'borderColor' => $style['border'],
                'borderWidth' => 1,
            ];
        }

        return view('expenses.reports', array_merge(
            compact(
                'expenses',
                'businessUnits',
                'types',
                'categories',
                'vendors',
                'totalExpenses',
                'netExpenseTotal',
                'gstExpenses',
                'gstExpenseCount',
                'gstSubtotal',
                'gstTotal',
                'gstGrossTotal',
                'cogsTotal',
                'operatingTotal',
                'otherTotal',
                'averageExpense',
                'cogsPurchaseTotal',
                'unitSummary',
                'categorySummary',
                'vendorSummary',
                'accountSummary',
                'monthlySummary',
                'unitChart',
                'monthlyUnitChart'
            ),
            $filters
        ));
    }

    public function show(Expense $expense)
    {
        $expense->load(['category', 'vendorEntity', 'account', 'creator', 'updater', 'inventoryPurchases.inventoryItem']);
        $businessUnits = Expense::getBusinessUnits();
        $accounts = $this->availableExpenseAccounts();

        return view('expenses.show', compact('expense', 'businessUnits', 'accounts'));
    }

    public function create(Request $request)
    {
        $categories = ExpenseCategory::where('is_active', true)->orderBy('name')->get();
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();
        $businessUnits = Expense::getBusinessUnits();
        $accounts = $this->availableExpenseAccounts();
        $inventoryItems = InventoryItem::where('is_active', true)->where('is_service', false)->orderBy('name')->get();
        $inventoryCategories = InventoryCategory::where('is_active', true)->orderBy('name')->get();
        $defaultDate = $this->requestedExpenseDate($request);
        [$defaultCategoryId, $defaultAccountId, $defaultBusinessUnit, $defaultIsPaid, $defaultIsGst] = $this->expenseFormDefaults($request, $categories, $accounts);
        $vendorsJson = $vendors->map(function ($vendor) {
            return [
                'name' => $vendor->name,
                'phone' => $vendor->phone,
                'contact_name' => $vendor->contact_name,
                'address' => $vendor->address,
            ];
        })->values()->toJson();

        return view('expenses.create', compact('categories', 'vendors', 'businessUnits', 'vendorsJson', 'accounts', 'inventoryItems', 'inventoryCategories', 'defaultDate', 'defaultCategoryId', 'defaultAccountId', 'defaultBusinessUnit', 'defaultIsPaid', 'defaultIsGst'));
    }

    public function createCogs(Request $request)
    {
        $categories = ExpenseCategory::where('is_active', true)
            ->where('type', ExpenseCategory::TYPE_COGS)
            ->orderBy('name')
            ->get();
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();
        $businessUnits = Expense::getBusinessUnits();
        $accounts = $this->availableExpenseAccounts();
        $inventoryItems = InventoryItem::where('is_active', true)->where('is_service', false)->orderBy('name')->get();
        $inventoryCategories = InventoryCategory::where('is_active', true)->orderBy('name')->get();
        $defaultDate = $this->requestedExpenseDate($request);
        [$defaultCategoryId, $defaultAccountId, $defaultBusinessUnit, $defaultIsPaid, $defaultIsGst] = $this->expenseFormDefaults($request, $categories, $accounts);
        $vendorsJson = $vendors->map(function ($vendor) {
            return [
                'name' => $vendor->name,
                'phone' => $vendor->phone,
                'contact_name' => $vendor->contact_name,
                'address' => $vendor->address,
            ];
        })->values()->toJson();

        return view('expenses.create-cogs', compact('categories', 'vendors', 'businessUnits', 'vendorsJson', 'accounts', 'inventoryItems', 'inventoryCategories', 'defaultDate', 'defaultCategoryId', 'defaultAccountId', 'defaultBusinessUnit', 'defaultIsPaid', 'defaultIsGst'));
    }

    public function createOperating(Request $request)
    {
        $categories = ExpenseCategory::where('is_active', true)
            ->whereIn('type', [ExpenseCategory::TYPE_OPERATING, ExpenseCategory::TYPE_OTHER])
            ->orderBy('name')
            ->get();
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();
        $businessUnits = Expense::getBusinessUnits();
        $accounts = $this->availableExpenseAccounts();
        $inventoryItems = InventoryItem::where('is_active', true)->where('is_service', false)->orderBy('name')->get();
        $inventoryCategories = InventoryCategory::where('is_active', true)->orderBy('name')->get();
        $defaultDate = $this->requestedExpenseDate($request);
        [$defaultCategoryId, $defaultAccountId, $defaultBusinessUnit, $defaultIsPaid, $defaultIsGst] = $this->expenseFormDefaults($request, $categories, $accounts);
        $vendorsJson = $vendors->map(function ($vendor) {
            return [
                'name' => $vendor->name,
                'phone' => $vendor->phone,
                'contact_name' => $vendor->contact_name,
                'address' => $vendor->address,
            ];
        })->values()->toJson();

        return view('expenses.create-operating', compact('categories', 'vendors', 'businessUnits', 'vendorsJson', 'accounts', 'inventoryItems', 'inventoryCategories', 'defaultDate', 'defaultCategoryId', 'defaultAccountId', 'defaultBusinessUnit', 'defaultIsPaid', 'defaultIsGst'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'is_paid' => $request->has('is_paid') ? $request->boolean('is_paid') : true,
            'is_gst_applicable' => $request->boolean('is_gst_applicable'),
        ]);

        $validated = $request->validate([
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'vendor_id' => ['required', 'exists:vendors,id'],
            'is_paid' => ['required', 'boolean'],
            'account_id' => ['nullable', 'required_if:is_paid,1', 'exists:accounts,id'],
            'business_unit' => ['required', 'in:' . implode(',', array_keys(Expense::getBusinessUnits()))],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'is_gst_applicable' => ['required', 'boolean'],
            'gst_expenditure_type' => ['nullable', 'in:revenue,capital'],
            'incurred_at' => ['required', 'date'],
            'due_date' => ['nullable', 'date'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'purchases' => ['nullable', 'array'],
            'purchases.*.inventory_item_id' => ['nullable', 'exists:inventory_items,id'],
            'purchases.*.name' => ['nullable', 'string', 'max:255'],
            'purchases.*.category' => ['nullable', 'in:moto,ac,it,easyfix'],
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
        $validated['paid_at'] = $validated['is_paid'] ? $validated['incurred_at'] : null;
        $validated['due_date'] = $validated['is_paid'] ? null : ($validated['due_date'] ?? null);
        $vendor = Vendor::find($validated['vendor_id']);
        $validated['vendor'] = $vendor?->name;
        if ($validated['is_gst_applicable'] && blank($vendor?->gst_number)) {
            return back()->withErrors(['vendor_id' => 'A vendor GST TIN is required for a GST tax invoice. Edit the vendor and add its GST number.'])->withInput();
        }
        if ($validated['is_gst_applicable'] && blank($validated['reference'] ?? null)) {
            return back()->withErrors(['reference' => 'Invoice / bill number is required for a GST tax invoice.'])->withInput();
        }
        $validated = $this->calculateExpenseGst($validated);

        try {
            $expense = DB::transaction(function () use ($validated) {
                $expense = Expense::create($validated);

                if ($validated['is_paid']) {
                    $account = Account::lockForUpdate()->findOrFail($validated['account_id']);
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

                    $this->pettyCashAccounts->syncExpense($expense, $account);
                }

                $this->applyInventoryPurchases($expense, $validated);

                return $expense;
            });
        } catch (\RuntimeException $e) {
            return back()->withErrors(['account_id' => $e->getMessage()])->withInput();
        }

        $paymentLabel = $validated['is_paid'] ? 'paid' : 'due';
        ActivityLog::record('expense.created', "Expense recorded — {$validated['amount']} MVR ({$paymentLabel}) via " . (Vendor::find($validated['vendor_id'])?->name ?? 'unknown vendor'));

        $expense->loadMissing(['category', 'vendorEntity']);
        $createRoute = $expense->category?->type === ExpenseCategory::TYPE_COGS
            ? 'expenses.create-cogs'
            : 'expenses.create-operating';
        $nextExpenseUrl = route($createRoute, [
            'date' => $expense->incurred_at->toDateString(),
            'expense_category_id' => $expense->expense_category_id,
            'account_id' => $expense->account_id,
            'business_unit' => $expense->business_unit,
            'is_paid' => $expense->is_paid ? 1 : 0,
            'is_gst_applicable' => $expense->is_gst_applicable ? 1 : 0,
            'gst_expenditure_type' => $expense->gst_expenditure_type,
        ]);

        return redirect($nextExpenseUrl)
            ->with('success', 'Expense added successfully. The form is ready for another expense.')
            ->with('last_expense', [
                'id' => $expense->id,
                'amount' => (float) $expense->amount,
                'date' => $expense->incurred_at->toDateString(),
                'date_label' => $expense->incurred_at->format('d M Y'),
                'vendor' => $expense->vendorEntity?->name ?? $expense->vendor ?? 'No vendor',
                'category' => $expense->category?->name ?? 'Expense',
                'invoice_number' => $expense->reference,
                'is_paid' => $expense->is_paid,
                'add_another_url' => $nextExpenseUrl,
            ]);
    }

    public function edit(Expense $expense)
    {
        $categories = ExpenseCategory::where('is_active', true)->orderBy('name')->get();
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();
        $businessUnits = Expense::getBusinessUnits();
        $accounts = $this->availableExpenseAccounts();
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
        $request->merge([
            'is_paid' => $request->has('is_paid') ? $request->boolean('is_paid') : true,
            'is_gst_applicable' => $request->boolean('is_gst_applicable'),
        ]);

        $validated = $request->validate([
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'vendor_id' => ['required', 'exists:vendors,id'],
            'is_paid' => ['required', 'boolean'],
            'account_id' => ['nullable', 'required_if:is_paid,1', 'exists:accounts,id'],
            'business_unit' => ['required', 'in:' . implode(',', array_keys(Expense::getBusinessUnits()))],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'is_gst_applicable' => ['required', 'boolean'],
            'gst_expenditure_type' => ['nullable', 'in:revenue,capital'],
            'incurred_at' => ['required', 'date'],
            'due_date' => ['nullable', 'date'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'purchases' => ['nullable', 'array'],
            'purchases.*.inventory_item_id' => ['nullable', 'exists:inventory_items,id'],
            'purchases.*.name' => ['nullable', 'string', 'max:255'],
            'purchases.*.category' => ['nullable', 'in:moto,ac,it,easyfix'],
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
        $validated['paid_at'] = $validated['is_paid']
            ? ($expense->is_paid ? ($expense->paid_at?->toDateString() ?? $validated['incurred_at']) : now()->toDateString())
            : null;
        $validated['due_date'] = $validated['is_paid'] ? null : ($validated['due_date'] ?? null);
        $vendor = Vendor::find($validated['vendor_id']);
        $validated['vendor'] = $vendor?->name;
        if ($validated['is_gst_applicable'] && blank($vendor?->gst_number)) {
            return back()->withErrors(['vendor_id' => 'A vendor GST TIN is required for a GST tax invoice. Edit the vendor and add its GST number.'])->withInput();
        }
        if ($validated['is_gst_applicable'] && blank($validated['reference'] ?? null)) {
            return back()->withErrors(['reference' => 'Invoice / bill number is required for a GST tax invoice.'])->withInput();
        }
        $validated = $this->calculateExpenseGst($validated);

        try {
            DB::transaction(function () use ($validated, $expense) {
                $previousAccountId = $expense->account_id;
                $previousAmount = (float) $expense->amount;
                $wasPaid = (bool) $expense->is_paid;

                // Revert previous account impact if it existed
                if ($wasPaid && $previousAccountId) {
                    $prevAccount = Account::lockForUpdate()->find($previousAccountId);
                    if ($prevAccount) {
                        $prevAccount->balance = (float) $prevAccount->balance + $previousAmount;
                        $prevAccount->save();

                        AccountTransaction::create([
                            'account_id' => $prevAccount->id,
                            'type' => 'expense_reversal',
                            'amount' => $previousAmount,
                            'occurred_at' => $validated['incurred_at'],
                            'description' => 'Expense amendment reversal: #' . $expense->id,
                            'related_type' => Expense::class,
                            'related_id' => $expense->id,
                            'created_by' => Auth::id(),
                        ]);
                    }
                }

                $this->pettyCashAccounts->reverseExpense($expense);
                $this->reverseInventoryPurchases($expense);
                $expense->update($validated);

                if ($validated['is_paid']) {
                    $account = Account::lockForUpdate()->findOrFail($validated['account_id']);
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
                        'occurred_at' => $validated['paid_at'],
                        'description' => 'Expense update: ' . ($expense->category?->name ?? 'Expense'),
                        'related_type' => Expense::class,
                        'related_id' => $expense->id,
                        'created_by' => Auth::id(),
                    ]);

                    $this->pettyCashAccounts->syncExpense($expense, $account);
                }

                $this->applyInventoryPurchases($expense, $validated);
            });
        } catch (\RuntimeException $e) {
            return back()->withErrors(['account_id' => $e->getMessage()])->withInput();
        }

        return redirect()->route('expenses.index')
            ->with('success', 'Expense updated successfully.');
    }

    public function markPaid(Request $request, Expense $expense)
    {
        if ($expense->is_paid) {
            return back()->withErrors(['payment' => 'This expense is already marked as paid.']);
        }

        $validated = $request->validate([
            'account_id' => ['required', 'exists:accounts,id'],
            'paid_at' => ['required', 'date'],
        ]);

        try {
            DB::transaction(function () use ($validated, $expense) {
                $lockedExpense = Expense::lockForUpdate()->findOrFail($expense->id);
                if ($lockedExpense->is_paid) {
                    throw new \RuntimeException('This expense has already been paid.');
                }

                $account = Account::lockForUpdate()->findOrFail($validated['account_id']);
                $amount = (float) $lockedExpense->amount;
                if ((float) $account->balance < $amount) {
                    throw new \RuntimeException('Selected account has no available balance.');
                }

                $account->balance = (float) $account->balance - $amount;
                $account->save();

                $lockedExpense->update([
                    'account_id' => $account->id,
                    'is_paid' => true,
                    'paid_at' => $validated['paid_at'],
                    'updated_by' => Auth::id(),
                ]);

                AccountTransaction::create([
                    'account_id' => $account->id,
                    'type' => 'expense',
                    'amount' => -$amount,
                    'occurred_at' => $validated['paid_at'],
                    'description' => 'Payment for expense #' . $lockedExpense->id,
                    'related_type' => Expense::class,
                    'related_id' => $lockedExpense->id,
                    'created_by' => Auth::id(),
                ]);

                $this->pettyCashAccounts->syncExpense($lockedExpense, $account);
            });
        } catch (\RuntimeException $e) {
            return back()->withErrors(['account_id' => $e->getMessage()])->withInput();
        }

        ActivityLog::record('expense.paid', "Expense #{$expense->id} marked paid — MVR {$expense->amount}");

        return redirect()->route('expenses.show', $expense)
            ->with('success', 'Expense marked as paid and the account balance was updated.');
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
            $this->pettyCashAccounts->reverseExpense($expense);

            // Restore account balance and write a reversal transaction (keeps audit trail)
            if ($expense->is_paid && $expense->account_id) {
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
            $this->reverseInventoryPurchases($expense);

            $expense->delete();
        });

        ActivityLog::record(
            'expense.deleted',
            "Deleted expense #{$expenseId} ({$categoryName}, MVR " . number_format($amount, 2) . ($accountName ? ", from {$accountName}" : '') . ')',
            null,
            ['expense_id' => $expenseId, 'amount' => $amount, 'category' => $categoryName, 'account' => $accountName]
        );

        return redirect()->route('expenses.index')
            ->with('success', $expense->is_paid
                ? 'Expense deleted and account balance restored.'
                : 'Due expense deleted. No account balance was changed.');
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

    private function reverseInventoryPurchases(Expense $expense): void
    {
        $purchases = InventoryPurchase::where('expense_id', $expense->id)->get();

        foreach ($purchases as $purchase) {
            $item = InventoryItem::find($purchase->inventory_item_id);
            if ($item) {
                $item->quantity = max(0, (float) $item->quantity - (float) $purchase->quantity);
                $item->save();
            }

            InventoryLog::where('inventory_item_id', $purchase->inventory_item_id)
                ->where('notes', 'Purchase for expense #' . $expense->id)
                ->delete();

            $purchase->delete();
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

    private function availableExpenseAccounts()
    {
        return Account::with('custodian')
            ->where('is_active', true)
            ->where(function ($query) {
                $query->where('is_system', false)
                    ->orWhere('is_petty_cash', true);
            })
            ->orderBy('is_petty_cash')
            ->orderBy('name')
            ->get();
    }

    private function requestedExpenseDate(Request $request): string
    {
        $date = $request->query('date');

        if (!$date) {
            return now()->toDateString();
        }

        try {
            $parsed = Carbon::createFromFormat('Y-m-d', $date);

            return $parsed->toDateString() === $date
                ? $parsed->toDateString()
                : now()->toDateString();
        } catch (\Throwable) {
            return now()->toDateString();
        }
    }

    private function expenseFormDefaults(Request $request, $categories, $accounts): array
    {
        $requestedCategoryId = (int) $request->query('expense_category_id', 0);
        $requestedAccountId = (int) $request->query('account_id', 0);
        $requestedBusinessUnit = (string) $request->query('business_unit', '');
        $requestedPaid = $request->query('is_paid');
        $requestedGst = $request->query('is_gst_applicable');

        $defaultCategoryId = $categories->contains('id', $requestedCategoryId) ? $requestedCategoryId : null;
        $defaultAccountId = $accounts->contains('id', $requestedAccountId) ? $requestedAccountId : null;
        $defaultBusinessUnit = array_key_exists($requestedBusinessUnit, Expense::getBusinessUnits())
            ? $requestedBusinessUnit
            : null;
        $defaultIsPaid = $requestedPaid === null
            ? true
            : filter_var($requestedPaid, FILTER_VALIDATE_BOOLEAN);
        $defaultIsGst = $requestedGst === null
            ? false
            : filter_var($requestedGst, FILTER_VALIDATE_BOOLEAN);

        return [$defaultCategoryId, $defaultAccountId, $defaultBusinessUnit, $defaultIsPaid, $defaultIsGst];
    }

    private function calculateExpenseGst(array $validated): array
    {
        $subtotal = round((float) $validated['amount'], 2);
        $gstRate = $validated['is_gst_applicable'] ? 8.00 : 0.00;
        $gstAmount = $validated['is_gst_applicable'] ? round($subtotal * 0.08, 2) : 0.00;

        $validated['subtotal_amount'] = $subtotal;
        $validated['gst_rate'] = $gstRate;
        $validated['gst_amount'] = $gstAmount;
        $validated['gst_expenditure_type'] = $validated['is_gst_applicable']
            ? ($validated['gst_expenditure_type'] ?? 'revenue')
            : null;
        $validated['amount'] = round($subtotal + $gstAmount, 2);

        return $validated;
    }

    private function exportGstCsv($expenses)
    {
        $gstExpenses = $expenses->where('is_gst_applicable', true)->values();
        $filename = 'gst-input-tax-expenses-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($gstExpenses) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Supplier TIN',
                'Supplier Name',
                'Supplier Invoice Number',
                'Invoice Date',
                'Invoice Total Excluding GST',
                'GST Charged at 8%',
                'Invoice Total Including GST',
                'Business Unit',
                'Expense Category',
                'Payment Status',
            ]);

            foreach ($gstExpenses as $expense) {
                fputcsv($handle, [
                    $expense->vendorEntity?->gst_number,
                    $expense->vendorEntity?->name ?? $expense->vendor,
                    $expense->reference,
                    $expense->incurred_at?->format('Y-m-d'),
                    number_format((float) $expense->subtotal_amount, 2, '.', ''),
                    number_format((float) $expense->gst_amount, 2, '.', ''),
                    number_format((float) $expense->amount, 2, '.', ''),
                    Expense::getBusinessUnits()[$expense->business_unit] ?? $expense->business_unit,
                    $expense->category?->name,
                    $expense->is_paid ? 'Paid' : 'Due',
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function expenseFilters(Request $request, string $defaultPeriod = 'month'): array
    {
        $period = $request->query('period', $defaultPeriod);
        $businessUnit = $request->query('business_unit', 'all');
        $type = $request->query('type', 'all');
        $paymentStatus = $request->query('payment_status', 'all');
        $search = trim((string) $request->query('search', ''));
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');

        return compact('period', 'businessUnit', 'type', 'paymentStatus', 'search', 'fromDate', 'toDate');
    }

    private function buildExpenseQuery(array $filters)
    {
        $query = Expense::query();

        if (($filters['businessUnit'] ?? 'all') !== 'all') {
            $query->where('business_unit', $filters['businessUnit']);
        }

        if (($filters['type'] ?? 'all') !== 'all') {
            $query->whereHas('category', function ($q) use ($filters) {
                $q->where('type', $filters['type']);
            });
        }

        if (($filters['paymentStatus'] ?? 'all') === 'paid') {
            $query->where('is_paid', true);
        } elseif (($filters['paymentStatus'] ?? 'all') === 'due') {
            $query->where('is_paid', false);
        }

        if (($filters['search'] ?? '') !== '') {
            $s = mb_strtolower($filters['search']);
            $query->where(function ($q) use ($s) {
                $q->whereRaw('lower(vendor) like ?', ["%{$s}%"])
                    ->orWhereRaw('lower(reference) like ?', ["%{$s}%"])
                    ->orWhereRaw('lower(notes) like ?', ["%{$s}%"]);
            });
        }

        if (!empty($filters['fromDate']) || !empty($filters['toDate'])) {
            $from = !empty($filters['fromDate']) ? Carbon::parse($filters['fromDate'])->toDateString() : null;
            $to = !empty($filters['toDate']) ? Carbon::parse($filters['toDate'])->toDateString() : null;

            if ($from) {
                $query->whereDate('incurred_at', '>=', $from);
            }

            if ($to) {
                $query->whereDate('incurred_at', '<=', $to);
            }

            return $query;
        }

        $today = now()->startOfDay();

        match ($filters['period'] ?? 'all') {
            'today' => $query->whereDate('incurred_at', $today),
            'yesterday' => $query->whereDate('incurred_at', $today->copy()->subDay()),
            'week' => $query->whereBetween('incurred_at', [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()]),
            'month' => $query->whereBetween('incurred_at', [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()]),
            'year' => $query->whereBetween('incurred_at', [$today->copy()->startOfYear(), $today->copy()->endOfYear()]),
            default => null,
        };

        return $query;
    }

}
