<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Add Allowance for {{ $employee->name }}</h2>
    </x-slot>
    <div class="py-6 max-w-3xl mx-auto">
        <form method="POST" action="{{ route('employees.allowances.store', $employee) }}" class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 space-y-4">
            @csrf
            <div><label class="block text-sm font-medium mb-1">Type *</label>
                <select name="allowance_type" required class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900">
                    <option value="food">Food</option><option value="mobile">Mobile</option><option value="travel">Travel</option>
                    <option value="housing">Housing</option><option value="transport">Transport</option><option value="medical">Medical</option>
                </select>
            </div>
            <div><label class="block text-sm font-medium mb-1">Amount (MVR) *</label>
                <input type="number" step="0.01" name="amount" required class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900">
            </div>
            <div><label class="block text-sm font-medium mb-1">Frequency *</label>
                <select name="frequency" required class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900">
                    <option value="monthly" selected>Monthly</option><option value="quarterly">Quarterly</option><option value="yearly">Yearly</option>
                </select>
            </div>
            <div><label class="block text-sm font-medium mb-1">Start Date *</label>
                <input type="date" name="start_date" value="{{ date('Y-m-d') }}" required class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900">
            </div>
            <div class="flex gap-3 justify-end">
                <a href="{{ route('employees.show', $employee) }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Add Allowance</button>
            </div>
        </form>
    </div>
</x-app-layout>
