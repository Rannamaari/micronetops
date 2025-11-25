<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Edit Inventory Item
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-4 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6">
                @if ($errors->any())
                    <div class="mb-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md p-3">
                        <ul class="list-disc list-inside text-sm text-red-600 dark:text-red-400">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('inventory.update', $inventoryItem) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" value="{{ old('name', $inventoryItem->name) }}" required
                                   class="block w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('name')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                SKU
                            </label>
                            <input type="text" name="sku" value="{{ old('sku', $inventoryItem->sku) }}"
                                   class="block w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('sku')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Brand
                            </label>
                            <input type="text" name="brand" value="{{ old('brand', $inventoryItem->brand) }}"
                                   class="block w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Unit <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="unit" value="{{ old('unit', $inventoryItem->unit) }}" required
                                   class="block w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Category Type <span class="text-red-500">*</span>
                            </label>
                            <select name="category" required
                                    class="block w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="moto" {{ old('category', $inventoryItem->category) === 'moto' ? 'selected' : '' }}>Moto</option>
                                <option value="ac" {{ old('category', $inventoryItem->category) === 'ac' ? 'selected' : '' }}>AC</option>
                                <option value="both" {{ old('category', $inventoryItem->category) === 'both' ? 'selected' : '' }}>Both</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Inventory Category
                            </label>
                            <select name="inventory_category_id"
                                    class="block w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">None</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('inventory_category_id', $inventoryItem->inventory_category_id) == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }} ({{ $cat->type }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Cost Price (MVR) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" step="0.01" name="cost_price" id="cost_price" value="{{ old('cost_price', $inventoryItem->cost_price) }}" required min="0"
                                   class="block w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <div id="gst-info" class="mt-2 text-xs text-gray-600 dark:text-gray-400 hidden">
                                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded p-2">
                                    <div class="flex justify-between">
                                        <span>Cost Price:</span>
                                        <span id="display-cost-price">MVR 0.00</span>
                                    </div>
                                    <div class="flex justify-between font-medium text-blue-600 dark:text-blue-400">
                                        <span>GST (8%):</span>
                                        <span id="display-gst">MVR 0.00</span>
                                    </div>
                                    <div class="flex justify-between font-bold border-t border-blue-200 dark:border-blue-700 pt-1 mt-1">
                                        <span>Total with GST:</span>
                                        <span id="display-total">MVR 0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Sell Price (MVR) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" step="0.01" name="sell_price" value="{{ old('sell_price', $inventoryItem->sell_price) }}" required min="0"
                                   class="block w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div id="quantity-field">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Current Quantity
                            </label>
                            <input type="number" name="quantity" value="{{ old('quantity', $inventoryItem->quantity) }}" min="0"
                                   class="block w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Use stock adjustment on detail page to change quantity
                            </p>
                        </div>

                        <div id="low-stock-field">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Low Stock Limit
                            </label>
                            <input type="number" name="low_stock_limit" value="{{ old('low_stock_limit', $inventoryItem->low_stock_limit) }}" min="0"
                                   class="block w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div class="md:col-span-2">
                            <input type="hidden" name="is_service" value="0">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_service" value="1" {{ old('is_service', $inventoryItem->is_service) ? 'checked' : '' }}
                                       id="is_service"
                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">This is a service item (no stock tracking)</span>
                            </label>
                            @error('is_service')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <input type="hidden" name="has_gst" value="0">
                            <label class="flex items-center">
                                <input type="checkbox" name="has_gst" value="1" {{ old('has_gst', $inventoryItem->has_gst) ? 'checked' : '' }}
                                       id="has_gst"
                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Include 8% GST in cost price</span>
                            </label>
                            <p class="ml-6 mt-1 text-xs text-gray-500 dark:text-gray-400">Check this if the item purchase requires GST calculation</p>
                            @error('has_gst')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <input type="hidden" name="is_active" value="0">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $inventoryItem->is_active) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Active</span>
                            </label>
                            @error('is_active')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('inventory.index') }}"
                           class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none">
                            Cancel
                        </a>
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none">
                            Update Item
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Handle service checkbox
        document.getElementById('is_service').addEventListener('change', function() {
            const quantityField = document.getElementById('quantity-field');
            const lowStockField = document.getElementById('low-stock-field');
            if (this.checked) {
                quantityField.style.display = 'none';
                lowStockField.style.display = 'none';
            } else {
                quantityField.style.display = 'block';
                lowStockField.style.display = 'block';
            }
        });
        // Trigger on page load
        document.getElementById('is_service').dispatchEvent(new Event('change'));

        // Handle GST calculations
        const hasGstCheckbox = document.getElementById('has_gst');
        const costPriceInput = document.getElementById('cost_price');
        const gstInfo = document.getElementById('gst-info');
        const displayCostPrice = document.getElementById('display-cost-price');
        const displayGst = document.getElementById('display-gst');
        const displayTotal = document.getElementById('display-total');

        function calculateGst() {
            const hasGst = hasGstCheckbox.checked;
            const costPrice = parseFloat(costPriceInput.value) || 0;

            if (hasGst && costPrice > 0) {
                const gstAmount = costPrice * 0.08;
                const totalWithGst = costPrice + gstAmount;

                displayCostPrice.textContent = 'MVR ' + costPrice.toFixed(2);
                displayGst.textContent = 'MVR ' + gstAmount.toFixed(2);
                displayTotal.textContent = 'MVR ' + totalWithGst.toFixed(2);
                gstInfo.classList.remove('hidden');
            } else {
                gstInfo.classList.add('hidden');
            }
        }

        hasGstCheckbox.addEventListener('change', calculateGst);
        costPriceInput.addEventListener('input', calculateGst);

        // Trigger on page load
        calculateGst();
    </script>
</x-app-layout>

