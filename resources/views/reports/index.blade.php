<x-app-layout>
    @php
        $themeStyles = [
            'blue' => [
                'card' => 'bg-blue-50 border-blue-200 dark:bg-blue-900/20 dark:border-blue-800',
                'icon' => 'text-blue-600 dark:text-blue-400 bg-blue-100 dark:bg-blue-900/40',
                'badge' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
                'link' => 'text-blue-700 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-900/40',
            ],
            'teal' => [
                'card' => 'bg-teal-50 border-teal-200 dark:bg-teal-900/20 dark:border-teal-800',
                'icon' => 'text-teal-600 dark:text-teal-400 bg-teal-100 dark:bg-teal-900/40',
                'badge' => 'bg-teal-100 text-teal-700 dark:bg-teal-900/40 dark:text-teal-300',
                'link' => 'text-teal-700 dark:text-teal-300 hover:bg-teal-100 dark:hover:bg-teal-900/40',
            ],
            'green' => [
                'card' => 'bg-green-50 border-green-200 dark:bg-green-900/20 dark:border-green-800',
                'icon' => 'text-green-600 dark:text-green-400 bg-green-100 dark:bg-green-900/40',
                'badge' => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
                'link' => 'text-green-700 dark:text-green-300 hover:bg-green-100 dark:hover:bg-green-900/40',
            ],
            'yellow' => [
                'card' => 'bg-yellow-50 border-yellow-200 dark:bg-yellow-900/20 dark:border-yellow-800',
                'icon' => 'text-yellow-600 dark:text-yellow-400 bg-yellow-100 dark:bg-yellow-900/40',
                'badge' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300',
                'link' => 'text-yellow-700 dark:text-yellow-300 hover:bg-yellow-100 dark:hover:bg-yellow-900/40',
            ],
            'purple' => [
                'card' => 'bg-purple-50 border-purple-200 dark:bg-purple-900/20 dark:border-purple-800',
                'icon' => 'text-purple-600 dark:text-purple-400 bg-purple-100 dark:bg-purple-900/40',
                'badge' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300',
                'link' => 'text-purple-700 dark:text-purple-300 hover:bg-purple-100 dark:hover:bg-purple-900/40',
            ],
            'red' => [
                'card' => 'bg-red-50 border-red-200 dark:bg-red-900/20 dark:border-red-800',
                'icon' => 'text-red-600 dark:text-red-400 bg-red-100 dark:bg-red-900/40',
                'badge' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
                'link' => 'text-red-700 dark:text-red-300 hover:bg-red-100 dark:hover:bg-red-900/40',
            ],
            'orange' => [
                'card' => 'bg-orange-50 border-orange-200 dark:bg-orange-900/20 dark:border-orange-800',
                'icon' => 'text-orange-600 dark:text-orange-400 bg-orange-100 dark:bg-orange-900/40',
                'badge' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300',
                'link' => 'text-orange-700 dark:text-orange-300 hover:bg-orange-100 dark:hover:bg-orange-900/40',
            ],
            'slate' => [
                'card' => 'bg-slate-50 border-slate-200 dark:bg-slate-900/20 dark:border-slate-800',
                'icon' => 'text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-900/40',
                'badge' => 'bg-slate-100 text-slate-700 dark:bg-slate-900/40 dark:text-slate-300',
                'link' => 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-900/40',
            ],
            'indigo' => [
                'card' => 'bg-indigo-50 border-indigo-200 dark:bg-indigo-900/20 dark:border-indigo-800',
                'icon' => 'text-indigo-600 dark:text-indigo-400 bg-indigo-100 dark:bg-indigo-900/40',
                'badge' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300',
                'link' => 'text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 dark:hover:bg-indigo-900/40',
            ],
        ];

        $snapshotCards = [
            [
                'label' => 'Revenue This Month',
                'value' => 'MVR ' . number_format($monthRevenue, 2),
                'note' => 'Completed jobs recognized this month',
                'theme' => 'green',
            ],
            [
                'label' => 'Expenses This Month',
                'value' => 'MVR ' . number_format($monthExpenses, 2),
                'note' => $expenseEntriesThisMonth . ' expense entries recorded',
                'theme' => 'blue',
            ],
            [
                'label' => 'Expenses This Week',
                'value' => 'MVR ' . number_format($weekExpenses, 2),
                'note' => 'Useful for short weekly stakeholder updates',
                'theme' => 'teal',
            ],
            [
                'label' => 'Low Stock Items',
                'value' => number_format($lowStockCount),
                'note' => 'Items at or below low-stock level',
                'theme' => 'red',
            ],
            [
                'label' => 'RW Expiring Soon',
                'value' => number_format($rwExpiringSoonCount),
                'note' => 'Vehicles due within the next 30 days',
                'theme' => 'indigo',
            ],
        ];

        $expenseMix = [
            'cogs' => $expenseTypeBreakdown->get('cogs'),
            'operating' => $expenseTypeBreakdown->get('operating'),
            'other' => $expenseTypeBreakdown->get('other'),
        ];
    @endphp

    <x-slot name="header">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ __('Reports') }}
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    A clean reporting hub for daily checks, monthly reviews, and stakeholder sharing.
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('expenses.reports', ['period' => 'month']) }}" class="px-3 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700">
                    Monthly Expense Pack
                </a>
                <a href="{{ route('reports.pnl', ['period' => 'month']) }}" class="px-3 py-2 rounded-lg border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                    Monthly P&L
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="relative overflow-hidden rounded-3xl border border-slate-200 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-700 p-6 text-white shadow-xl">
                <div class="absolute inset-y-0 right-0 w-1/2 bg-[radial-gradient(circle_at_top_right,_rgba(59,130,246,0.35),_transparent_55%)]"></div>
                <div class="relative grid gap-6 lg:grid-cols-[1.4fr_1fr] lg:items-end">
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-blue-100">
                            Reporting Hub
                        </div>
                        <h3 class="mt-4 max-w-2xl text-2xl font-semibold leading-tight sm:text-3xl">
                            Open the right report fast, and share clearer business updates with less digging.
                        </h3>
                        <p class="mt-3 max-w-2xl text-sm text-slate-200 sm:text-base">
                            Start with the expense dashboard for cost control, then move into P&amp;L and sales reports for a fuller stakeholder picture.
                        </p>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <a href="{{ route('expenses.reports', ['period' => 'week']) }}" class="rounded-2xl bg-white/10 px-4 py-4 text-sm font-medium text-white transition hover:bg-white/15">
                            This Week Expenses
                        </a>
                        <a href="{{ route('expenses.reports', ['period' => 'month']) }}" class="rounded-2xl bg-white/10 px-4 py-4 text-sm font-medium text-white transition hover:bg-white/15">
                            This Month Expenses
                        </a>
                        <a href="{{ route('reports.daily-sales', ['date' => now()->toDateString()]) }}" class="rounded-2xl bg-white/10 px-4 py-4 text-sm font-medium text-white transition hover:bg-white/15">
                            Today Sales
                        </a>
                        <a href="{{ route('reports.sales-trends', ['view' => 'month']) }}" class="rounded-2xl bg-white/10 px-4 py-4 text-sm font-medium text-white transition hover:bg-white/15">
                            30-Day Trend
                        </a>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
                @foreach ($snapshotCards as $card)
                    @php $style = $themeStyles[$card['theme']] ?? $themeStyles['blue']; @endphp
                    <div class="rounded-2xl border p-4 shadow-sm {{ $style['card'] }}">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $card['label'] }}</p>
                        <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $card['value'] }}</p>
                        <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">{{ $card['note'] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="grid gap-6 xl:grid-cols-[1.5fr_1fr]">
                <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Expense Review Shortcuts</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Fast links for weekly and monthly cost reviews.</p>
                        </div>
                        <a href="{{ route('expenses.reports') }}" class="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                            Open full dashboard
                        </a>
                    </div>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <a href="{{ route('expenses.reports', ['period' => 'today']) }}" class="rounded-2xl border border-gray-200 px-4 py-4 text-sm font-medium text-gray-700 transition hover:border-blue-300 hover:bg-blue-50 dark:border-gray-700 dark:text-gray-200 dark:hover:border-blue-700 dark:hover:bg-blue-900/20">Today</a>
                        <a href="{{ route('expenses.reports', ['period' => 'week']) }}" class="rounded-2xl border border-gray-200 px-4 py-4 text-sm font-medium text-gray-700 transition hover:border-blue-300 hover:bg-blue-50 dark:border-gray-700 dark:text-gray-200 dark:hover:border-blue-700 dark:hover:bg-blue-900/20">This Week</a>
                        <a href="{{ route('expenses.reports', ['period' => 'month']) }}" class="rounded-2xl border border-gray-200 px-4 py-4 text-sm font-medium text-gray-700 transition hover:border-blue-300 hover:bg-blue-50 dark:border-gray-700 dark:text-gray-200 dark:hover:border-blue-700 dark:hover:bg-blue-900/20">This Month</a>
                        <a href="{{ route('expenses.reports', ['period' => 'year']) }}" class="rounded-2xl border border-gray-200 px-4 py-4 text-sm font-medium text-gray-700 transition hover:border-blue-300 hover:bg-blue-50 dark:border-gray-700 dark:text-gray-200 dark:hover:border-blue-700 dark:hover:bg-blue-900/20">This Year</a>
                    </div>
                </div>

                <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Monthly Expense Mix</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Useful for quick owner summaries.</p>
                    <div class="mt-4 space-y-3">
                        @foreach (['cogs' => 'COGS', 'operating' => 'Operating', 'other' => 'Other'] as $key => $label)
                            @php $row = $expenseMix[$key]; @endphp
                            <div class="rounded-2xl bg-gray-50 px-4 py-3 dark:bg-gray-900/50">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ $label }}</span>
                                    <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                        MVR {{ number_format((float) ($row->total ?? 0), 2) }}
                                    </span>
                                </div>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    {{ number_format((int) ($row->entries ?? 0)) }} entries this month
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            @foreach ($reportGroups as $group)
                <section class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $group['title'] }}</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $group['description'] }}</p>
                        </div>
                    </div>

                    <div class="mt-5 grid grid-cols-1 gap-4 lg:grid-cols-2 xl:grid-cols-3">
                        @foreach ($group['reports'] as $report)
                            @php $style = $themeStyles[$report['theme']] ?? $themeStyles['blue']; @endphp
                            <div class="flex h-full flex-col rounded-3xl border p-5 transition hover:-translate-y-0.5 hover:shadow-md {{ $style['card'] }}">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl {{ $style['icon'] }}">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 19h16M6 17V7m4 10V5m4 12v-7m4 7v-4"></path>
                                        </svg>
                                    </div>
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide {{ $style['badge'] }}">
                                        {{ $report['badge'] }}
                                    </span>
                                </div>

                                <div class="mt-4">
                                    <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $report['title'] }}</h4>
                                    <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-300">{{ $report['description'] }}</p>
                                </div>

                                <div class="mt-4 flex flex-wrap gap-2">
                                    @foreach ($report['links'] as $link)
                                        <a href="{{ $link['url'] }}" class="rounded-full px-3 py-1.5 text-xs font-medium transition {{ $style['link'] }}">
                                            {{ $link['label'] }}
                                        </a>
                                    @endforeach
                                </div>

                                <div class="mt-5 pt-4 border-t border-black/5 dark:border-white/10">
                                    <a href="{{ $report['route'] }}" class="inline-flex items-center gap-2 text-sm font-semibold text-gray-900 hover:text-gray-700 dark:text-gray-100 dark:hover:text-white">
                                        Open report
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    </div>
</x-app-layout>
