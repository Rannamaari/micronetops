{{-- resources/views/jobs/index.blade.php - Mobile-First Redesign --}}

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Jobs
            </h2>
            {{-- Desktop only: Calendar + New buttons --}}
            <div class="hidden sm:flex gap-2">
                <a href="{{ route('jobs.calendar') }}"
                   class="inline-flex items-center px-3 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-200">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Calendar
                </a>
                <a href="{{ route('jobs.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 rounded-lg font-medium text-sm text-white hover:bg-indigo-700">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    New Job
                </a>
            </div>
            {{-- Mobile only: Calendar icon --}}
            <a href="{{ route('jobs.calendar') }}"
               class="sm:hidden p-2 text-gray-500 dark:text-gray-400 hover:text-gray-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </a>
        </div>
    </x-slot>

    @php
        $currentView = $view ?? 'current';
        $currentWhen = $when ?? null;
        $viewTabs = [
            'current' => ['label' => 'Active', 'count' => $statusCounts['current'] ?? 0],
            'my_jobs' => ['label' => 'Mine', 'count' => $statusCounts['my_jobs'] ?? 0],
            'completed' => ['label' => 'Done', 'count' => $statusCounts['completed'] ?? 0],
        ];
        $whenLabels = [
            null => 'All dates',
            'today' => 'Today',
            'tomorrow' => 'Tomorrow',
            'week' => 'This week',
            'later' => 'Later',
            'unscheduled' => 'Unscheduled',
        ];
    @endphp

    <div class="pb-20 sm:pb-6" x-data="{ showFilters: false }">
        <div class="max-w-7xl mx-auto">

            {{-- Search bar - Full width, prominent --}}
            <div class="px-4 pt-3 pb-2 bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 sm:border-0 sm:bg-transparent sm:pt-6">
                <form action="{{ route('jobs.index') }}" method="GET">
                    @foreach(request()->except(['search', 'page']) as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <input type="text" name="search" value="{{ $search ?? '' }}"
                               placeholder="Search jobs..."
                               class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-700 border-0 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:bg-white dark:focus:bg-gray-600">
                    </div>
                </form>
            </div>

            {{-- Status tabs - Compact segmented control --}}
            <div class="px-4 py-3 bg-white dark:bg-gray-800 sm:bg-transparent">
                <div class="inline-flex p-1 bg-gray-100 dark:bg-gray-700 rounded-lg w-full sm:w-auto">
                    @foreach($viewTabs as $key => $data)
                        <a href="{{ route('jobs.index', ['view' => $key]) }}"
                           class="flex-1 sm:flex-none flex items-center justify-center gap-1.5 px-4 py-2 rounded-md text-sm font-medium transition-colors
                                  {{ $currentView === $key
                                      ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-white shadow-sm'
                                      : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200' }}">
                            {{ $data['label'] }}
                            @if($data['count'] > 0)
                                <span class="text-xs {{ $currentView === $key ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-400' }}">{{ $data['count'] }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- When dropdown + Filter button row --}}
            @if($currentView !== 'completed')
            <div class="flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 sm:bg-transparent sm:border-0">
                {{-- When dropdown --}}
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" type="button"
                            class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium rounded-lg
                                   {{ $currentWhen ? 'text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30' : 'text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        {{ $whenLabels[$currentWhen] ?? 'All dates' }}
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition
                         class="absolute left-0 mt-1 w-48 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 py-1 z-50">
                        <a href="{{ route('jobs.index', array_merge(request()->except(['when', 'page']), [])) }}"
                           class="block px-4 py-2.5 text-sm {{ !$currentWhen ? 'text-indigo-600 bg-indigo-50 dark:bg-indigo-900/30' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                            All dates
                        </a>
                        @foreach(['today' => 'Today', 'tomorrow' => 'Tomorrow', 'week' => 'This week', 'later' => 'Later', 'unscheduled' => 'Unscheduled'] as $whenKey => $whenLabel)
                            <a href="{{ route('jobs.index', array_merge(request()->except('page'), ['when' => $whenKey])) }}"
                               class="flex items-center justify-between px-4 py-2.5 text-sm {{ $currentWhen === $whenKey ? 'text-indigo-600 bg-indigo-50 dark:bg-indigo-900/30' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                {{ $whenLabel }}
                                @if(($dateCounts[$whenKey] ?? 0) > 0)
                                    <span class="text-xs text-gray-400">{{ $dateCounts[$whenKey] }}</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>

                {{-- Filter button --}}
                <button @click="showFilters = true" type="button"
                        class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium rounded-lg
                               {{ $type || $priority ? 'text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30' : 'text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    Filter
                    @if($type || $priority)
                        <span class="w-2 h-2 bg-indigo-500 rounded-full"></span>
                    @endif
                </button>

                {{-- Active filter chips --}}
                @if($type)
                    <a href="{{ route('jobs.index', array_merge(request()->except('type'), [])) }}"
                       class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 rounded-full">
                        {{ $type === 'ac' ? 'AC' : 'Bike' }}
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </a>
                @endif
            </div>
            @endif

            {{-- Success message --}}
            @if (session('success'))
                <div class="mx-4 mt-3 px-4 py-3 bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded-xl text-sm">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Mobile card view - Clean, scannable --}}
            <div class="sm:hidden px-4 pt-3 space-y-2">
                @forelse($jobs as $job)
                    <a href="{{ route('jobs.show', $job) }}"
                       class="block bg-white dark:bg-gray-800 rounded-xl p-3 active:bg-gray-50 dark:active:bg-gray-700">
                        <div class="flex items-start gap-3">
                            {{-- Type indicator --}}
                            <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center
                                        {{ $job->job_type === 'ac' ? 'bg-sky-100 dark:bg-sky-900/30' : 'bg-orange-100 dark:bg-orange-900/30' }}">
                                <span class="text-lg">{{ $job->job_type === 'ac' ? '❄️' : '🏍️' }}</span>
                            </div>

                            {{-- Content --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <h3 class="font-medium text-gray-900 dark:text-gray-100 truncate">
                                            {{ $job->title ?: $job->customer_name }}
                                        </h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 truncate">
                                            {{ $job->customer_name }} @if($job->location)• {{ Str::limit($job->location, 20) }}@endif
                                        </p>
                                    </div>
                                    {{-- Status pill --}}
                                    <span class="flex-shrink-0 px-2 py-0.5 rounded-md text-xs font-medium"
                                          style="background-color: {{ $job->status_color }}15; color: {{ $job->status_color }};">
                                        {{ \App\Models\Job::getStatuses()[$job->status] ?? $job->status }}
                                    </span>
                                </div>

                                {{-- Time + Actions row --}}
                                <div class="flex items-center justify-between mt-2">
                                    <div class="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                                        @if($job->scheduled_at)
                                            <span class="{{ $job->scheduled_at->isToday() ? 'text-green-600 dark:text-green-400 font-medium' : '' }}">
                                                {{ $job->scheduled_at->isToday() ? 'Today' : ($job->scheduled_at->isTomorrow() ? 'Tomorrow' : $job->scheduled_at->format('M j')) }}
                                                {{ $job->scheduled_at->format('g:i A') }}
                                            </span>
                                        @else
                                            <span class="text-amber-600 dark:text-amber-400">Unscheduled</span>
                                        @endif
                                        @if($job->assignees->count())
                                            <span>{{ $job->assignees->first()->name }}</span>
                                        @endif
                                    </div>
                                    {{-- WhatsApp icon --}}
                                    <span onclick="event.preventDefault(); event.stopPropagation(); window.open('https://wa.me/{{ preg_replace('/[^0-9]/', '', $job->customer_phone) }}', '_blank')"
                                          class="p-1.5 text-green-600 hover:bg-green-50 dark:hover:bg-green-900/30 rounded-lg">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                        </svg>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="py-12 text-center">
                        <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <p class="text-gray-500 dark:text-gray-400 mb-4">No jobs found</p>
                    </div>
                @endforelse

                {{-- Pagination --}}
                @if($jobs->hasPages())
                    <div class="py-4">
                        {{ $jobs->links() }}
                    </div>
                @endif
            </div>

            {{-- Mobile FAB (Floating Action Button) --}}
            <a href="{{ route('jobs.create') }}"
               class="sm:hidden fixed bottom-6 right-6 w-14 h-14 bg-indigo-600 text-white rounded-full shadow-lg flex items-center justify-center active:bg-indigo-700 z-40">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                </svg>
            </a>

            {{-- Filter Bottom Sheet --}}
            <div x-show="showFilters" x-cloak
                 class="sm:hidden fixed inset-0 z-50"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
                {{-- Backdrop --}}
                <div class="absolute inset-0 bg-black/50" @click="showFilters = false"></div>

                {{-- Sheet --}}
                <div class="absolute bottom-0 left-0 right-0 bg-white dark:bg-gray-800 rounded-t-2xl max-h-[70vh] overflow-y-auto"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="translate-y-full"
                     x-transition:enter-end="translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="translate-y-0"
                     x-transition:leave-end="translate-y-full">
                    {{-- Handle --}}
                    <div class="flex justify-center py-3">
                        <div class="w-10 h-1 bg-gray-300 dark:bg-gray-600 rounded-full"></div>
                    </div>

                    <div class="px-4 pb-8">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Filters</h3>
                            @if($type || $priority)
                                <a href="{{ route('jobs.index', ['view' => $currentView]) }}"
                                   class="text-sm text-indigo-600 dark:text-indigo-400">Clear all</a>
                            @endif
                        </div>

                        {{-- Job Type --}}
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Job Type</label>
                            <div class="grid grid-cols-3 gap-2">
                                <a href="{{ route('jobs.index', array_merge(request()->except('type'), [])) }}"
                                   class="px-4 py-3 text-center rounded-xl text-sm font-medium
                                          {{ !$type ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                                    All
                                </a>
                                <a href="{{ route('jobs.index', array_merge(request()->all(), ['type' => 'ac'])) }}"
                                   class="px-4 py-3 text-center rounded-xl text-sm font-medium
                                          {{ $type === 'ac' ? 'bg-sky-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                                    ❄️ AC
                                </a>
                                <a href="{{ route('jobs.index', array_merge(request()->all(), ['type' => 'moto'])) }}"
                                   class="px-4 py-3 text-center rounded-xl text-sm font-medium
                                          {{ $type === 'moto' ? 'bg-orange-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                                    🏍️ Bike
                                </a>
                            </div>
                        </div>

                        {{-- Priority --}}
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Priority</label>
                            <div class="grid grid-cols-2 gap-2">
                                <a href="{{ route('jobs.index', array_merge(request()->except('priority'), [])) }}"
                                   class="px-4 py-3 text-center rounded-xl text-sm font-medium
                                          {{ !$priority ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                                    All
                                </a>
                                @foreach(\App\Models\Job::getPriorities() as $key => $label)
                                    <a href="{{ route('jobs.index', array_merge(request()->all(), ['priority' => $key])) }}"
                                       class="px-4 py-3 text-center rounded-xl text-sm font-medium
                                              {{ $priority === $key ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                                        {{ $label }}
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        <button @click="showFilters = false" type="button"
                                class="w-full py-3 bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900 rounded-xl font-medium">
                            Done
                        </button>
                    </div>
                </div>
            </div>

            {{-- Desktop table view --}}
            <div class="hidden sm:block sm:px-6 lg:px-8 sm:mt-4">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Job</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Customer</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Scheduled</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Status</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Assigned</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($jobs as $job)
                            <tr onclick="window.location.href='{{ route('jobs.show', $job) }}'"
                                class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-8 rounded-full {{ $job->job_type === 'ac' ? 'bg-sky-500' : 'bg-orange-500' }}"></span>
                                        <div>
                                            <div class="font-medium text-gray-900 dark:text-gray-100">
                                                {{ $job->title ?: 'Job #' . $job->id }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ strtoupper($job->job_type) }} - {{ $job->job_category }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $job->customer_name }}</div>
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $job->customer_phone) }}"
                                       onclick="event.stopPropagation()"
                                       class="text-xs text-green-600 dark:text-green-400 hover:underline">
                                        {{ $job->customer_phone }}
                                    </a>
                                </td>
                                <td class="px-4 py-4 text-gray-600 dark:text-gray-400">
                                    @if($job->scheduled_at)
                                        <div>{{ $job->scheduled_at->format('M j, Y') }}</div>
                                        <div class="text-xs">{{ $job->scheduled_at->format('g:i A') }}</div>
                                    @else
                                        <span class="text-gray-400">Not scheduled</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex px-2 py-1 rounded text-xs font-medium"
                                          style="background-color: {{ $job->status_color }}20; color: {{ $job->status_color }};">
                                        {{ \App\Models\Job::getStatuses()[$job->status] ?? $job->status }}
                                    </span>
                                    @if($job->priority !== 'normal')
                                        <span class="inline-flex px-2 py-1 rounded text-xs font-medium ml-1"
                                              style="background-color: {{ $job->priority_color }}20; color: {{ $job->priority_color }};">
                                            {{ ucfirst($job->priority) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-400">
                                    @if($job->assignees->count())
                                        {{ $job->assignees->pluck('name')->join(', ') }}
                                    @else
                                        <span class="text-gray-400">Unassigned</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4" onclick="event.stopPropagation()">
                                    <a href="{{ route('jobs.show', $job) }}"
                                       class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 text-sm font-medium">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-gray-500 dark:text-gray-400">
                                    No jobs found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4 px-6">
                {{ $jobs->links() }}
            </div>
            </div>
        </div>
    </div>
</x-app-layout>
