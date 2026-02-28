<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Add Operating Expense') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
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
                                <label class="block text-sm font-medium">Operating/Other Category</label>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('category-search');
    const select = document.getElementById('category-select');
    const vendorSelect = document.getElementById('vendor-select');
    const openVendorModal = document.getElementById('open-vendor-modal');
    const vendorModal = document.getElementById('vendor-modal');
    const vendorModalClose = document.getElementById('vendor-modal-close');
    const vendorModalCloseBtn = document.getElementById('vendor-modal-close-btn');
    const vendorForm = document.getElementById('vendor-form');
    const vendorError = document.getElementById('vendor-error');

    if (!searchInput || !select) return;

    const applyFilters = () => {
        const query = searchInput.value.toLowerCase();
        let firstVisible = null;
        Array.from(select.options).forEach((opt) => {
            const text = opt.text.toLowerCase();
            const matchesText = !query || text.includes(query);
            opt.hidden = !matchesText;
            if (matchesText && !firstVisible) {
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
