<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Daily Sales Log
            </h2>
            <a href="{{ route('sales.reports') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                Sales Reports
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-4 lg:px-8 space-y-6">
            {{-- Flash messages --}}
            @if(session('success'))
                <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 px-4 py-3 rounded-lg text-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-3 rounded-lg text-sm">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Date Picker --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6">
                <form method="GET" action="{{ route('sales.daily.index') }}" class="flex flex-col sm:flex-row items-start sm:items-end gap-4">
                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Select Date</label>
                        <input type="date" name="date" id="date" value="{{ $date }}"
                               class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <button type="submit"
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                        View Logs
                    </button>
                </form>
            </div>

            {{-- Business Unit Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach(['moto' => 'Micro Moto', 'cool' => 'Micro Cool'] as $unit => $label)
                    @php
                        $log = $logs->firstWhere('business_unit', $unit);
                    @endphp
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $label }}</h3>
                            @if($log)
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $log->status === 'submitted' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }}">
                                    {{ ucfirst($log->status) }}
                                </span>
                            @endif
                        </div>

                        @if($log)
                            @php $totals = $log->totals; @endphp
                            <div class="space-y-2 mb-4">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500 dark:text-gray-400">Lines</span>
                                    <span class="text-gray-900 dark:text-gray-100 font-medium">{{ $log->lines->count() }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500 dark:text-gray-400">Cash</span>
                                    <span class="text-gray-900 dark:text-gray-100 font-medium">{{ number_format($totals['cash'], 2) }} MVR</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500 dark:text-gray-400">Transfer</span>
                                    <span class="text-gray-900 dark:text-gray-100 font-medium">{{ number_format($totals['transfer'], 2) }} MVR</span>
                                </div>
                                <div class="flex justify-between text-sm font-semibold border-t border-gray-200 dark:border-gray-700 pt-2">
                                    <span class="text-gray-700 dark:text-gray-300">Total</span>
                                    <span class="text-gray-900 dark:text-gray-100">{{ number_format($totals['grand'], 2) }} MVR</span>
                                </div>
                            </div>
                            @if($log->createdByUser)
                                <p class="text-xs text-gray-400 dark:text-gray-500 mb-4">Created by {{ $log->createdByUser->name }}</p>
                            @endif
                            <a href="{{ route('sales.daily.show', $log) }}"
                               class="block w-full text-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                                {{ $log->isSubmitted() ? 'View Log' : 'Continue Entry' }}
                            </a>
                        @else
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">No log for this date yet.</p>
                            <form method="POST" action="{{ route('sales.daily.open') }}">
                                @csrf
                                <input type="hidden" name="date" value="{{ $date }}">
                                <input type="hidden" name="business_unit" value="{{ $unit }}">
                                <button type="submit"
                                        class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition">
                                    Start Log
                                </button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
