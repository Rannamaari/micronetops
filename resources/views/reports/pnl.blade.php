<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Profit & Loss') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium">Period</label>
                            <select name="period" class="mt-1 w-full rounded border-gray-300">
                                <option value="month" @selected($period === 'month')>Month</option>
                                <option value="quarter" @selected($period === 'quarter')>Quarter</option>
                                <option value="year" @selected($period === 'year')>Year</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Year</label>
                            <input type="number" name="year" min="2000" max="2100" class="mt-1 w-full rounded border-gray-300" value="{{ $year }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Month</label>
                            <input type="number" name="month" min="1" max="12" class="mt-1 w-full rounded border-gray-300" value="{{ $month }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Quarter</label>
                            <input type="number" name="quarter" min="1" max="4" class="mt-1 w-full rounded border-gray-300" value="{{ $quarter }}">
                        </div>
                        <div class="md:col-span-4 flex items-center justify-end">
                            <button class="px-4 py-2 bg-gray-900 text-white rounded-lg">Update</button>
                        </div>
                    </form>

                    <div class="mt-6 text-sm text-gray-500">
                        Period: {{ $startDate->toDateString() }} to {{ $endDate->toDateString() }} (accrual basis)
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">Summary by Business Unit</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Unit</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Revenue</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">COGS</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Gross Profit</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Operating Expenses</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Net Profit</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($businessUnits as $unitKey => $unitLabel)
                                    <tr>
                                        <td class="px-4 py-2">{{ $unitLabel }}</td>
                                        <td class="px-4 py-2 text-right">{{ number_format($revenue[$unitKey] ?? 0, 2) }}</td>
                                        <td class="px-4 py-2 text-right">{{ number_format($cogs[$unitKey] ?? 0, 2) }}</td>
                                        <td class="px-4 py-2 text-right">{{ number_format($grossProfit[$unitKey] ?? 0, 2) }}</td>
                                        <td class="px-4 py-2 text-right">{{ number_format($opex[$unitKey] ?? 0, 2) }}</td>
                                        <td class="px-4 py-2 text-right font-semibold">{{ number_format($netProfit[$unitKey] ?? 0, 2) }}</td>
                                    </tr>
                                @endforeach
                                <tr class="bg-gray-50 font-semibold">
                                    <td class="px-4 py-2">Total</td>
                                    <td class="px-4 py-2 text-right">{{ number_format(array_sum($revenue), 2) }}</td>
                                    <td class="px-4 py-2 text-right">{{ number_format(array_sum($cogs), 2) }}</td>
                                    <td class="px-4 py-2 text-right">{{ number_format(array_sum($grossProfit), 2) }}</td>
                                    <td class="px-4 py-2 text-right">{{ number_format(array_sum($opex), 2) }}</td>
                                    <td class="px-4 py-2 text-right">{{ number_format(array_sum($netProfit), 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 text-xs text-gray-500">
                        Shared costs are shown separately and included in the totals. For deeper allocation, we can add allocation rules in Phase 2.
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-semibold mb-4">COGS Breakdown</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span>Inventory Purchases</span>
                                <span class="font-medium">{{ number_format(array_sum($purchaseCogs), 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>COGS Expenses</span>
                                <span class="font-medium">{{ number_format(array_sum($cogsExpenses), 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-semibold mb-4">Notes</h3>
                        <ul class="text-sm text-gray-600 dark:text-gray-300 list-disc list-inside">
                            <li>Revenue is recognized on job completion date.</li>
                            <li>Inventory purchases are treated as COGS in this phase.</li>
                            <li>Operating expenses use incurred date (accrual basis).</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
