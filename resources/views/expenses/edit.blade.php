<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Expense') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('expenses.update', $expense) }}" class="space-y-4">
                        @csrf
                        @method('PATCH')
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
                                <label class="block text-sm font-medium">Category</label>
                                <a href="{{ route('expense-categories.create') }}" class="text-sm text-blue-600 hover:underline">Add New Category</a>
                            </div>
                            <div class="mt-2 flex flex-wrap gap-3 text-sm">
                                <label class="inline-flex items-center gap-2">
                                    <input type="radio" name="category_type_filter" value="all" checked>
                                    <span>All</span>
                                </label>
                                <label class="inline-flex items-center gap-2">
                                    <input type="radio" name="category_type_filter" value="cogs">
                                    <span>COGS</span>
                                </label>
                                <label class="inline-flex items-center gap-2">
                                    <input type="radio" name="category_type_filter" value="operating">
                                    <span>Operating</span>
                                </label>
                                <label class="inline-flex items-center gap-2">
                                    <input type="radio" name="category_type_filter" value="other">
                                    <span>Other</span>
                                </label>
                            </div>
                            <input id="category-search" type="text" placeholder="Search category..." class="mt-1 w-full rounded border-gray-300" />
                            <select id="category-select" name="expense_category_id" class="mt-2 w-full rounded border-gray-300" required>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" data-type="{{ $category->type }}" @selected((string) old('expense_category_id', $expense->expense_category_id) === (string) $category->id)>
                                        {{ $category->name }} ({{ $category->type }})
                                    </option>
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
                                        <option value="{{ $vendor->id }}" @selected((string) old('vendor_id', $expense->vendor_id) === (string) $vendor->id)>{{ $vendor->name }} ({{ $vendor->phone }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Business Unit</label>
                            <select id="business-unit" name="business_unit" class="mt-1 w-full rounded border-gray-300" required>
                                @foreach ($businessUnits as $key => $label)
                                    <option value="{{ $key }}" @selected(old('business_unit', $expense->business_unit) === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Paid From Account</label>
                            <select name="account_id" class="mt-1 w-full rounded border-gray-300" required>
                                <option value="">Select account</option>
                                @foreach ($accounts as $account)
                                    <option value="{{ $account->id }}" @selected((string) old('account_id', $expense->account_id) === (string) $account->id)>{{ $account->name }} ({{ number_format($account->balance, 2) }})</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Balance is shown in parentheses.</p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium">Amount</label>
                                <input id="expense-amount" type="number" step="0.01" name="amount" class="mt-1 w-full rounded border-gray-300" value="{{ old('amount', $expense->amount) }}" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium">Incurred At</label>
                                <input type="date" name="incurred_at" class="mt-1 w-full rounded border-gray-300" value="{{ old('incurred_at', $expense->incurred_at->toDateString()) }}" required>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Reference</label>
                            <input name="reference" class="mt-1 w-full rounded border-gray-300" value="{{ old('reference', $expense->reference) }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Notes</label>
                            <textarea name="notes" rows="3" class="mt-1 w-full rounded border-gray-300">{{ old('notes', $expense->notes) }}</textarea>
                        </div>
                        <div id="cogs-section" class="hidden border-t pt-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">COGS Items (Inventory Purchases)</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Add each item you purchased so inventory is updated.</p>
                                </div>
                                <button type="button" id="add-purchase-row" class="text-sm text-blue-600 hover:underline">Add Item</button>
                            </div>
                            <div class="mt-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm">
                                <div class="overflow-x-auto">
                                    <div class="min-w-[980px] px-4 py-3 hidden sm:grid grid-cols-12 gap-3 text-xs uppercase tracking-wide text-gray-500 dark:text-gray-300 bg-gray-50 dark:bg-gray-800">
                                        <div class="col-span-5">Item</div>
                                        <div class="col-span-4">New Item</div>
                                        <div class="col-span-3">SKU</div>
                                        <div class="col-span-4">Category</div>
                                        <div class="col-span-2">Unit</div>
                                        <div class="col-span-1 text-right">Qty</div>
                                        <div class="col-span-2 text-right">Unit Cost</div>
                                        <div class="col-span-2 text-right">Line Total</div>
                                        <div class="col-span-1 text-right">Remove</div>
                                    </div>
                                    <div id="purchase-rows" class="space-y-4 p-4">
                                        @foreach (old('purchases', $expense->inventoryPurchases->toArray()) as $index => $purchase)
                                            <div class="purchase-row rounded-lg border border-gray-200 dark:border-gray-700 p-4 bg-white dark:bg-gray-900">
                                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                                                    <div class="sm:col-span-5">
                                                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Inventory Item</label>
                                                        <select name="purchases[{{ $index }}][inventory_item_id]" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-base h-12 px-4 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 inventory-item-select">
                                                            <option value="">Select</option>
                                                            @foreach ($inventoryItems as $item)
                                                                <option value="{{ $item->id }}" data-unit="{{ $item->unit }}" data-cost="{{ $item->cost_price }}" data-category="{{ $item->category }}" data-sku="{{ $item->sku }}" data-inventory-category-id="{{ $item->inventory_category_id }}" @selected((string) ($purchase['inventory_item_id'] ?? ($purchase['inventory_item']['id'] ?? null)) === (string) $item->id)>{{ $item->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error("purchases.$index.inventory_item_id")
                                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                                        @enderror
                                                    </div>
                                                    <div class="sm:col-span-4">
                                                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400">New Item Name</label>
                                                        <input name="purchases[{{ $index }}][name]" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-base h-12 px-4 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 inventory-item-name" value="{{ $purchase['name'] ?? ($purchase['inventory_item']['name'] ?? '') }}">
                                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Use only if item is new.</p>
                                                        @error("purchases.$index.name")
                                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                                        @enderror
                                                    </div>
                                                    <div class="sm:col-span-3">
                                                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400">SKU</label>
                                                        <input name="purchases[{{ $index }}][sku]" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-base h-12 px-4 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 inventory-item-sku" value="{{ $purchase['sku'] ?? ($purchase['inventory_item']['sku'] ?? '') }}">
                                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Required for new items.</p>
                                                        @error("purchases.$index.sku")
                                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                                        @enderror
                                                    </div>
                                                    <div class="sm:col-span-4">
                                                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Category</label>
                                                        <select name="purchases[{{ $index }}][inventory_category_id]" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-base h-12 px-4 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 inventory-item-category">
                                                            <option value="">Select</option>
                                                            @foreach ($inventoryCategories as $invCategory)
                                                                <option value="{{ $invCategory->id }}" @selected((string) ($purchase['inventory_category_id'] ?? ($purchase['inventory_item']['inventory_category_id'] ?? '')) === (string) $invCategory->id)>{{ $invCategory->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error("purchases.$index.inventory_category_id")
                                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                                        @enderror
                                                    </div>
                                                    <div class="sm:col-span-2">
                                                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Unit</label>
                                                        <input name="purchases[{{ $index }}][unit]" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-base h-12 px-4 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="{{ $purchase['unit'] ?? ($purchase['inventory_item']['unit'] ?? '') }}">
                                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Auto-filled for existing items.</p>
                                                        @error("purchases.$index.unit")
                                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                                        @enderror
                                                    </div>
                                                    <div class="sm:col-span-1">
                                                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Qty</label>
                                                        <input name="purchases[{{ $index }}][quantity]" type="number" step="0.01" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-base h-12 px-4 text-right focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="{{ $purchase['quantity'] ?? '' }}">
                                                        @error("purchases.$index.quantity")
                                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                                        @enderror
                                                    </div>
                                                    <div class="sm:col-span-2">
                                                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Unit Cost</label>
                                                        <input name="purchases[{{ $index }}][unit_cost]" type="number" step="0.01" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-base h-12 px-4 text-right focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="{{ $purchase['unit_cost'] ?? '' }}">
                                                        @error("purchases.$index.unit_cost")
                                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                                        @enderror
                                                    </div>
                                                    <div class="sm:col-span-2">
                                                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Line Total</label>
                                                        <input type="text" readonly class="mt-1 w-full rounded-lg border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-base h-12 px-4 text-right line-total" value="0.00">
                                                    </div>
                                                    <div class="sm:col-span-1 flex items-end justify-end">
                                                        <button type="button" class="text-red-600 hover:text-red-700 remove-row">Remove</button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3 flex items-center gap-3">
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="checkbox" id="auto-calc-amount" checked>
                                    Auto-calculate amount from items
                                </label>
                                <span class="text-xs text-gray-500">You can adjust amount for shipping/duty.</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('expenses.index') }}" class="text-gray-600 hover:underline">Cancel</a>
                            <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Update Expense</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('category-search');
    const select = document.getElementById('category-select');
    const typeRadios = document.querySelectorAll('input[name="category_type_filter"]');
    const vendorSelect = document.getElementById('vendor-select');
    const businessUnitSelect = document.getElementById('business-unit');
    const businessUnitSelect = document.getElementById('business-unit');
    const openVendorModal = document.getElementById('open-vendor-modal');
    const vendorModal = document.getElementById('vendor-modal');
    const vendorModalClose = document.getElementById('vendor-modal-close');
    const vendorModalCloseBtn = document.getElementById('vendor-modal-close-btn');
    const vendorForm = document.getElementById('vendor-form');
    const vendorError = document.getElementById('vendor-error');
    const amountInput = document.getElementById('expense-amount');
    const cogsSection = document.getElementById('cogs-section');
    const purchaseRows = document.getElementById('purchase-rows');
    const addPurchaseRow = document.getElementById('add-purchase-row');
    const autoCalcAmount = document.getElementById('auto-calc-amount');

    if (!searchInput || !select) return;

    const updateCogsVisibility = () => {
        const selected = select.options[select.selectedIndex];
        const type = selected?.dataset?.type || 'operating';
        if (type === 'cogs') {
            cogsSection.classList.remove('hidden');
        } else {
            cogsSection.classList.add('hidden');
        }
    };

    const applyFilters = () => {
        const query = searchInput.value.toLowerCase();
        const selectedType = Array.from(typeRadios).find((radio) => radio.checked)?.value ?? 'all';

        let firstVisible = null;
        Array.from(select.options).forEach((opt) => {
            const text = opt.text.toLowerCase();
            const optType = opt.dataset.type || 'operating';
            const matchesText = !query || text.includes(query);
            const matchesType = selectedType === 'all' || optType === selectedType;
            const isVisible = matchesText && matchesType;
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
        updateCogsVisibility();
    };

    searchInput.addEventListener('input', applyFilters);
    typeRadios.forEach((radio) => radio.addEventListener('change', applyFilters));
    applyFilters();

    select.addEventListener('change', updateCogsVisibility);
    updateCogsVisibility();

    const filterInventoryOptions = (inventorySelect) => {
        if (!inventorySelect) return;
        const unit = businessUnitSelect?.value || '';
        let firstVisible = null;
        Array.from(inventorySelect.options).forEach((opt) => {
            if (!opt.value) {
                opt.hidden = false;
                return;
            }
            const itemUnit = opt.dataset.category || '';
            const isVisible = !unit || unit === 'shared' || itemUnit === unit;
            opt.hidden = !isVisible;
            if (isVisible && !firstVisible) firstVisible = opt;
        });
        if (inventorySelect.selectedOptions.length && inventorySelect.selectedOptions[0].hidden) {
            inventorySelect.value = firstVisible?.value || '';
        }
    };

    const refreshInventoryFilters = () => {
        purchaseRows.querySelectorAll('select[name$="[inventory_item_id]"]').forEach((inventorySelect) => {
            filterInventoryOptions(inventorySelect);
        });
    };

    businessUnitSelect?.addEventListener('change', refreshInventoryFilters);

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
            const lineTotal = qty * unitCost;
            setLineTotal(row, lineTotal.toFixed(2));
            total += lineTotal;
        });
        if (autoCalcAmount && autoCalcAmount.checked && amountInput) {
            amountInput.value = total.toFixed(2);
        }
    };

    const wireRow = (row) => {
        const selectItem = row.querySelector('.inventory-item-select');
        const nameInput = row.querySelector('.inventory-item-name');
        const skuInput = row.querySelector('.inventory-item-sku');
        const categorySelect = row.querySelector('.inventory-item-category');
        const unitInput = row.querySelector('[name$="[unit]"]');
        const costInput = row.querySelector('[name$="[unit_cost]"]');
        filterInventoryOptions(selectItem);
        selectItem?.addEventListener('change', () => {
            const opt = selectItem.options[selectItem.selectedIndex];
            if (opt?.dataset?.unit) unitInput.value = opt.dataset.unit;
            if (opt?.dataset?.cost) costInput.value = opt.dataset.cost;
            if (opt?.dataset?.sku) skuInput.value = opt.dataset.sku;
            if (opt?.dataset?.inventoryCategoryId) categorySelect.value = opt.dataset.inventoryCategoryId;
            if (selectItem.value) {
                nameInput.value = '';
                nameInput.disabled = true;
                skuInput.disabled = true;
                categorySelect.disabled = true;
            } else {
                nameInput.disabled = false;
                skuInput.disabled = false;
                categorySelect.disabled = false;
            }
            calcTotals();
        });
        nameInput?.addEventListener('input', () => {
            if (nameInput.value.trim()) {
                selectItem.value = '';
                selectItem.disabled = true;
                skuInput.disabled = false;
                categorySelect.disabled = false;
            } else {
                selectItem.disabled = false;
            }
        });
        row.querySelectorAll('input').forEach((input) => input.addEventListener('input', calcTotals));
        row.querySelector('.remove-row')?.addEventListener('click', () => {
            row.remove();
            calcTotals();
        });
        if (selectItem?.value) {
            nameInput.disabled = true;
            skuInput.disabled = true;
            categorySelect.disabled = true;
        }
        if (nameInput?.value.trim()) {
            selectItem.disabled = true;
            skuInput.disabled = false;
            categorySelect.disabled = false;
        }
    };

    const addRow = () => {
        const index = purchaseRows.children.length;
        const row = document.createElement('div');
        row.className = 'purchase-row rounded-lg border border-gray-200 dark:border-gray-700 p-4 bg-white dark:bg-gray-900';
        row.innerHTML = `
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                <div class="sm:col-span-5">
                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Inventory Item</label>
                    <select name="purchases[${index}][inventory_item_id]" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-base h-12 px-4 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 inventory-item-select">
                        <option value="">Select</option>
                        @foreach ($inventoryItems as $item)
                            <option value="{{ $item->id }}" data-unit="{{ $item->unit }}" data-cost="{{ $item->cost_price }}" data-category="{{ $item->category }}" data-sku="{{ $item->sku }}" data-inventory-category-id="{{ $item->inventory_category_id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-4">
                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400">New Item Name</label>
                    <input name="purchases[${index}][name]" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-base h-12 px-4 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 inventory-item-name" placeholder="New item name">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Use only if item is new.</p>
                </div>
                <div class="sm:col-span-3">
                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400">SKU</label>
                    <input name="purchases[${index}][sku]" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-base h-12 px-4 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 inventory-item-sku" placeholder="SKU">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Required for new items.</p>
                </div>
                <div class="sm:col-span-4">
                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Category</label>
                    <select name="purchases[${index}][inventory_category_id]" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-base h-12 px-4 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 inventory-item-category">
                        <option value="">Select</option>
                        @foreach ($inventoryCategories as $invCategory)
                            <option value="{{ $invCategory->id }}">{{ $invCategory->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Unit</label>
                    <input name="purchases[${index}][unit]" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-base h-12 px-4 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="pcs">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Auto-filled for existing items.</p>
                </div>
                <div class="sm:col-span-1">
                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Qty</label>
                    <input name="purchases[${index}][quantity]" type="number" step="0.01" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-base h-12 px-4 text-right focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="sm:col-span-2">
                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Unit Cost</label>
                    <input name="purchases[${index}][unit_cost]" type="number" step="0.01" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-base h-12 px-4 text-right focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="sm:col-span-2">
                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Line Total</label>
                    <input type="text" readonly class="mt-1 w-full rounded-lg border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-base h-12 px-4 text-right line-total" value="0.00">
                </div>
                <div class="sm:col-span-1 flex items-end justify-end">
                    <button type="button" class="text-red-600 hover:text-red-700 remove-row">Remove</button>
                </div>
            </div>
        `;
        purchaseRows.appendChild(row);
        wireRow(row);
        calcTotals();
    };

    purchaseRows.querySelectorAll('.purchase-row').forEach(wireRow);
    addPurchaseRow?.addEventListener('click', addRow);
    refreshInventoryFilters();
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
