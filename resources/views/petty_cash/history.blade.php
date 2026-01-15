<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Petty Cash - Full Transaction History
            </h2>
            <a href="{{ route('petty-cash.index') }}"
               class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                ← Back to Petty Cash
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-4 lg:px-8">
            {{-- Balance card --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6 mb-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            My Petty Cash Balance
                        </h3>
                        <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-1">
                            {{ number_format($balance, 2) }} MVR
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ Auth::user()->name }}</p>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium
                            {{ $balance >= 0 ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                            {{ $balance >= 0 ? 'Available' : 'Overdrawn' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Full Transaction History --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4">
                    All Transactions ({{ $pagination->total() }} total)
                </h3>

                @if($pagination->count() > 0)
                    <div class="space-y-1.5 bg-gray-50 dark:bg-gray-900/50 rounded-lg p-2">
                        @foreach($pagination as $ledgerItem)
                            @php
                                $entry = $ledgerItem['entry'];
                                $balanceAfter = $ledgerItem['balance_after'];
                                $isTopup = $entry->type === 'topup';
                            @endphp
                            <div class="flex items-start justify-between py-2.5 px-3 rounded-md bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-700 transition-colors">
                                <div class="flex-1 min-w-0 pr-3">
                                    <div class="flex items-baseline gap-2 flex-wrap">
                                        <span class="text-lg font-bold {{ $isTopup ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                            {{ $isTopup ? '+' : '−' }}{{ number_format($entry->amount, 2) }}
                                        </span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $entry->paid_at?->format('M d, Y H:i') ?? $entry->created_at->format('M d, Y H:i') }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                        {{ $entry->purpose }}
                                        @if($entry->category)
                                            <span class="text-gray-400"> • {{ ucfirst($entry->category) }}</span>
                                        @endif
                                    </div>
                                    @if($entry->user)
                                        <div class="text-[10px] text-gray-400 mt-0.5">
                                            by {{ $entry->user->name }}
                                        </div>
                                    @endif
                                </div>
                                <div class="text-right flex-shrink-0 border-l border-gray-200 dark:border-gray-700 pl-3 ml-3">
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Balance</div>
                                    <div class="text-sm font-bold {{ $balanceAfter >= 0 ? 'text-gray-900 dark:text-gray-100' : 'text-red-600 dark:text-red-400' }}">
                                        {{ number_format($balanceAfter, 2) }} MVR
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">
                        No transaction history yet.
                    </p>
                @endif
            </div>

            {{-- Pagination --}}
            @if($pagination->hasPages())
                <div class="mt-4">
                    {{ $pagination->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
