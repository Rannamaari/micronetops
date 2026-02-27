<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Sales
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

            {{-- Date Picker + Filter + New Sale Buttons --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row items-start sm:items-end gap-4">
                    <form method="GET" action="{{ route('sales.daily.index') }}" class="flex flex-wrap items-end gap-3">
                        <div>
                            <label for="date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date</label>
                            <input type="date" name="date" id="date" value="{{ $date }}"
                                   class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label for="business_unit" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Unit</label>
                            <select name="business_unit" id="business_unit"
                                    class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                <option value="">All</option>
                                <option value="moto" {{ ($businessUnit ?? '') === 'moto' ? 'selected' : '' }}>Micro Moto</option>
                                <option value="cool" {{ ($businessUnit ?? '') === 'cool' ? 'selected' : '' }}>Micro Cool</option>
                            </select>
                        </div>
                        <button type="submit"
                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                            View
                        </button>
                    </form>

                    <div class="flex items-center gap-3 sm:ml-auto">
                        @if(!Auth::user()->allowedBusinessUnit() || Auth::user()->allowedBusinessUnit() === 'moto')
                            <form method="POST" action="{{ route('sales.daily.open') }}">
                                @csrf
                                <input type="hidden" name="date" value="{{ $date }}">
                                <input type="hidden" name="business_unit" value="moto">
                                <button type="submit"
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    New Sale (Micro Moto)
                                </button>
                            </form>
                        @endif
                        @if(!Auth::user()->allowedBusinessUnit() || Auth::user()->allowedBusinessUnit() === 'cool')
                            <form method="POST" action="{{ route('sales.daily.open') }}">
                                @csrf
                                <input type="hidden" name="date" value="{{ $date }}">
                                <input type="hidden" name="business_unit" value="cool">
                                <button type="submit"
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    New Sale (Micro Cool)
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Sales List --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                <div class="p-4 sm:p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">
                        Sales for {{ \Carbon\Carbon::parse($date)->format('D, d M Y') }} ({{ $logs->count() }})
                    </h3>
                </div>

                @if($logs->isEmpty())
                    <div class="p-6 text-center text-gray-500 dark:text-gray-400 text-sm">
                        No sales for this date. Click "New Sale" to get started.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Bill #</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Unit</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Customer</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Lines</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($logs as $log)
                                    @php $totals = $log->totals; @endphp
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                                            #{{ $log->id }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                                                {{ $log->business_unit === 'moto' ? 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200' : 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900 dark:text-cyan-200' }}">
                                                {{ $log->business_unit === 'moto' ? 'Micro Moto' : 'Micro Cool' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                            @if($log->customer)
                                                {{ $log->customer->name }}
                                            @else
                                                <span class="text-gray-400 dark:text-gray-500">Walk-in</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center text-sm text-gray-700 dark:text-gray-300">
                                            {{ $log->lines->count() }}
                                        </td>
                                        <td class="px-4 py-3 text-right text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ number_format($totals['grand'], 2) }} MVR
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <div class="flex flex-col items-center gap-1">
                                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    {{ $log->status === 'submitted' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }}">
                                                    {{ ucfirst($log->status) }}
                                                </span>
                                                @if($log->isSubmitted() && $log->payment_method)
                                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                                                        {{ $log->payment_method === 'cash' ? 'bg-green-50 text-green-700 dark:bg-green-900/50 dark:text-green-300' : 'bg-blue-50 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300' }}">
                                                        {{ ucfirst($log->payment_method) }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                <a href="{{ route('sales.daily.show', $log) }}"
                                                   class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-lg transition
                                                       {{ $log->isSubmitted() ? 'bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300' : 'bg-indigo-100 hover:bg-indigo-200 dark:bg-indigo-900/40 dark:hover:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300' }}">
                                                    {{ $log->isSubmitted() ? 'View' : 'Continue' }}
                                                </a>
                                                @if(!$log->isSubmitted() && Auth::user()->canDeleteSales())
                                                    <form method="POST" action="{{ route('sales.daily.destroy', $log) }}"
                                                          onsubmit="return confirm('Delete draft sale #{{ $log->id }}? This cannot be undone.')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium rounded-lg transition bg-red-100 hover:bg-red-200 dark:bg-red-900/40 dark:hover:bg-red-900/60 text-red-700 dark:text-red-300">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                            Delete
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Day Totals --}}
                    @php
                        $dayGrand = $logs->sum(fn($l) => $l->totals['grand']);
                        $dayCash = $logs->where('payment_method', 'cash')->sum(fn($l) => $l->totals['grand']);
                        $dayTransfer = $logs->where('payment_method', 'transfer')->sum(fn($l) => $l->totals['grand']);
                    @endphp
                    <div class="p-4 sm:p-6 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30">
                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div>
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Cash</p>
                                <p class="text-lg font-bold text-green-600 dark:text-green-400 mt-1">{{ number_format($dayCash, 2) }} MVR</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Transfer</p>
                                <p class="text-lg font-bold text-blue-600 dark:text-blue-400 mt-1">{{ number_format($dayTransfer, 2) }} MVR</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Day Total</p>
                                <p class="text-xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ number_format($dayGrand, 2) }} MVR</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
