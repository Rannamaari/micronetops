<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Sales Reports
            </h2>
            <a href="{{ route('sales.daily.index') }}"
               class="inline-flex items-center gap-1 px-3 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                Back to Sales
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-4 lg:px-8 space-y-6">
            {{-- Filters --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6">
                <form method="GET" action="{{ route('sales.reports') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                    <div>
                        <label for="date_from" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">From</label>
                        <input type="date" name="date_from" id="date_from" value="{{ $dateFrom }}"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    </div>
                    <div>
                        <label for="date_to" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">To</label>
                        <input type="date" name="date_to" id="date_to" value="{{ $dateTo }}"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    </div>
                    <div>
                        <label for="business_unit" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Unit</label>
                        <select name="business_unit" id="business_unit"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            <option value="">All Units</option>
                            <option value="moto" {{ $businessUnit === 'moto' ? 'selected' : '' }}>Micro Moto</option>
                            <option value="cool" {{ $businessUnit === 'cool' ? 'selected' : '' }}>Micro Cool</option>
                        </select>
                    </div>
                    <div>
                        <label for="payment_method" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Payment</label>
                        <select name="payment_method" id="payment_method"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            <option value="">All Methods</option>
                            <option value="cash" {{ $paymentMethod === 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="transfer" {{ $paymentMethod === 'transfer' ? 'selected' : '' }}>Transfer</option>
                        </select>
                    </div>
                    <div>
                        <button type="submit"
                                class="w-full px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                            Filter
                        </button>
                    </div>
                </form>
            </div>

            {{-- Summary Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6 text-center">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total Cash</p>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-2">{{ number_format($totalCash, 2) }} <span class="text-sm font-normal">MVR</span></p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6 text-center">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total Transfer</p>
                    <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-2">{{ number_format($totalTransfer, 2) }} <span class="text-sm font-normal">MVR</span></p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6 text-center">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Grand Total</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-2">{{ number_format($grandTotal, 2) }} <span class="text-sm font-normal">MVR</span></p>
                </div>
            </div>

            {{-- Unit Breakdown --}}
            @if($unitBreakdown->isNotEmpty() && !$businessUnit)
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide mb-4">Unit Breakdown</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($unitBreakdown as $unit => $data)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ $unit === 'moto' ? 'Micro Moto' : 'Micro Cool' }}</h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-500 dark:text-gray-400">Cash</span>
                                        <span class="text-green-600 dark:text-green-400 font-medium">{{ number_format($data['cash'], 2) }} MVR</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500 dark:text-gray-400">Transfer</span>
                                        <span class="text-blue-600 dark:text-blue-400 font-medium">{{ number_format($data['transfer'], 2) }} MVR</span>
                                    </div>
                                    <div class="flex justify-between border-t border-gray-200 dark:border-gray-700 pt-2 font-semibold">
                                        <span class="text-gray-700 dark:text-gray-300">Total</span>
                                        <span class="text-gray-900 dark:text-gray-100">{{ number_format($data['grand'], 2) }} MVR</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Daily Breakdown --}}
            @if($dailySummaries->isNotEmpty())
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="p-4 sm:p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">Daily Breakdown</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Unit</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Payment</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($dailySummaries as $summary)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $summary['date']->format('D, d M') }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $summary['business_unit'] === 'moto' ? 'Micro Moto' : 'Micro Cool' }}</td>
                                        <td class="px-4 py-3 text-center">
                                            @if($summary['payment_method'])
                                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                                                    {{ $summary['payment_method'] === 'cash' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' }}">
                                                    {{ ucfirst($summary['payment_method']) }}
                                                </span>
                                            @else
                                                <span class="text-gray-400 dark:text-gray-500">&mdash;</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right font-medium text-gray-900 dark:text-gray-100">{{ number_format($summary['grand'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Top Items --}}
            @if($topItems->isNotEmpty())
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="p-4 sm:p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">Top 10 Items by Revenue</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">#</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Item</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Qty Sold</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Revenue</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($topItems as $index => $item)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $index + 1 }}</td>
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $item['description'] }}</td>
                                        <td class="px-4 py-3 text-sm text-right text-gray-700 dark:text-gray-300">{{ $item['qty_sold'] }}</td>
                                        <td class="px-4 py-3 text-sm text-right font-medium text-gray-900 dark:text-gray-100">{{ number_format($item['revenue'], 2) }} MVR</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Empty State --}}
            @if($dailySummaries->isEmpty())
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-center">
                    <p class="text-gray-500 dark:text-gray-400">No submitted sales logs found for the selected filters.</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
