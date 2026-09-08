<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <a href="{{ route('vendors.index') }}" class="text-sm font-medium text-blue-600 hover:underline dark:text-blue-400">&larr; Back to Vendors</a>
                <h2 class="mt-1 text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">{{ $vendor->name }}</h2>
            </div>
            <a href="{{ route('vendors.edit', $vendor) }}" class="inline-flex justify-center rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700 dark:bg-blue-600 dark:hover:bg-blue-500">
                Edit Vendor
            </a>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700">
                <div class="grid gap-5 p-5 sm:grid-cols-2 lg:grid-cols-4 sm:p-6">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Phone</p>
                        <p class="mt-1 font-medium text-gray-900 dark:text-gray-100">{{ $vendor->phone }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Contact Person</p>
                        <p class="mt-1 font-medium text-gray-900 dark:text-gray-100">{{ $vendor->contact_name ?: 'Not added' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">GST Number (TIN)</p>
                        <p class="mt-1 font-mono font-medium text-gray-900 dark:text-gray-100">{{ $vendor->gst_number ?: 'Not added' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</p>
                        <span class="mt-1 inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $vendor->is_active ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' }}">
                            {{ $vendor->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    @if($vendor->address)
                        <div class="sm:col-span-2 lg:col-span-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Address</p>
                            <p class="mt-1 text-gray-900 dark:text-gray-100">{{ $vendor->address }}</p>
                        </div>
                    @endif
                </div>
            </section>

            <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700 sm:p-6">
                <div class="mb-5">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Expense History</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Review expenses recorded against this vendor for any date range.</p>
                </div>

                <form method="GET" action="{{ route('vendors.show', $vendor) }}" class="grid gap-4 rounded-xl bg-gray-50 p-4 dark:bg-gray-900/40 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_auto] lg:items-end">
                    <div>
                        <label for="from_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">From date</label>
                        <input id="from_date" name="from_date" type="date" value="{{ $filters['from_date'] ?? '' }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                    </div>
                    <div>
                        <label for="to_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">To date</label>
                        <input id="to_date" name="to_date" type="date" value="{{ $filters['to_date'] ?? '' }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                        @error('to_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Filter</button>
                        @if(!empty($filters['from_date']) || !empty($filters['to_date']))
                            <a href="{{ route('vendors.show', $vendor) }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-center text-sm font-semibold text-gray-700 hover:bg-white dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">Clear</a>
                        @endif
                    </div>
                </form>

                <div class="mt-5 grid grid-cols-2 gap-3 lg:grid-cols-5">
                    <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-700/40">
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Expenses</p>
                        <p class="mt-1 text-xl font-bold text-gray-900 dark:text-white">{{ number_format($summary['count']) }}</p>
                    </div>
                    <div class="rounded-xl bg-blue-50 p-4 dark:bg-blue-900/20">
                        <p class="text-xs font-medium uppercase tracking-wide text-blue-600 dark:text-blue-300">Total</p>
                        <p class="mt-1 text-xl font-bold text-blue-900 dark:text-blue-100">MVR {{ number_format($summary['total'], 2) }}</p>
                    </div>
                    <div class="rounded-xl bg-green-50 p-4 dark:bg-green-900/20">
                        <p class="text-xs font-medium uppercase tracking-wide text-green-600 dark:text-green-300">Paid</p>
                        <p class="mt-1 text-xl font-bold text-green-900 dark:text-green-100">MVR {{ number_format($summary['paid'], 2) }}</p>
                    </div>
                    <div class="rounded-xl bg-amber-50 p-4 dark:bg-amber-900/20">
                        <p class="text-xs font-medium uppercase tracking-wide text-amber-700 dark:text-amber-300">Due</p>
                        <p class="mt-1 text-xl font-bold text-amber-900 dark:text-amber-100">MVR {{ number_format($summary['due'], 2) }}</p>
                    </div>
                    <div class="col-span-2 rounded-xl bg-cyan-50 p-4 dark:bg-cyan-900/20 lg:col-span-1">
                        <p class="text-xs font-medium uppercase tracking-wide text-cyan-700 dark:text-cyan-300">GST Included</p>
                        <p class="mt-1 text-xl font-bold text-cyan-900 dark:text-cyan-100">MVR {{ number_format($summary['gst'], 2) }}</p>
                    </div>
                </div>

                <div class="mt-6 hidden overflow-x-auto md:block">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900/40">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Bill Number</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Category</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Business Unit</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">GST</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Total</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            @forelse($expenses as $expense)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $expense->incurred_at->format('d M Y') }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $expense->reference ?: '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $expense->category?->name ?? 'Uncategorized' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ \App\Models\Expense::getBusinessUnits()[$expense->business_unit] ?? ucfirst($expense->business_unit) }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-600 dark:text-gray-300">{{ $expense->is_gst_applicable ? 'MVR '.number_format((float) $expense->gst_amount, 2) : '-' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-semibold text-gray-900 dark:text-white">MVR {{ number_format((float) $expense->amount, 2) }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $expense->is_paid ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' }}">{{ $expense->is_paid ? 'Paid' : 'Due' }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-right"><a href="{{ route('expenses.show', $expense) }}" class="text-sm font-semibold text-blue-600 hover:underline dark:text-blue-400">View</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="px-4 py-10 text-center text-sm text-gray-500">No expenses found for this date range.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 space-y-3 md:hidden">
                    @forelse($expenses as $expense)
                        <a href="{{ route('expenses.show', $expense) }}" class="block rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-white">{{ $expense->category?->name ?? 'Uncategorized' }}</p>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $expense->incurred_at->format('d M Y') }} &middot; {{ $expense->reference ?: 'No bill number' }}</p>
                                </div>
                                <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $expense->is_paid ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">{{ $expense->is_paid ? 'Paid' : 'Due' }}</span>
                            </div>
                            <div class="mt-4 flex items-end justify-between gap-3">
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ \App\Models\Expense::getBusinessUnits()[$expense->business_unit] ?? ucfirst($expense->business_unit) }}</p>
                                <p class="text-lg font-bold text-gray-900 dark:text-white">MVR {{ number_format((float) $expense->amount, 2) }}</p>
                            </div>
                        </a>
                    @empty
                        <div class="rounded-xl border border-dashed border-gray-300 px-4 py-10 text-center text-sm text-gray-500 dark:border-gray-600">No expenses found for this date range.</div>
                    @endforelse
                </div>

                @if($expenses->hasPages())
                    <div class="mt-6">{{ $expenses->links() }}</div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
