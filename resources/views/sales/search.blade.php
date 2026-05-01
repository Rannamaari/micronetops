<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Search
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Search across customers, jobs, and sales from one place.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-4 sm:py-6">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4 sm:space-y-6">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-2xl border border-gray-100 dark:border-gray-700 p-4 sm:p-6">
                <form method="GET" action="{{ route('sales.search') }}" class="space-y-3">
                    <label for="q" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Search Anything
                    </label>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <input
                            type="text"
                            name="q"
                            id="q"
                            value="{{ $query }}"
                            placeholder="Customer name, phone, job #, sale #, PO number, location..."
                            class="flex-1 h-12 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 shadow-sm text-base px-4 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        >
                        <button
                            type="submit"
                            class="h-12 px-5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold transition"
                        >
                            Search
                        </button>
                        @if(filled($query))
                            <a
                                href="{{ route('sales.search') }}"
                                class="h-12 px-5 inline-flex items-center justify-center rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-semibold transition"
                            >
                                Clear
                            </a>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Strong search checks customer names and phones, job IDs, sales IDs, PO numbers, locations, notes, and sale line descriptions.
                    </p>
                </form>
            </div>

            @if(blank($query))
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-2xl border border-dashed border-gray-200 dark:border-gray-700 p-8 text-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Start typing to search</h3>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Try a customer name, phone number, job number, sale number, PO number, or even a location.
                    </p>
                </div>
            @else
                @php
                    $totalResults = $customers->count() + $jobs->count() + $sales->count();
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm">
                        <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Customers</div>
                        <div class="mt-1 text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $customers->count() }}</div>
                    </div>
                    <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm">
                        <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Jobs</div>
                        <div class="mt-1 text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $jobs->count() }}</div>
                    </div>
                    <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm">
                        <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Sales</div>
                        <div class="mt-1 text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $sales->count() }}</div>
                    </div>
                </div>

                @if($totalResults === 0)
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-2xl border border-gray-200 dark:border-gray-700 p-8 text-center text-sm text-gray-500 dark:text-gray-400">
                        No results found for "{{ $query }}".
                    </div>
                @endif

                <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 sm:gap-6">
                    <section class="bg-white dark:bg-gray-800 shadow-sm rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Customers</h3>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($customers as $customer)
                                <a href="{{ route('customers.show', $customer) }}" class="block px-4 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/40 transition">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $customer->name }}</div>
                                            <div class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ $customer->phone ?: 'No phone' }}</div>
                                            @if($customer->address)
                                                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400 line-clamp-2">{{ $customer->address }}</div>
                                            @endif
                                        </div>
                                        <div class="text-right shrink-0">
                                            <div class="text-xs text-gray-500 dark:text-gray-400">Customer #{{ $customer->id }}</div>
                                            <div class="mt-1 text-xs font-medium text-indigo-600 dark:text-indigo-400">{{ $customer->jobs_count }} jobs</div>
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <div class="px-4 py-6 text-sm text-gray-500 dark:text-gray-400">No customer matches.</div>
                            @endforelse
                        </div>
                    </section>

                    <section class="bg-white dark:bg-gray-800 shadow-sm rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Jobs</h3>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($jobs as $job)
                                <a href="{{ route('jobs.show', $job) }}" class="block px-4 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/40 transition">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="font-semibold text-gray-900 dark:text-gray-100 truncate">Job #{{ $job->id }}{{ $job->title ? ' · ' . $job->title : '' }}</div>
                                            <div class="mt-1 text-sm text-gray-600 dark:text-gray-300 truncate">{{ $job->customer_name ?: optional($job->customer)->name ?: 'Walk-in' }}</div>
                                            @if($job->location)
                                                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400 line-clamp-2">{{ $job->location }}</div>
                                            @endif
                                        </div>
                                        <div class="text-right shrink-0">
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ str($job->status)->replace('_', ' ')->title() }}</div>
                                            <div class="mt-1 text-xs font-medium text-gray-700 dark:text-gray-300">{{ number_format((float) $job->total_amount, 2) }} MVR</div>
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <div class="px-4 py-6 text-sm text-gray-500 dark:text-gray-400">No job matches.</div>
                            @endforelse
                        </div>
                    </section>

                    <section class="bg-white dark:bg-gray-800 shadow-sm rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Sales</h3>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($sales as $sale)
                                @php $totals = $sale->totals; @endphp
                                <a href="{{ route('sales.daily.show', $sale) }}" class="block px-4 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/40 transition">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="font-semibold text-gray-900 dark:text-gray-100 truncate">Sale #{{ $sale->id }}</div>
                                            <div class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                                {{ $sale->customer?->name ?? $sale->job?->customer_name ?? 'Walk-in' }}
                                            </div>
                                            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                {{ $sale->date?->format('d M Y') }} · {{ strtoupper($sale->business_unit) }} · {{ $sale->lines->count() }} line(s)
                                            </div>
                                        </div>
                                        <div class="text-right shrink-0">
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $sale->status_label }}</div>
                                            <div class="mt-1 text-xs font-medium text-gray-700 dark:text-gray-300">{{ number_format($totals['grand'], 2) }} MVR</div>
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <div class="px-4 py-6 text-sm text-gray-500 dark:text-gray-400">No sale matches.</div>
                            @endforelse
                        </div>
                    </section>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
