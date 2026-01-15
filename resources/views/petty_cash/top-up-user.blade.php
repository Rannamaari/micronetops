<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Top Up Petty Cash - {{ $user->name }}
            </h2>
            <a href="{{ route('petty-cash.admin-dashboard') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-4 lg:px-8 space-y-6">
            {{-- User Info Card --}}
            <div class="bg-gradient-to-br from-indigo-500 to-purple-600 shadow-sm sm:rounded-lg p-6 text-white">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center text-2xl font-bold">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                    <div class="flex-1">
                        <h3 class="text-2xl font-bold">{{ $user->name }}</h3>
                        <p class="text-sm opacity-90">{{ ucfirst($user->role) }}</p>
                        <div class="mt-2">
                            <span class="text-xs opacity-75">Current Balance:</span>
                            <span class="text-2xl font-bold ml-2">{{ number_format($currentBalance, 2) }} MVR</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Top Up Form --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                    Add Funds to {{ $user->name }}'s Petty Cash
                </h3>

                <form method="POST" action="{{ route('petty-cash.top-up-user', $user) }}" class="space-y-6">
                    @csrf

                    {{-- Amount Field --}}
                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Amount (MVR) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 dark:text-gray-400 sm:text-sm">MVR</span>
                            </div>
                            <input type="number"
                                   name="amount"
                                   id="amount"
                                   step="0.01"
                                   min="0.01"
                                   required
                                   value="{{ old('amount') }}"
                                   class="block w-full pl-14 pr-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-lg font-semibold focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:focus:ring-indigo-400 dark:focus:border-indigo-400"
                                   placeholder="0.00">
                        </div>
                        @error('amount')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Purpose Field --}}
                    <div>
                        <label for="purpose" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Purpose / Notes <span class="text-red-500">*</span>
                        </label>
                        <textarea name="purpose"
                                  id="purpose"
                                  rows="3"
                                  required
                                  class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:focus:ring-indigo-400 dark:focus:border-indigo-400"
                                  placeholder="e.g., Monthly petty cash allocation, Reimbursement, etc.">{{ old('purpose') }}</textarea>
                        @error('purpose')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Quick Amount Buttons --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Quick Select
                        </label>
                        <div class="grid grid-cols-3 sm:grid-cols-5 gap-2">
                            @foreach([100, 200, 500, 1000, 2000] as $quickAmount)
                                <button type="button"
                                        onclick="document.getElementById('amount').value = {{ $quickAmount }}"
                                        class="px-3 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-indigo-100 dark:hover:bg-indigo-900 text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 text-sm font-medium rounded-lg transition">
                                    {{ number_format($quickAmount) }} MVR
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Summary Box --}}
                    <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Summary</h4>
                        <div class="space-y-1 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Current Balance:</span>
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ number_format($currentBalance, 2) }} MVR</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Top Up Amount:</span>
                                <span class="font-medium text-green-600 dark:text-green-400" id="topup-display">0.00 MVR</span>
                            </div>
                            <div class="border-t border-gray-300 dark:border-gray-600 my-2"></div>
                            <div class="flex justify-between text-base">
                                <span class="font-semibold text-gray-900 dark:text-gray-100">New Balance:</span>
                                <span class="font-bold text-indigo-600 dark:text-indigo-400" id="new-balance-display">{{ number_format($currentBalance, 2) }} MVR</span>
                            </div>
                        </div>
                    </div>

                    {{-- Submit Button --}}
                    <div class="flex gap-3">
                        <a href="{{ route('petty-cash.admin-dashboard') }}"
                           class="flex-1 sm:flex-initial px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition text-center">
                            Cancel
                        </a>
                        <button type="submit"
                                class="flex-1 sm:flex-auto px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition inline-flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Top Up Petty Cash
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Update summary when amount changes
        const amountInput = document.getElementById('amount');
        const topupDisplay = document.getElementById('topup-display');
        const newBalanceDisplay = document.getElementById('new-balance-display');
        const currentBalance = {{ $currentBalance }};

        amountInput.addEventListener('input', function() {
            const amount = parseFloat(this.value) || 0;
            topupDisplay.textContent = amount.toFixed(2) + ' MVR';
            newBalanceDisplay.textContent = (currentBalance + amount).toFixed(2) + ' MVR';
        });
    </script>
    @endpush
</x-app-layout>
