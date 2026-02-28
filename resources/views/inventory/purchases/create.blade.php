<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Record Purchase') }} - {{ $inventory->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('inventory.purchases.store', $inventory) }}" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium">Quantity</label>
                                <input type="number" name="quantity" min="1" class="mt-1 w-full rounded border-gray-300" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium">Unit Cost</label>
                                <input type="number" step="0.01" name="unit_cost" min="0" class="mt-1 w-full rounded border-gray-300" required>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium">Purchased At</label>
                                <input type="date" name="purchased_at" class="mt-1 w-full rounded border-gray-300" value="{{ now()->toDateString() }}" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium">Business Unit</label>
                                <select name="business_unit" class="mt-1 w-full rounded border-gray-300" required>
                                    @foreach ($businessUnits as $key => $label)
                                        <option value="{{ $key }}" @selected($inventory->category === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium">Vendor</label>
                                <input name="vendor" class="mt-1 w-full rounded border-gray-300">
                            </div>
                            <div>
                                <label class="block text-sm font-medium">Reference</label>
                                <input name="reference" class="mt-1 w-full rounded border-gray-300">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Notes</label>
                            <textarea name="notes" rows="3" class="mt-1 w-full rounded border-gray-300"></textarea>
                        </div>
                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('inventory.show', $inventory) }}" class="text-gray-600 hover:underline">Cancel</a>
                            <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save Purchase</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
