<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Create Inventory Item
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-4 lg:px-8">
            {{-- Success message --}}
            @if (session('success'))
                <div class="mb-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-md p-3">
                    <p class="text-sm text-green-600 dark:text-green-400">{{ session('success') }}</p>
                </div>
            @endif

            {{-- Keyboard shortcuts info --}}
            <div class="mb-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-md p-3">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="text-sm text-blue-700 dark:text-blue-300">
                        <strong>Keyboard Shortcuts:</strong>
                        <span class="ml-2">Ctrl+S (Save)</span>
                        <span class="ml-2">•</span>
                        <span class="ml-2">Ctrl+Shift+S (Save & Add Another)</span>
                        <span class="ml-2">•</span>
                        <span class="ml-2">Esc (Cancel)</span>
                    </div>
                </div>
            </div>

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

                <form method="POST" action="{{ route('inventory.store') }}" class="space-y-6" id="inventory-form">
                    @csrf
                    <input type="hidden" name="add_another" id="add_another" value="0">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" id="name-field" value="{{ old('name') }}" required autofocus
                                   class="block w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('name')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                SKU
                            </label>
                            <input type="text" name="sku" value="{{ old('sku') }}"
                                   class="block w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('sku')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Brand
                            </label>
                            <input type="text" name="brand" value="{{ old('brand') }}"
                                   class="block w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Unit <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="unit" value="{{ old('unit', 'pcs') }}" required
                                   placeholder="pcs, ltr, kg, etc."
                                   class="block w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Category Type <span class="text-red-500">*</span>
                            </label>
                            <select name="category" required
                                    class="block w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="moto" {{ old('category') === 'moto' ? 'selected' : '' }}>Moto</option>
                                <option value="ac" {{ old('category') === 'ac' ? 'selected' : '' }}>AC</option>
                                <option value="both" {{ old('category') === 'both' ? 'selected' : '' }}>Both</option>
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
                                    <option value="{{ $cat->id }}" {{ old('inventory_category_id') == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }} ({{ $cat->type }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Cost Price (MVR) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" step="0.01" name="cost_price" id="cost_price" value="{{ old('cost_price', 0) }}" required min="0"
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
                            <input type="number" step="0.01" name="sell_price" value="{{ old('sell_price', 0) }}" required min="0"
                                   class="block w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div id="quantity-field">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Initial Quantity
                            </label>
                            <input type="number" name="quantity" value="{{ old('quantity', 0) }}" min="0"
                                   class="block w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div id="low-stock-field">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Low Stock Limit
                            </label>
                            <input type="number" name="low_stock_limit" value="{{ old('low_stock_limit', 0) }}" min="0"
                                   class="block w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div class="md:col-span-2">
                            <input type="hidden" name="is_service" value="0">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_service" value="1" {{ old('is_service') ? 'checked' : '' }}
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
                                <input type="checkbox" name="has_gst" value="1" {{ old('has_gst') ? 'checked' : '' }}
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
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Active</span>
                            </label>
                            @error('is_active')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row justify-between gap-3">
                        <a href="{{ route('inventory.index') }}" id="cancel-btn"
                           class="inline-flex items-center justify-center px-4 py-2 bg-gray-200 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-600 focus:outline-none">
                            Cancel <span class="ml-2 text-gray-500 dark:text-gray-400">(Esc)</span>
                        </a>
                        <div class="flex gap-3">
                            <button type="submit" id="save-btn"
                                    class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Save <span class="ml-2 opacity-75">(Ctrl+S)</span>
                            </button>
                            <button type="button" id="save-add-another-btn"
                                    class="inline-flex items-center justify-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Save & Add Another <span class="ml-2 opacity-75">(Ctrl+Shift+S)</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl+S or Cmd+S - Save
            if ((e.ctrlKey || e.metaKey) && e.key === 's' && !e.shiftKey) {
                e.preventDefault();
                document.getElementById('add_another').value = '0';
                document.getElementById('inventory-form').submit();
            }
            // Ctrl+Shift+S or Cmd+Shift+S - Save & Add Another
            else if ((e.ctrlKey || e.metaKey) && e.key === 'S' && e.shiftKey) {
                e.preventDefault();
                document.getElementById('add_another').value = '1';
                document.getElementById('inventory-form').submit();
            }
            // Escape - Cancel
            else if (e.key === 'Escape') {
                e.preventDefault();
                window.location.href = document.getElementById('cancel-btn').href;
            }
        });

        // Save & Add Another button click handler
        document.getElementById('save-add-another-btn').addEventListener('click', function() {
            document.getElementById('add_another').value = '1';
            document.getElementById('inventory-form').submit();
        });

        // Focus on name field on page load
        window.addEventListener('load', function() {
            document.getElementById('name-field').focus();
        });

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

