<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Add Recurring Expense') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('recurring-expenses.store') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium">Name</label>
                            <input name="name" class="mt-1 w-full rounded border-gray-300" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Category</label>
                            <select name="expense_category_id" class="mt-1 w-full rounded border-gray-300" required>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }} ({{ $category->type }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Business Unit</label>
                            <select name="business_unit" class="mt-1 w-full rounded border-gray-300" required>
                                @foreach ($businessUnits as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium">Amount</label>
                                <input type="number" step="0.01" name="amount" class="mt-1 w-full rounded border-gray-300" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium">Frequency</label>
                                <select name="frequency" class="mt-1 w-full rounded border-gray-300" required>
                                    @foreach ($frequencies as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium">Next Due Date</label>
                                <input type="date" name="next_due_at" class="mt-1 w-full rounded border-gray-300" value="{{ now()->toDateString() }}" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium">Day of Month (optional)</label>
                                <input type="number" min="1" max="31" name="day_of_month" class="mt-1 w-full rounded border-gray-300" placeholder="e.g. 1">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium">Vendor Name</label>
                                <input name="vendor_name" class="mt-1 w-full rounded border-gray-300" list="vendor-name-list" placeholder="Start typing vendor name">
                                <datalist id="vendor-name-list">
                                    @foreach ($vendors as $vendor)
                                        <option value="{{ $vendor->name }}"></option>
                                    @endforeach
                                </datalist>
                            </div>
                            <div>
                                <label class="block text-sm font-medium">Vendor Phone</label>
                                <input name="vendor_phone" class="mt-1 w-full rounded border-gray-300" list="vendor-phone-list">
                                <datalist id="vendor-phone-list">
                                    @foreach ($vendors as $vendor)
                                        <option value="{{ $vendor->phone }}"></option>
                                    @endforeach
                                </datalist>
                            </div>
                            <div>
                                <label class="block text-sm font-medium">Contact Name</label>
                                <input name="vendor_contact_name" class="mt-1 w-full rounded border-gray-300">
                            </div>
                            <div>
                                <label class="block text-sm font-medium">Address</label>
                                <input name="vendor_address" class="mt-1 w-full rounded border-gray-300">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Reference</label>
                            <input name="reference" class="mt-1 w-full rounded border-gray-300">
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Notes</label>
                            <textarea name="notes" rows="3" class="mt-1 w-full rounded border-gray-300"></textarea>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="is_active" value="1" checked>
                            <label class="text-sm">Active</label>
                        </div>
                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('recurring-expenses.index') }}" class="text-gray-600 hover:underline">Cancel</a>
                            <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
