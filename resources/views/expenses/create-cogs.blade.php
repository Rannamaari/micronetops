<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Add COGS Expense') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-[1600px] mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('expenses.store') }}" class="space-y-4">
                        @csrf
                        @if ($errors->any())
                            <div class="rounded border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                                <div class="font-medium">Please fix the errors below:</div>
                                <ul class="mt-1 list-disc pl-5">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div>
                            <div class="flex items-center justify-between">
                                <label class="block text-sm font-medium">COGS Category</label>
                                <a href="{{ route('expense-categories.create') }}" class="text-sm text-blue-600 hover:underline">Add New Category</a>
                            </div>
                            <input id="category-search" type="text" placeholder="Search category..." class="mt-1 w-full rounded border-gray-300" />
                            <select id="category-select" name="expense_category_id" class="mt-2 w-full rounded border-gray-300" required>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" data-type="{{ $category->type }}" @selected((string) old('expense_category_id') === (string) $category->id)>{{ $category->name }} ({{ $category->type }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <div class="flex items-center justify-between">
                                <label class="block text-sm font-medium">Vendor</label>
                                <button type="button" id="open-vendor-modal" class="text-sm text-blue-600 hover:underline">Add Vendor</button>
                            </div>
                            <div class="mt-2">
                                <select id="vendor-select" name="vendor_id" class="mt-1 w-full rounded border-gray-300" required>
                                    <option value="">Select vendor</option>
                                    @foreach ($vendors as $vendor)
                                        <option value="{{ $vendor->id }}" @selected((string) old('vendor_id') === (string) $vendor->id)>{{ $vendor->name }} ({{ $vendor->phone }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Business Unit</label>
                            <select id="business-unit" name="business_unit" class="mt-1 w-full rounded border-gray-300" required>
                                @foreach ($businessUnits as $key => $label)
                                    <option value="{{ $key }}" @selected(old('business_unit') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Paid From Account</label>
                            <select name="account_id" class="mt-1 w-full rounded border-gray-300" required>
                                <option value="">Select account</option>
                                @foreach ($accounts as $account)
                                    <option value="{{ $account->id }}" @selected((string) old('account_id') === (string) $account->id)>{{ $account->name }} ({{ number_format($account->balance, 2) }})</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Balance is shown in parentheses.</p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium">Amount</label>
                                <input id="expense-amount" type="number" step="0.01" name="amount" class="mt-1 w-full rounded border-gray-300" value="{{ old('amount') }}" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium">Incurred At</label>
                                <input type="date" name="incurred_at" class="mt-1 w-full rounded border-gray-300" value="{{ old('incurred_at', now()->toDateString()) }}" required>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Reference</label>
                            <input name="reference" class="mt-1 w-full rounded border-gray-300" value="{{ old('reference') }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Notes</label>
                            <textarea name="notes" rows="3" class="mt-1 w-full rounded border-gray-300">{{ old('notes') }}</textarea>
                        </div>
                        @php
                            $cogsGrid = 'grid-cols-[minmax(200px,2fr)_minmax(160px,1.5fr)_minmax(100px,1fr)_minmax(140px,1.2fr)_minmax(80px,0.8fr)_minmax(80px,0.8fr)_minmax(110px,1fr)_minmax(110px,1fr)_minmax(50px,auto)_minmax(110px,1fr)]';
                            $cogsInput = 'w-full h-11 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 px-3 text-sm shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500';
                            $cogsSelect = 'w-full h-11 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 pl-3 pr-10 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500';
                            $cogsReadonly = 'w-full h-11 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 dark:text-gray-100 px-3 text-sm text-right tabular-nums font-medium';
                            $cogsLabel = 'block lg:hidden text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1';
                            $cogsTip = '<span class="relative group/tip inline-flex cursor-help"><svg class="w-3.5 h-3.5 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd"/></svg><span class="absolute z-50 top-full left-1/2 -translate-x-1/2 mt-1.5 px-2 py-1 text-[11px] text-white bg-gray-900 dark:bg-gray-600 rounded shadow-lg whitespace-nowrap opacity-0 invisible group-hover/tip:opacity-100 group-hover/tip:visible transition-all pointer-events-none">';
                            $cogsTipEnd = '</span></span>';
                        @endphp

                        <div id="cogs-section" class="border-t pt-6">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">COGS Items (Inventory Purchases)</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Add each item you purchased so inventory is updated.</p>
                                </div>
                                <button type="button" id="add-purchase-row"
                                    class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Add Item
                                </button>
                            </div>

                            <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm overflow-hidden">
                                <div class="overflow-x-auto">
                                    <div class="lg:min-w-[1240px]">
                                        {{-- Header Row (lg+ only) --}}
                                        <div class="hidden lg:grid {{ $cogsGrid }} gap-3 px-5 pr-14 py-3 bg-gray-50 dark:bg-gray-800/80 border-b border-gray-200 dark:border-gray-700">
                                            <div class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide">Item</div>
                                            <div class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide flex items-center gap-1">New Item {!! $cogsTip !!}Fill only if adding a new item{!! $cogsTipEnd !!}</div>
                                            <div class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide flex items-center gap-1">SKU {!! $cogsTip !!}Required for new items{!! $cogsTipEnd !!}</div>
                                            <div class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide">Category</div>
                                            <div class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide flex items-center gap-1">Unit {!! $cogsTip !!}Auto-filled for existing items{!! $cogsTipEnd !!}</div>
                                            <div class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide text-right">Qty</div>
                                            <div class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide text-right">Unit Cost</div>
                                            <div class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide text-right flex items-center justify-end gap-1">Sell Price {!! $cogsTip !!}Auto-filled for existing items{!! $cogsTipEnd !!}</div>
                                            <div class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide text-center flex items-center gap-1">GST {!! $cogsTip !!}Add 8% GST to cost{!! $cogsTipEnd !!}</div>
                                            <div class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide text-right">Total</div>
                                        </div>

                                        {{-- Purchase Rows --}}
                                        <div id="purchase-rows" class="divide-y divide-gray-100 dark:divide-gray-800">
                                            @foreach (old('purchases', []) as $index => $purchase)
                                                <div class="purchase-row relative group/row hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors">
                                                    {{-- Remove button --}}
                                                    <button type="button" class="remove-row absolute top-3 right-3 lg:top-1/2 lg:-translate-y-1/2 z-10 p-1.5 rounded-lg text-gray-300 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition lg:opacity-0 lg:group-hover/row:opacity-100 focus:opacity-100" title="Remove item">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    </button>

                                                    <div class="grid grid-cols-2 gap-3 p-4 pr-12 lg:pr-14 lg:px-5 lg:py-3 lg:grid-cols-[minmax(200px,2fr)_minmax(160px,1.5fr)_minmax(100px,1fr)_minmax(140px,1.2fr)_minmax(80px,0.8fr)_minmax(80px,0.8fr)_minmax(110px,1fr)_minmax(110px,1fr)_minmax(50px,auto)_minmax(110px,1fr)] lg:items-center">
                                                        {{-- Item --}}
                                                        @php
                                                            $selectedItemId = $purchase['inventory_item_id'] ?? '';
                                                            $selectedItemName = '';
                                                            if ($selectedItemId) {
                                                                $selectedItemObj = $inventoryItems->firstWhere('id', (int) $selectedItemId);
                                                                $selectedItemName = $selectedItemObj?->name ?? '';
                                                            }
                                                        @endphp
                                                        <div class="col-span-2 lg:col-auto">
                                                            <label class="{{ $cogsLabel }}">Item</label>
                                                            <input type="hidden" name="purchases[{{ $index }}][inventory_item_id]" class="inventory-item-id" value="{{ $selectedItemId }}">
                                                            <input type="text" list="inventory-datalist" class="{{ $cogsInput }} inventory-item-search" placeholder="Search & select item..." value="{{ $selectedItemName }}" autocomplete="off">
                                                            @error("purchases.$index.inventory_item_id")<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                                        </div>
                                                        {{-- New Item Name --}}
                                                        <div class="col-span-2 lg:col-auto">
                                                            <label class="{{ $cogsLabel }} flex items-center gap-1">New Item {!! $cogsTip !!}Fill only if adding a new item{!! $cogsTipEnd !!}</label>
                                                            <input name="purchases[{{ $index }}][name]" class="{{ $cogsInput }} inventory-item-name" value="{{ $purchase['name'] ?? '' }}" placeholder="New item name">
                                                            @error("purchases.$index.name")<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                                        </div>
                                                        {{-- SKU --}}
                                                        <div>
                                                            <label class="{{ $cogsLabel }}">SKU</label>
                                                            <input name="purchases[{{ $index }}][sku]" class="{{ $cogsInput }} inventory-item-sku" value="{{ $purchase['sku'] ?? '' }}" placeholder="SKU">
                                                            @error("purchases.$index.sku")<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                                        </div>
                                                        {{-- Category --}}
                                                        <div>
                                                            <label class="{{ $cogsLabel }}">Category</label>
                                                            <select name="purchases[{{ $index }}][inventory_category_id]" class="{{ $cogsSelect }} inventory-item-category">
                                                                <option value="">Select</option>
                                                                @foreach ($inventoryCategories as $invCategory)
                                                                    <option value="{{ $invCategory->id }}" @selected((string) ($purchase['inventory_category_id'] ?? '') === (string) $invCategory->id)>{{ $invCategory->name }}</option>
                                                                @endforeach
                                                            </select>
                                                            @error("purchases.$index.inventory_category_id")<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                                        </div>
                                                        {{-- Unit --}}
                                                        <div>
                                                            <label class="{{ $cogsLabel }}">Unit</label>
                                                            <input name="purchases[{{ $index }}][unit]" class="{{ $cogsInput }}" value="{{ $purchase['unit'] ?? '' }}" placeholder="pcs">
                                                            @error("purchases.$index.unit")<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                                        </div>
                                                        {{-- Qty --}}
                                                        <div>
                                                            <label class="{{ $cogsLabel }}">Qty</label>
                                                            <input name="purchases[{{ $index }}][quantity]" type="number" step="0.01" class="{{ $cogsInput }} text-right" value="{{ $purchase['quantity'] ?? '' }}">
                                                            @error("purchases.$index.quantity")<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                                        </div>
                                                        {{-- Unit Cost --}}
                                                        <div>
                                                            <label class="{{ $cogsLabel }}">Unit Cost</label>
                                                            <input name="purchases[{{ $index }}][unit_cost]" type="number" step="0.01" class="{{ $cogsInput }} text-right" value="{{ $purchase['unit_cost'] ?? '' }}">
                                                            @error("purchases.$index.unit_cost")<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                                        </div>
                                                        {{-- Sell Price + Margin --}}
                                                        <div>
                                                            <label class="{{ $cogsLabel }}">Sell Price</label>
                                                            <input name="purchases[{{ $index }}][sell_price]" type="number" step="0.01" class="{{ $cogsInput }} text-right sell-price" value="{{ $purchase['sell_price'] ?? '' }}" placeholder="0.00">
                                                            <p class="mt-0.5 text-[10px] tabular-nums text-right margin-display text-gray-400">&nbsp;</p>
                                                        </div>
                                                        {{-- GST 8% --}}
                                                        <div class="flex items-center lg:justify-center">
                                                            <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                                                <input type="hidden" name="purchases[{{ $index }}][has_gst]" value="0">
                                                                <input type="checkbox" name="purchases[{{ $index }}][has_gst]" value="1" class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 gst-checkbox" @checked(old("purchases.$index.has_gst"))>
                                                                <span class="text-xs text-gray-500 dark:text-gray-400 lg:hidden">+8% GST</span>
                                                            </label>
                                                        </div>
                                                        {{-- Line Total --}}
                                                        <div>
                                                            <label class="{{ $cogsLabel }}">Total</label>
                                                            <input type="text" readonly class="{{ $cogsReadonly }} line-total" value="0.00">
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>

                                        {{-- Empty state --}}
                                        <div id="cogs-empty-state" class="px-5 py-10 text-center text-sm text-gray-400 dark:text-gray-500 {{ old('purchases') ? 'hidden' : '' }}">
                                            No items yet. Click <strong>Add Item</strong> to get started.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3 flex items-center gap-3">
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="checkbox" id="auto-calc-amount" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    Auto-calculate amount from items
                                </label>
                                <span class="text-xs text-gray-500">You can adjust amount for shipping/duty.</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('expenses.index') }}" class="text-gray-600 hover:underline">Cancel</a>
                            <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save Expense</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<datalist id="inventory-datalist">
    @foreach ($inventoryItems as $item)
        <option value="{{ $item->name }}" data-id="{{ $item->id }}" data-unit="{{ $item->unit }}" data-cost="{{ $item->cost_price }}" data-sell="{{ $item->sell_price }}" data-category="{{ $item->category }}" data-sku="{{ $item->sku }}" data-inventory-category-id="{{ $item->inventory_category_id }}">
    @endforeach
</datalist>

<script>

document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('category-search');
    const select = document.getElementById('category-select');
    const vendorSelect = document.getElementById('vendor-select');
    const businessUnitSelect = document.getElementById('business-unit');
    const openVendorModal = document.getElementById('open-vendor-modal');
    const vendorModal = document.getElementById('vendor-modal');
    const vendorModalClose = document.getElementById('vendor-modal-close');
    const vendorModalCloseBtn = document.getElementById('vendor-modal-close-btn');
    const vendorForm = document.getElementById('vendor-form');
    const vendorError = document.getElementById('vendor-error');
    const amountInput = document.getElementById('expense-amount');
    const purchaseRows = document.getElementById('purchase-rows');
    const addPurchaseRow = document.getElementById('add-purchase-row');
    const autoCalcAmount = document.getElementById('auto-calc-amount');

    if (!searchInput || !select) return;

    const applyFilters = () => {
        const query = searchInput.value.toLowerCase();

        let firstVisible = null;
        Array.from(select.options).forEach((opt) => {
            const text = opt.text.toLowerCase();
            const matchesText = !query || text.includes(query);
            const isVisible = matchesText;
            opt.hidden = !isVisible;
            if (isVisible && !firstVisible) {
                firstVisible = opt;
            }
        });

        const current = select.value;
        if (current) {
            const currentOption = select.querySelector(`option[value="${current}"]`);
            if (currentOption && !currentOption.hidden) {
                // keep current selection
            } else if (firstVisible) {
                select.value = firstVisible.value;
            }
        } else if (firstVisible) {
            select.value = firstVisible.value;
        }
    };

    searchInput.addEventListener('input', applyFilters);
    applyFilters();

    const datalistEl = document.getElementById('inventory-datalist');
    const datalistOptions = datalistEl ? Array.from(datalistEl.options) : [];

    const findItemByName = (name) => {
        if (!name) return null;
        const n = name.trim().toLowerCase();
        return datalistOptions.find(opt => opt.value.toLowerCase() === n) || null;
    };

    const rebuildDatalist = () => {
        const unit = businessUnitSelect?.value || '';
        datalistOptions.forEach(opt => {
            const matchesUnit = !unit || unit === 'shared' || (opt.dataset.category || '') === unit;
            opt.disabled = !matchesUnit;
            opt.hidden = !matchesUnit;
        });
    };

    businessUnitSelect?.addEventListener('change', rebuildDatalist);
    rebuildDatalist();

    const setLineTotal = (row, value) => {
        const target = row.querySelector('.line-total');
        if (!target) return;
        if (target.tagName === 'INPUT') {
            target.value = value;
        } else {
            target.textContent = value;
        }
    };

    const calcTotals = () => {
        let total = 0;
        purchaseRows.querySelectorAll('.purchase-row').forEach((row) => {
            const qty = parseFloat(row.querySelector('[name$="[quantity]"]').value || 0);
            const unitCost = parseFloat(row.querySelector('[name$="[unit_cost]"]').value || 0);
            const sellPrice = parseFloat(row.querySelector('.sell-price')?.value || 0);
            const hasGst = row.querySelector('.gst-checkbox')?.checked || false;
            const multiplier = hasGst ? 1.08 : 1;
            const effectiveCost = unitCost * multiplier;
            const lineTotal = qty * effectiveCost;
            setLineTotal(row, lineTotal.toFixed(2));
            total += lineTotal;

            // Margin display
            const marginEl = row.querySelector('.margin-display');
            if (marginEl) {
                if (sellPrice > 0 && effectiveCost > 0) {
                    const margin = ((sellPrice - effectiveCost) / sellPrice) * 100;
                    const color = margin >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-500 dark:text-red-400';
                    marginEl.className = `mt-0.5 text-[10px] tabular-nums text-right margin-display ${color}`;
                    marginEl.textContent = `${margin >= 0 ? '+' : ''}${margin.toFixed(1)}% margin`;
                } else {
                    marginEl.className = 'mt-0.5 text-[10px] tabular-nums text-right margin-display text-gray-400';
                    marginEl.innerHTML = '&nbsp;';
                }
            }
        });
        if (autoCalcAmount && autoCalcAmount.checked && amountInput) {
            amountInput.value = total.toFixed(2);
        }
    };

    const wireRow = (row) => {
        const searchInput = row.querySelector('.inventory-item-search');
        const hiddenId = row.querySelector('.inventory-item-id');
        const nameInput = row.querySelector('.inventory-item-name');
        const skuInput = row.querySelector('.inventory-item-sku');
        const categorySelect = row.querySelector('.inventory-item-category');
        const unitInput = row.querySelector('[name$="[unit]"]');
        const costInput = row.querySelector('[name$="[unit_cost]"]');
        const sellPriceInput = row.querySelector('.sell-price');

        const applyItemSelection = (opt) => {
            if (opt) {
                hiddenId.value = opt.dataset.id || '';
                if (opt.dataset.unit) unitInput.value = opt.dataset.unit;
                if (opt.dataset.cost) costInput.value = opt.dataset.cost;
                if (opt.dataset.sell) sellPriceInput.value = opt.dataset.sell;
                if (opt.dataset.sku) skuInput.value = opt.dataset.sku;
                if (opt.dataset.inventoryCategoryId) categorySelect.value = opt.dataset.inventoryCategoryId;
                nameInput.value = '';
                nameInput.disabled = true;
                skuInput.disabled = true;
                categorySelect.disabled = true;
            } else {
                hiddenId.value = '';
                nameInput.disabled = false;
                skuInput.disabled = false;
                categorySelect.disabled = false;
            }
            calcTotals();
        };

        searchInput.addEventListener('input', () => {
            const matched = findItemByName(searchInput.value);
            applyItemSelection(matched);
        });

        nameInput.addEventListener('input', () => {
            if (nameInput.value.trim()) {
                searchInput.value = '';
                searchInput.disabled = true;
                hiddenId.value = '';
                skuInput.disabled = false;
                categorySelect.disabled = false;
            } else {
                searchInput.disabled = false;
            }
        });

        row.querySelectorAll('input').forEach(input => input.addEventListener('input', calcTotals));
        row.querySelector('.gst-checkbox')?.addEventListener('change', calcTotals);
        row.querySelector('.remove-row').addEventListener('click', () => {
            row.remove();
            toggleEmptyState();
            calcTotals();
        });

        // Initial state for pre-populated rows (old() data)
        if (hiddenId.value) {
            nameInput.disabled = true;
            skuInput.disabled = true;
            categorySelect.disabled = true;
        }
        if (nameInput.value.trim()) {
            searchInput.disabled = true;
            hiddenId.value = '';
        }
    };

    const cogsInputCls = 'w-full h-11 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 px-3 text-sm shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500';
    const cogsSelectCls = 'w-full h-11 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 pl-3 pr-10 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500';
    const cogsReadonlyCls = 'w-full h-11 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 dark:text-gray-100 px-3 text-sm text-right tabular-nums font-medium';
    const cogsLabelCls = 'block lg:hidden text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1';
    const emptyState = document.getElementById('cogs-empty-state');

    const toggleEmptyState = () => {
        if (!emptyState) return;
        emptyState.classList.toggle('hidden', purchaseRows.children.length > 0);
    };

    const addRow = () => {
        const index = purchaseRows.children.length;
        const row = document.createElement('div');
        row.className = 'purchase-row relative group/row hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors';
        row.innerHTML = `
            <button type="button" class="remove-row absolute top-3 right-3 lg:top-1/2 lg:-translate-y-1/2 z-10 p-1.5 rounded-lg text-gray-300 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition lg:opacity-0 lg:group-hover/row:opacity-100 focus:opacity-100" title="Remove item">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            <div class="grid grid-cols-2 gap-3 p-4 pr-12 lg:pr-14 lg:px-5 lg:py-3 lg:grid-cols-[minmax(200px,2fr)_minmax(160px,1.5fr)_minmax(100px,1fr)_minmax(140px,1.2fr)_minmax(80px,0.8fr)_minmax(80px,0.8fr)_minmax(110px,1fr)_minmax(110px,1fr)_minmax(50px,auto)_minmax(110px,1fr)] lg:items-center">
                <div class="col-span-2 lg:col-auto">
                    <label class="${cogsLabelCls}">Item</label>
                    <input type="hidden" name="purchases[${index}][inventory_item_id]" class="inventory-item-id" value="">
                    <input type="text" list="inventory-datalist" class="${cogsInputCls} inventory-item-search" placeholder="Search & select item..." autocomplete="off">
                </div>
                <div class="col-span-2 lg:col-auto">
                    <label class="${cogsLabelCls}">New Item</label>
                    <input name="purchases[${index}][name]" class="${cogsInputCls} inventory-item-name" placeholder="New item name">
                </div>
                <div>
                    <label class="${cogsLabelCls}">SKU</label>
                    <input name="purchases[${index}][sku]" class="${cogsInputCls} inventory-item-sku" placeholder="SKU">
                </div>
                <div>
                    <label class="${cogsLabelCls}">Category</label>
                    <select name="purchases[${index}][inventory_category_id]" class="${cogsSelectCls} inventory-item-category">
                        <option value="">Select</option>
                        @foreach ($inventoryCategories as $invCategory)
                            <option value="{{ $invCategory->id }}">{{ $invCategory->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="${cogsLabelCls}">Unit</label>
                    <input name="purchases[${index}][unit]" class="${cogsInputCls}" placeholder="pcs">
                </div>
                <div>
                    <label class="${cogsLabelCls}">Qty</label>
                    <input name="purchases[${index}][quantity]" type="number" step="0.01" class="${cogsInputCls} text-right">
                </div>
                <div>
                    <label class="${cogsLabelCls}">Unit Cost</label>
                    <input name="purchases[${index}][unit_cost]" type="number" step="0.01" class="${cogsInputCls} text-right">
                </div>
                <div>
                    <label class="${cogsLabelCls}">Sell Price</label>
                    <input name="purchases[${index}][sell_price]" type="number" step="0.01" class="${cogsInputCls} text-right sell-price" placeholder="0.00">
                    <p class="mt-0.5 text-[10px] tabular-nums text-right margin-display text-gray-400">&nbsp;</p>
                </div>
                <div class="flex items-center lg:justify-center">
                    <label class="inline-flex items-center gap-1.5 cursor-pointer">
                        <input type="hidden" name="purchases[${index}][has_gst]" value="0">
                        <input type="checkbox" name="purchases[${index}][has_gst]" value="1" class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 gst-checkbox">
                        <span class="text-xs text-gray-500 dark:text-gray-400 lg:hidden">+8% GST</span>
                    </label>
                </div>
                <div>
                    <label class="${cogsLabelCls}">Total</label>
                    <input type="text" readonly class="${cogsReadonlyCls} line-total" value="0.00">
                </div>
            </div>
        `;
        purchaseRows.appendChild(row);
        wireRow(row);
        toggleEmptyState();
        calcTotals();
    };

    addPurchaseRow?.addEventListener('click', addRow);
    purchaseRows.querySelectorAll('.purchase-row').forEach(wireRow);
    toggleEmptyState();
    calcTotals();

    if (openVendorModal && vendorModal && vendorModalClose) {
        const open = () => vendorModal.classList.remove('hidden');
        const close = () => vendorModal.classList.add('hidden');
        openVendorModal.addEventListener('click', open);
        vendorModalClose.addEventListener('click', close);
        vendorModalCloseBtn?.addEventListener('click', close);
        vendorModal.addEventListener('click', (e) => {
            if (e.target === vendorModal) close();
        });
    }

    if (vendorForm && vendorSelect) {
        vendorForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            vendorError.textContent = '';

            const formData = new FormData(vendorForm);
            try {
                const response = await fetch("{{ route('vendors.store') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                if (!response.ok) {
                    const data = await response.json();
                    vendorError.textContent = data.message || 'Failed to create vendor.';
                    return;
                }

                const data = await response.json();
                const option = document.createElement('option');
                option.value = data.id;
                option.textContent = `${data.name} (${data.phone})`;
                vendorSelect.appendChild(option);
                vendorSelect.value = data.id;
                vendorForm.reset();
                vendorModal.classList.add('hidden');
            } catch (err) {
                vendorError.textContent = 'Failed to create vendor.';
            }
        });
    }
});
</script>

<div id="vendor-modal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg w-full max-w-md p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold">Add Vendor</h3>
            <button type="button" id="vendor-modal-close" class="text-gray-500 hover:text-gray-700">✕</button>
        </div>
        <form id="vendor-form" class="space-y-3">
            <div>
                <label class="block text-sm font-medium">Vendor Name</label>
                <input name="name" class="mt-1 w-full rounded border-gray-300" required>
            </div>
            <div>
                <label class="block text-sm font-medium">Phone</label>
                <input name="phone" class="mt-1 w-full rounded border-gray-300" required>
            </div>
            <div>
                <label class="block text-sm font-medium">Contact Name</label>
                <input name="contact_name" class="mt-1 w-full rounded border-gray-300">
            </div>
            <div>
                <label class="block text-sm font-medium">Address</label>
                <input name="address" class="mt-1 w-full rounded border-gray-300">
            </div>
            <p id="vendor-error" class="text-sm text-red-600"></p>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" id="vendor-modal-close-btn" class="px-4 py-2 text-sm rounded border">Cancel</button>
                <button class="px-4 py-2 text-sm rounded bg-blue-600 text-white">Save Vendor</button>
            </div>
        </form>
    </div>
</div>
