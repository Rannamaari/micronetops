<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">GST Input &amp; Output Report</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">MIRA v25.1 preparation report for TIN {{ $taxpayerTin }}.</p>
            </div>
            <div class="flex gap-3">
                @if(Auth::user()->isAdmin())
                    <a href="{{ route('system.settings') }}" class="text-sm font-semibold text-emerald-600 hover:underline dark:text-emerald-400">GST Settings</a>
                @endif
                <a href="{{ route('reports.index') }}" class="text-sm font-semibold text-blue-600 hover:underline dark:text-blue-400">Back to Reports</a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <form method="GET" class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5 lg:items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Filing period</label>
                        <select name="period_type" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700" onchange="this.form.submit()">
                            <option value="monthly" @selected($periodType === 'monthly')>Monthly</option>
                            <option value="quarterly" @selected($periodType === 'quarterly')>Quarterly</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Year</label>
                        <input name="year" type="number" min="2000" max="2100" value="{{ $year }}" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                    </div>
                    @if($periodType === 'monthly')
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Month</label>
                            <select name="month" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                @foreach(range(1, 12) as $monthNumber)
                                    <option value="{{ $monthNumber }}" @selected($month === $monthNumber)>{{ \Carbon\Carbon::create(2000, $monthNumber)->format('F') }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Quarter</label>
                            <select name="quarter" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                @foreach(range(1, 4) as $quarterNumber)
                                    <option value="{{ $quarterNumber }}" @selected($quarter === $quarterNumber)>Quarter {{ $quarterNumber }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <button class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Generate Report</button>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $startDate->format('d M Y') }} to {{ $endDate->format('d M Y') }}</p>
                </div>
            </form>

            @if($missingActivities->isNotEmpty())
                <div class="rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-700 dark:bg-amber-900/20 dark:text-amber-200">
                    Taxable activity numbers are missing for: <strong>{{ $missingActivities->map(fn($unit) => \App\Models\Expense::getBusinessUnits()[$unit === 'cool' ? 'ac' : $unit] ?? ucfirst($unit))->join(', ') }}</strong>. Add the numbers from your MIRA GST registration certificate to the production environment before filing.
                </div>
            @endif
            @if($unclassifiedSales > 0)
                <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-800 dark:bg-red-900/20 dark:text-red-200">
                    MVR {{ number_format($unclassifiedSales, 2) }} of non-GST sales is unclassified. The current records do not distinguish zero-rated, exempt, and out-of-scope sales, so these amounts are not silently assigned to a MIRA category.
                </div>
            @endif

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl bg-sky-50 p-5 ring-1 ring-sky-200"><p class="text-xs font-semibold uppercase text-sky-700">Taxable Sales</p><p class="mt-2 text-2xl font-bold text-sky-950">MVR {{ number_format($outputInvoices->sum('taxable'), 2) }}</p></div>
                <div class="rounded-2xl bg-blue-50 p-5 ring-1 ring-blue-200"><p class="text-xs font-semibold uppercase text-blue-700">Output GST</p><p class="mt-2 text-2xl font-bold text-blue-950">MVR {{ number_format($outputGst, 2) }}</p></div>
                <div class="rounded-2xl bg-emerald-50 p-5 ring-1 ring-emerald-200"><p class="text-xs font-semibold uppercase text-emerald-700">Input GST</p><p class="mt-2 text-2xl font-bold text-emerald-950">MVR {{ number_format($inputGst, 2) }}</p></div>
                <div class="rounded-2xl p-5 ring-1 {{ $netGst >= 0 ? 'bg-amber-50 ring-amber-200' : 'bg-green-50 ring-green-200' }}"><p class="text-xs font-semibold uppercase text-gray-700">{{ $netGst >= 0 ? 'Estimated GST Payable' : 'Estimated GST Credit' }}</p><p class="mt-2 text-2xl font-bold text-gray-950">MVR {{ number_format(abs($netGst), 2) }}</p></div>
            </div>

            <section class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700">
                <div class="flex flex-col gap-3 border-b border-gray-200 p-5 sm:flex-row sm:items-center sm:justify-between dark:border-gray-700">
                    <div><h3 class="font-semibold text-gray-900 dark:text-gray-100">Output Tax Statement</h3><p class="mt-1 text-sm text-gray-500">{{ $taxInvoices->count() }} GST-customer invoices; {{ $otherTransactions->count() }} aggregated activity rows.</p></div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('reports.gst.export', array_merge(request()->query(), ['statement' => 'tax-invoices'])) }}" class="rounded-lg bg-blue-700 px-3 py-2 text-sm font-semibold text-white">TaxInvoices CSV</a>
                        <a href="{{ route('reports.gst.export', array_merge(request()->query(), ['statement' => 'other-transactions'])) }}" class="rounded-lg border border-blue-300 px-3 py-2 text-sm font-semibold text-blue-700">OtherTransactions CSV</a>
                    </div>
                </div>
                <div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700"><thead class="bg-gray-50 dark:bg-gray-700/50"><tr><th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Date</th><th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Invoice / Customer</th><th class="px-4 py-3 text-left text-xs uppercase text-gray-500">TIN</th><th class="px-4 py-3 text-right text-xs uppercase text-gray-500">Taxable Value</th><th class="px-4 py-3 text-right text-xs uppercase text-gray-500">Output GST</th></tr></thead><tbody class="divide-y divide-gray-100 dark:divide-gray-700">@forelse($outputInvoices as $invoice)<tr><td class="whitespace-nowrap px-4 py-3 text-sm">{{ $invoice['date']->format('d M Y') }}</td><td class="px-4 py-3 text-sm"><div class="font-semibold">{{ $invoice['invoice_number'] }}</div><div class="text-xs text-gray-500">{{ $invoice['customer_name'] }}</div></td><td class="px-4 py-3 text-sm">{{ $invoice['customer_tin'] ?: 'Other transaction' }}</td><td class="px-4 py-3 text-right text-sm">{{ number_format($invoice['taxable'], 2) }}</td><td class="px-4 py-3 text-right text-sm font-semibold text-blue-700">{{ number_format($invoice['gst'], 2) }}</td></tr>@empty<tr><td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">No invoiced sales in this period.</td></tr>@endforelse</tbody></table></div>
            </section>

            <section class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700">
                <div class="flex flex-col gap-3 border-b border-gray-200 p-5 sm:flex-row sm:items-center sm:justify-between dark:border-gray-700"><div><h3 class="font-semibold text-gray-900 dark:text-gray-100">Input Tax Statement</h3><p class="mt-1 text-sm text-gray-500">{{ $inputExpenses->count() }} GST supplier invoices eligible for review.</p></div><a href="{{ route('reports.gst.export', array_merge(request()->query(), ['statement' => 'input'])) }}" class="rounded-lg bg-emerald-700 px-3 py-2 text-center text-sm font-semibold text-white">Input Tax CSV</a></div>
                <div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700"><thead class="bg-gray-50 dark:bg-gray-700/50"><tr><th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Date</th><th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Supplier / TIN</th><th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Invoice</th><th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Type</th><th class="px-4 py-3 text-right text-xs uppercase text-gray-500">Input GST</th></tr></thead><tbody class="divide-y divide-gray-100 dark:divide-gray-700">@forelse($inputExpenses as $expense)<tr><td class="whitespace-nowrap px-4 py-3 text-sm">{{ $expense->incurred_at->format('d M Y') }}</td><td class="px-4 py-3 text-sm"><div class="font-semibold">{{ $expense->vendorEntity?->name ?? $expense->vendor }}</div><div class="text-xs text-gray-500">{{ $expense->vendorEntity?->gst_number }}</div></td><td class="px-4 py-3 text-sm">{{ $expense->reference }}</td><td class="px-4 py-3 text-sm">{{ ucfirst($expense->gst_expenditure_type ?? 'revenue') }}</td><td class="px-4 py-3 text-right text-sm font-semibold text-emerald-700">{{ number_format($expense->gst_amount, 2) }}</td></tr>@empty<tr><td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">No GST expenses in this period.</td></tr>@endforelse</tbody></table></div>
            </section>

            <div class="rounded-xl bg-gray-100 p-4 text-xs leading-5 text-gray-600 dark:bg-gray-800 dark:text-gray-300">These CSV files follow the columns in MIRA's v25.1 statements for preparation and checking. MIRAconnect requires MIRA's official Excel templates; transfer/import the reviewed CSV data into those unchanged workbooks before upload.</div>
        </div>
    </div>
</x-app-layout>
