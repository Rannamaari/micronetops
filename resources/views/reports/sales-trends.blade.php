<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Sales Trends Report') }}
            </h2>
            <form method="GET" action="{{ route('reports.sales-trends') }}" class="flex flex-wrap items-center gap-2">
                <select name="view"
                        class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        onchange="this.form.submit()">
                    <option value="day" {{ $view === 'day' ? 'selected' : '' }}>Last 24 Hours</option>
                    <option value="week" {{ $view === 'week' ? 'selected' : '' }}>Last 7 Days</option>
                    <option value="month" {{ $view === 'month' ? 'selected' : '' }}>Last 30 Days</option>
                </select>
                <select name="type"
                        class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        onchange="this.form.submit()">
                    <option value="all" {{ $jobType === 'all' ? 'selected' : '' }}>All Types</option>
                    <option value="moto" {{ $jobType === 'moto' ? 'selected' : '' }}>Motorcycle</option>
                    <option value="ac" {{ $jobType === 'ac' ? 'selected' : '' }}>AC Service</option>
                </select>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ __('Apply') }}
                </button>
            </form>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- Summary Cards --}}
            @php
                $totalRevenue = collect($salesData)->sum('total');
                $totalJobs = collect($salesData)->sum('count');
                $avgPerJob = $totalJobs > 0 ? $totalRevenue / $totalJobs : 0;
                $maxRevenue = collect($salesData)->max('total') ?: 1;
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 overflow-hidden shadow-lg sm:rounded-lg">
                    <div class="p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-blue-100">Total Revenue</p>
                                <p class="mt-2 text-3xl font-bold">MVR {{ number_format($totalRevenue, 2) }}</p>
                                @if($view === 'day')
                                    <p class="mt-1 text-sm text-blue-100">Last 24 hours</p>
                                @elseif($view === 'week')
                                    <p class="mt-1 text-sm text-blue-100">Last 7 days</p>
                                @else
                                    <p class="mt-1 text-sm text-blue-100">Last 30 days</p>
                                @endif
                            </div>
                            <div class="flex-shrink-0">
                                <svg class="w-12 h-12 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-500 to-green-600 overflow-hidden shadow-lg sm:rounded-lg">
                    <div class="p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-green-100">Total Jobs</p>
                                <p class="mt-2 text-3xl font-bold">{{ number_format($totalJobs, 0) }}</p>
                                <p class="mt-1 text-sm text-green-100">Jobs completed</p>
                            </div>
                            <div class="flex-shrink-0">
                                <svg class="w-12 h-12 text-green-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-purple-500 to-purple-600 overflow-hidden shadow-lg sm:rounded-lg">
                    <div class="p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-purple-100">Avg Per Job</p>
                                <p class="mt-2 text-3xl font-bold">MVR {{ number_format($avgPerJob, 2) }}</p>
                                <p class="mt-1 text-sm text-purple-100">Average transaction</p>
                            </div>
                            <div class="flex-shrink-0">
                                <svg class="w-12 h-12 text-purple-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Chart --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-6">Sales Trend</h3>

                    @if(collect($salesData)->sum('total') == 0)
                        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                            </svg>
                            <p class="mt-2">No sales data available for this period</p>
                        </div>
                    @else
                        {{-- Bar Chart --}}
                        <div class="space-y-3">
                            @foreach($salesData as $data)
                                @php
                                    $percentage = $maxRevenue > 0 ? ($data['total'] / $maxRevenue) * 100 : 0;
                                    $barWidth = max($percentage, 2); // Minimum 2% to show label
                                @endphp
                                <div class="flex items-center gap-4">
                                    <div class="w-16 sm:w-20 text-sm font-medium text-gray-700 dark:text-gray-300 flex-shrink-0">
                                        {{ $data['label'] }}
                                    </div>
                                    <div class="flex-1 relative">
                                        <div class="bg-gray-200 dark:bg-gray-700 rounded-full h-10 overflow-hidden">
                                            <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 h-full rounded-full flex items-center justify-end px-3 transition-all duration-500"
                                                 style="width: {{ $barWidth }}%">
                                                @if($data['total'] > 0)
                                                    <span class="text-xs font-semibold text-white whitespace-nowrap">
                                                        MVR {{ number_format($data['total'], 0) }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="w-12 sm:w-16 text-sm text-gray-500 dark:text-gray-400 text-right flex-shrink-0">
                                        {{ $data['count'] }} {{ $data['count'] === 1 ? 'job' : 'jobs' }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Data Table --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">Detailed Data</h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Period
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Jobs
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Revenue
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Avg/Job
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($salesData as $data)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $data['label'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 text-right">
                                            {{ number_format($data['count'], 0) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-gray-100 text-right">
                                            MVR {{ number_format($data['total'], 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-right">
                                            @if($data['count'] > 0)
                                                MVR {{ number_format($data['total'] / $data['count'], 2) }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <td class="px-6 py-4 text-sm font-bold text-gray-900 dark:text-gray-100">
                                        Total
                                    </td>
                                    <td class="px-6 py-4 text-sm font-bold text-gray-900 dark:text-gray-100 text-right">
                                        {{ number_format($totalJobs, 0) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm font-bold text-gray-900 dark:text-gray-100 text-right">
                                        MVR {{ number_format($totalRevenue, 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm font-bold text-gray-900 dark:text-gray-100 text-right">
                                        MVR {{ number_format($avgPerJob, 2) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
