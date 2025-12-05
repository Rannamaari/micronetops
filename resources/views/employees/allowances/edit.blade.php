<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('employees.allowances.index', $employee) }}" class="inline-flex items-center justify-center w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-full hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Edit Allowance for</div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ $employee->name }}
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-4 lg:px-8">
            @if ($errors->any())
                <div class="mb-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md p-3">
                    <ul class="list-disc list-inside text-sm text-red-600 dark:text-red-400">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('employees.allowances.update', [$employee, $allowance]) }}">
                @csrf
                @method('PATCH')

                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 space-y-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Allowance Details</h3>

                    {{-- Allowance Type --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Allowance Type <span class="text-red-500">*</span></label>
                        <select name="allowance_type" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                            <option value="">Select type...</option>
                            <option value="food" {{ old('allowance_type', $allowance->allowance_type) === 'food' ? 'selected' : '' }}>Food Allowance</option>
                            <option value="mobile" {{ old('allowance_type', $allowance->allowance_type) === 'mobile' ? 'selected' : '' }}>Mobile Allowance</option>
                            <option value="travel" {{ old('allowance_type', $allowance->allowance_type) === 'travel' ? 'selected' : '' }}>Travel Allowance</option>
                            <option value="housing" {{ old('allowance_type', $allowance->allowance_type) === 'housing' ? 'selected' : '' }}>Housing Allowance</option>
                            <option value="transport" {{ old('allowance_type', $allowance->allowance_type) === 'transport' ? 'selected' : '' }}>Transport Allowance</option>
                            <option value="medical" {{ old('allowance_type', $allowance->allowance_type) === 'medical' ? 'selected' : '' }}>Medical Allowance</option>
                            <option value="other" {{ old('allowance_type', $allowance->allowance_type) === 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    {{-- Amount --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Amount (MVR) <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" name="amount" value="{{ old('amount', $allowance->amount) }}" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                    </div>

                    {{-- Frequency --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Frequency <span class="text-red-500">*</span></label>
                        <select name="frequency" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                            <option value="monthly" {{ old('frequency', $allowance->frequency) === 'monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="quarterly" {{ old('frequency', $allowance->frequency) === 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                            <option value="yearly" {{ old('frequency', $allowance->frequency) === 'yearly' ? 'selected' : '' }}>Yearly</option>
                            <option value="one-time" {{ old('frequency', $allowance->frequency) === 'one-time' ? 'selected' : '' }}>One Time</option>
                        </select>
                    </div>

                    {{-- Start Date --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Date <span class="text-red-500">*</span></label>
                        <input type="date" name="start_date" value="{{ old('start_date', $allowance->start_date->format('Y-m-d')) }}" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                    </div>

                    {{-- End Date --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">End Date (Optional)</label>
                        <input type="date" name="end_date" value="{{ old('end_date', $allowance->end_date ? $allowance->end_date->format('Y-m-d') : '') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                    </div>

                    {{-- Description --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                        <input type="text" name="description" value="{{ old('description', $allowance->description) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                    </div>

                    {{-- Notes --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Internal Notes</label>
                        <textarea name="notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">{{ old('notes', $allowance->notes) }}</textarea>
                    </div>

                    {{-- Is Active --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status <span class="text-red-500">*</span></label>
                        <select name="is_active" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                            <option value="1" {{ old('is_active', $allowance->is_active) == 1 ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('is_active', $allowance->is_active) == 0 ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    {{-- Actions --}}
                    <div class="flex justify-end gap-3">
                        <a href="{{ route('employees.allowances.index', $employee) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-600">
                            Cancel
                        </a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                            Update Allowance
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
