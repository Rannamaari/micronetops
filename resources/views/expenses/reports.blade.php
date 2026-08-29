<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Expense Reports
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Review spending by period, business unit, category, vendor, account, and month.
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('expenses.index') }}" class="px-3 py-2 rounded-lg border border-gray-300 text-sm text-gray-700 hover:bg-gray-50">
                    Back to Expenses
                </a>
                <a href="{{ route('expenses.create-operating') }}" class="px-3 py-2 rounded-lg bg-gray-900 text-sm text-white hover:bg-gray-800">
                    Add Expense
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-4 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-2xl p-4 sm:p-6">
                <div class="flex flex-wrap items-center gap-2 mb-4">
                    @foreach (['today' => 'Today', 'yesterday' => 'Yesterday', 'week' => 'This Week', 'month' => 'This Month', 'year' => 'This Year', 'all' => 'All Time'] as $key => $label)
                        <a href="{{ route('expenses.reports', array_merge(request()->except('period', 'page'), ['period' => $key, 'from_date' => null, 'to_date' => null])) }}"
                            class="px-3 py-1.5 rounded-full text-xs font-medium border transition {{ $period === $key && blank($fromDate) && blank($toDate) ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>

                <form method="GET" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-3">
                    <input type="hidden" name="period" value="{{ $period }}">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Business Unit</label>
                        <select name="business_unit" class="w-full rounded-lg border-gray-300 text-sm h-11 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                            <option value="all">All Units</option>
                            @foreach ($businessUnits as $key => $label)
                                <option value="{{ $key }}" @selected($businessUnit === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Type</label>
                        <select name="type" class="w-full rounded-lg border-gray-300 text-sm h-11 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                            <option value="all">All Types</option>
                            @foreach ($types as $key => $label)
                                <option value="{{ $key }}" @selected($type === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">From</label>
                        <input type="date" name="from_date" value="{{ $fromDate }}" class="w-full rounded-lg border-gray-300 text-sm h-11 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">To</label>
                        <input type="date" name="to_date" value="{{ $toDate }}" class="w-full rounded-lg border-gray-300 text-sm h-11 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    </div>
                    <div class="xl:col-span-2">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Search</label>
                        <input type="text" name="search" value="{{ $search }}" placeholder="Vendor, reference, notes" class="w-full rounded-lg border-gray-300 text-sm h-11 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    </div>
                    <div class="md:col-span-2 xl:col-span-6 flex flex-wrap gap-2 pt-1">
                        <button type="submit" class="px-4 py-2.5 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700">
                            Apply Filters
                        </button>
                        <a href="{{ route('expenses.reports') }}" class="px-4 py-2.5 rounded-lg border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-2xl p-4">
                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Total Expenses</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-gray-100">MVR {{ number_format($totalExpenses, 2) }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $expenses->count() }} entries</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-2xl p-4">
                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">COGS</p>
                    <p class="mt-2 text-2xl font-bold text-orange-600 dark:text-orange-400">MVR {{ number_format($cogsTotal, 2) }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Inventory-linked spending</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-2xl p-4">
                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Operating</p>
                    <p class="mt-2 text-2xl font-bold text-blue-600 dark:text-blue-400">MVR {{ number_format($operatingTotal, 2) }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Day-to-day operating cost</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-2xl p-4">
                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Other</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-gray-100">MVR {{ number_format($otherTotal, 2) }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Unclassified or misc</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-2xl p-4">
                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Average Expense</p>
                    <p class="mt-2 text-2xl font-bold text-emerald-600 dark:text-emerald-400">MVR {{ number_format($averageExpense, 2) }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">COGS purchases: {{ number_format($cogsPurchaseTotal, 2) }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-2xl p-4 sm:p-6">
                    <div class="flex items-start justify-between gap-3 mb-4">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Business Unit Expense Chart</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Quick view of how spending is split across all business units for the selected filter.</p>
                        </div>
                    </div>
                    <div class="h-80">
                        <canvas id="expenseBusinessUnitChart"></canvas>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-2xl p-4 sm:p-6">
                    <div class="flex items-start justify-between gap-3 mb-4">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Month-Wise Unit Trend</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">See how each business unit’s expenses moved month by month.</p>
                        </div>
                    </div>
                    <div class="h-80">
                        <canvas id="expenseMonthlyUnitChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-2xl overflow-hidden">
                    <div class="px-4 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">By Business Unit</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Unit</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Entries</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($unitSummary as $row)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $row['label'] }}</td>
                                        <td class="px-4 py-3 text-sm text-right text-gray-600 dark:text-gray-300">{{ $row['count'] }}</td>
                                        <td class="px-4 py-3 text-sm text-right font-semibold text-gray-900 dark:text-gray-100">{{ number_format($row['amount'], 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="px-4 py-6 text-center text-sm text-gray-500">No data for this filter.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-2xl overflow-hidden">
                    <div class="px-4 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">By Category</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Category</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Type</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Entries</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($categorySummary as $row)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $row['name'] }}</td>
                                        <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400 uppercase">{{ $row['type'] }}</td>
                                        <td class="px-4 py-3 text-sm text-right text-gray-600 dark:text-gray-300">{{ $row['count'] }}</td>
                                        <td class="px-4 py-3 text-sm text-right font-semibold text-gray-900 dark:text-gray-100">{{ number_format($row['amount'], 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">No data for this filter.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-2xl overflow-hidden">
                    <div class="px-4 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">By Vendor</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Vendor</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Entries</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Amount</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Last Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($vendorSummary as $row)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $row['name'] }}</td>
                                        <td class="px-4 py-3 text-sm text-right text-gray-600 dark:text-gray-300">{{ $row['count'] }}</td>
                                        <td class="px-4 py-3 text-sm text-right font-semibold text-gray-900 dark:text-gray-100">{{ number_format($row['amount'], 2) }}</td>
                                        <td class="px-4 py-3 text-sm text-right text-gray-500 dark:text-gray-400">{{ $row['last_incurred_at'] }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">No vendor data for this filter.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-2xl overflow-hidden">
                    <div class="px-4 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">By Account</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Account</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Entries</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($accountSummary as $row)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $row['name'] }}</td>
                                        <td class="px-4 py-3 text-sm text-right text-gray-600 dark:text-gray-300">{{ $row['count'] }}</td>
                                        <td class="px-4 py-3 text-sm text-right font-semibold text-gray-900 dark:text-gray-100">{{ number_format($row['amount'], 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="px-4 py-6 text-center text-sm text-gray-500">No account data for this filter.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-2xl overflow-hidden">
                <div class="px-4 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Month-Wise Breakdown</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Month</th>
                                <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Entries</th>
                                <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">COGS</th>
                                <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Operating</th>
                                <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Other</th>
                                <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($monthlySummary as $row)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $row['label'] }}</td>
                                    <td class="px-4 py-3 text-sm text-right text-gray-600 dark:text-gray-300">{{ $row['count'] }}</td>
                                    <td class="px-4 py-3 text-sm text-right text-orange-600 dark:text-orange-400">{{ number_format($row['cogs_amount'], 2) }}</td>
                                    <td class="px-4 py-3 text-sm text-right text-blue-600 dark:text-blue-400">{{ number_format($row['operating_amount'], 2) }}</td>
                                    <td class="px-4 py-3 text-sm text-right text-gray-600 dark:text-gray-300">{{ number_format($row['other_amount'], 2) }}</td>
                                    <td class="px-4 py-3 text-sm text-right font-semibold text-gray-900 dark:text-gray-100">{{ number_format($row['amount'], 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">No monthly data for this filter.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-2xl overflow-hidden">
                <div class="px-4 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Recent Expense Entries</h3>
                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $expenses->count() }} matched</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Category</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Unit</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Vendor</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Account</th>
                                <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($expenses->take(20) as $expense)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $expense->incurred_at?->format('d M Y') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $expense->category?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $businessUnits[$expense->business_unit] ?? $expense->business_unit }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $expense->vendorEntity?->name ?? $expense->vendor ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $expense->account?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-right font-semibold text-gray-900 dark:text-gray-100">{{ number_format($expense->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">No expenses found for the selected filters.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        const isDarkMode = document.documentElement.classList.contains('dark');
        const axisColor = isDarkMode ? '#9CA3AF' : '#4B5563';
        const gridColor = isDarkMode ? '#374151' : '#E5E7EB';

        const businessUnitCanvas = document.getElementById('expenseBusinessUnitChart');
        if (businessUnitCanvas && @json(count($unitChart['labels'])) > 0) {
            new Chart(businessUnitCanvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: @json($unitChart['labels']),
                    datasets: [{
                        label: 'Expense Amount (MVR)',
                        data: @json($unitChart['amounts']),
                        backgroundColor: [
                            'rgba(249, 115, 22, 0.75)',
                            'rgba(16, 185, 129, 0.75)',
                            'rgba(59, 130, 246, 0.75)',
                            'rgba(139, 92, 246, 0.75)',
                            'rgba(107, 114, 128, 0.75)',
                        ],
                        borderColor: [
                            'rgb(249, 115, 22)',
                            'rgb(16, 185, 129)',
                            'rgb(59, 130, 246)',
                            'rgb(139, 92, 246)',
                            'rgb(107, 114, 128)',
                        ],
                        borderWidth: 1,
                        borderRadius: 10,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: (context) => `MVR ${Number(context.raw || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { color: axisColor },
                            grid: { color: gridColor }
                        },
                        x: {
                            ticks: { color: axisColor },
                            grid: { display: false }
                        }
                    }
                }
            });
        }

        const monthlyUnitCanvas = document.getElementById('expenseMonthlyUnitChart');
        if (monthlyUnitCanvas && @json(count($monthlyUnitChart['labels'])) > 0 && @json(count($monthlyUnitChart['datasets'])) > 0) {
            new Chart(monthlyUnitCanvas.getContext('2d'), {
                type: 'line',
                data: {
                    labels: @json($monthlyUnitChart['labels']),
                    datasets: @json($monthlyUnitChart['datasets']).map((dataset) => ({
                        ...dataset,
                        tension: 0.35,
                        fill: false,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                    })),
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: axisColor
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: (context) => `${context.dataset.label}: MVR ${Number(context.raw || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { color: axisColor },
                            grid: { color: gridColor }
                        },
                        x: {
                            ticks: { color: axisColor },
                            grid: { color: gridColor }
                        }
                    }
                }
            });
        }
    </script>
</x-app-layout>
