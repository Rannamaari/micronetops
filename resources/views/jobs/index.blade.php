{{-- resources/views/jobs/index.blade.php - Mobile-First Optimized --}}

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Jobs
            </h2>
            {{-- Header buttons - visible on all screens --}}
            <div class="flex gap-2 sm:gap-3">
                <a href="{{ route('jobs.calendar') }}"
                   class="inline-flex items-center px-3 py-2 sm:px-4 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg font-medium text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Calendar
                </a>
                <a href="{{ route('jobs.create') }}"
                   class="inline-flex items-center px-3 py-2 sm:px-4 bg-indigo-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-indigo-700 focus:outline-none">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                    </svg>
                    New
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-4 sm:py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- View tabs - horizontally scrollable on mobile --}}
            <div class="overflow-x-auto -mx-4 px-4 sm:mx-0 sm:px-0 mb-3">
                <div class="flex items-center gap-2 min-w-max">
                    @php
                        $currentView = $view ?? 'current';
                        $viewTabs = [
                            'current' => ['label' => 'Active', 'count' => $statusCounts['current'] ?? 0],
                            'my_jobs' => ['label' => 'My Jobs', 'count' => $statusCounts['my_jobs'] ?? 0],
                            'completed' => ['label' => 'Done', 'count' => $statusCounts['completed'] ?? 0],
                        ];
                    @endphp

                    @foreach($viewTabs as $key => $data)
                        <a href="{{ route('jobs.index', ['view' => $key]) }}"
                           class="inline-flex items-center px-4 py-2.5 rounded-xl text-sm font-semibold min-h-[44px] whitespace-nowrap
                                  {{ $currentView === $key
                                      ? 'bg-indigo-600 text-white shadow-sm'
                                      : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600' }}">
                            <span>{{ $data['label'] }}</span>
                            <span class="ml-1.5 px-1.5 py-0.5 rounded-full text-xs {{ $currentView === $key ? 'bg-white/20' : 'bg-gray-200 dark:bg-gray-600' }}">{{ $data['count'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Date filters - horizontally scrollable on mobile --}}
            @if(($view ?? 'current') !== 'completed')
            <div class="overflow-x-auto -mx-4 px-4 sm:mx-0 sm:px-0 mb-4">
                <div class="flex items-center gap-2 min-w-max pb-1">
                    @php $currentWhen = $when ?? null; @endphp

                    <a href="{{ route('jobs.index', array_merge(request()->except(['when', 'page']), [])) }}"
                       class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium min-h-[40px] whitespace-nowrap
                              {{ !$currentWhen
                                  ? 'bg-gray-800 text-white dark:bg-gray-200 dark:text-gray-800'
                                  : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-400' }}">
                        All
                    </a>

                    <a href="{{ route('jobs.index', array_merge(request()->except('page'), ['when' => 'today'])) }}"
                       class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium min-h-[40px] whitespace-nowrap
                              {{ $currentWhen === 'today'
                                  ? 'bg-green-600 text-white'
                                  : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-400' }}">
                        Today
                        @if(($dateCounts['today'] ?? 0) > 0)
                            <span class="ml-1.5 px-1.5 py-0.5 rounded-full text-xs font-bold {{ $currentWhen === 'today' ? 'bg-white/20' : 'bg-green-100 text-green-700' }}">
                                {{ $dateCounts['today'] }}
                            </span>
                        @endif
                    </a>

                    <a href="{{ route('jobs.index', array_merge(request()->except('page'), ['when' => 'tomorrow'])) }}"
                       class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium min-h-[40px] whitespace-nowrap
                              {{ $currentWhen === 'tomorrow'
                                  ? 'bg-blue-600 text-white'
                                  : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-400' }}">
                        Tomorrow
                        @if(($dateCounts['tomorrow'] ?? 0) > 0)
                            <span class="ml-1.5 px-1.5 py-0.5 rounded-full text-xs font-bold {{ $currentWhen === 'tomorrow' ? 'bg-white/20' : 'bg-blue-100 text-blue-700' }}">
                                {{ $dateCounts['tomorrow'] }}
                            </span>
                        @endif
                    </a>

                    <a href="{{ route('jobs.index', array_merge(request()->except('page'), ['when' => 'week'])) }}"
                       class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium min-h-[40px] whitespace-nowrap
                              {{ $currentWhen === 'week'
                                  ? 'bg-purple-600 text-white'
                                  : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-400' }}">
                        Week
                        @if(($dateCounts['week'] ?? 0) > 0)
                            <span class="ml-1.5 px-1.5 py-0.5 rounded-full text-xs font-bold {{ $currentWhen === 'week' ? 'bg-white/20' : 'bg-purple-100 text-purple-700' }}">
                                {{ $dateCounts['week'] }}
                            </span>
                        @endif
                    </a>

                    <a href="{{ route('jobs.index', array_merge(request()->except('page'), ['when' => 'later'])) }}"
                       class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium min-h-[40px] whitespace-nowrap
                              {{ $currentWhen === 'later'
                                  ? 'bg-amber-600 text-white'
                                  : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-400' }}">
                        Later
                    </a>

                    <a href="{{ route('jobs.index', array_merge(request()->except('page'), ['when' => 'unscheduled'])) }}"
                       class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium min-h-[40px] whitespace-nowrap
                              {{ $currentWhen === 'unscheduled'
                                  ? 'bg-gray-600 text-white'
                                  : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-400' }}">
                        Unsched.
                        @if(($dateCounts['unscheduled'] ?? 0) > 0)
                            <span class="ml-1.5 px-1.5 py-0.5 rounded-full text-xs font-bold {{ $currentWhen === 'unscheduled' ? 'bg-white/20' : 'bg-red-100 text-red-700' }}">
                                {{ $dateCounts['unscheduled'] }}
                            </span>
                        @endif
                    </a>
                </div>
            </div>
            @endif

            {{-- Filters row - compact on mobile --}}
            <div class="flex items-center gap-2 mb-4">
                {{-- Type & Priority filters - dropdown on mobile --}}
                <div class="flex gap-2">
                    <select onchange="window.location.href=this.value"
                            class="px-3 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm min-h-[44px]">
                        <option value="{{ route('jobs.index', array_merge(request()->except('type'), [])) }}" {{ !$type ? 'selected' : '' }}>All</option>
                        <option value="{{ route('jobs.index', array_merge(request()->all(), ['type' => 'ac'])) }}" {{ $type === 'ac' ? 'selected' : '' }}>AC</option>
                        <option value="{{ route('jobs.index', array_merge(request()->all(), ['type' => 'moto'])) }}" {{ $type === 'moto' ? 'selected' : '' }}>Bike</option>
                    </select>

                    <select onchange="window.location.href=this.value"
                            class="hidden sm:block px-3 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm min-h-[44px]">
                        <option value="{{ route('jobs.index', array_merge(request()->except('priority'), [])) }}" {{ !$priority ? 'selected' : '' }}>All Priorities</option>
                        @foreach(\App\Models\Job::getPriorities() as $key => $label)
                            <option value="{{ route('jobs.index', array_merge(request()->all(), ['priority' => $key])) }}" {{ $priority === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Search --}}
                <form action="{{ route('jobs.index') }}" method="GET" class="flex-1">
                    @foreach(request()->except(['search', 'page']) as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                    <div class="relative">
                        <input type="text" name="search" value="{{ $search ?? '' }}"
                               placeholder="Search..."
                               class="w-full px-3 py-2.5 pl-10 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm min-h-[44px]">
                        <svg class="absolute left-3 top-3 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </form>
            </div>

            @if (session('success'))
                <div class="mb-4 px-4 py-3 bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-200 rounded-md text-sm">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Mobile card view - Optimized for touch --}}
            <div class="sm:hidden space-y-3 pb-24">
                @forelse($jobs as $job)
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden border-l-4
                                {{ $job->job_type === 'ac' ? 'border-sky-500' : 'border-orange-500' }}">
                        {{-- Tappable main area --}}
                        <a href="{{ route('jobs.show', $job) }}" class="block p-4">
                            <div class="flex justify-between items-start mb-2">
                                <div class="flex-1 min-w-0 mr-3">
                                    <div class="font-semibold text-base text-gray-900 dark:text-gray-100 truncate">
                                        {{ $job->title ?: $job->customer_name }}
                                    </div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-0.5">
                                        {{ $job->customer_name }}
                                    </div>
                                </div>
                                <div class="flex flex-col items-end gap-1 flex-shrink-0">
                                    <span class="inline-flex px-2 py-1 rounded-lg text-xs font-semibold"
                                          style="background-color: {{ $job->status_color }}20; color: {{ $job->status_color }};">
                                        {{ \App\Models\Job::getStatuses()[$job->status] ?? $job->status }}
                                    </span>
                                    @if($job->priority !== 'normal')
                                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium"
                                              style="background-color: {{ $job->priority_color }}20; color: {{ $job->priority_color }};">
                                            {{ ucfirst($job->priority) }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Schedule & Location info --}}
                            <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm text-gray-500 dark:text-gray-400">
                                @if($job->scheduled_at)
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <span class="font-medium {{ $job->scheduled_at->isToday() ? 'text-green-600 dark:text-green-400' : '' }}">
                                            {{ $job->scheduled_at->isToday() ? 'Today' : ($job->scheduled_at->isTomorrow() ? 'Tomorrow' : $job->scheduled_at->format('M j')) }}
                                        </span>
                                        <span class="ml-1">{{ $job->scheduled_at->format('g:i A') }}</span>
                                    </div>
                                @else
                                    <div class="flex items-center text-amber-600 dark:text-amber-400">
                                        <svg class="w-4 h-4 mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Not scheduled
                                    </div>
                                @endif
                                @if($job->location)
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        </svg>
                                        <span class="truncate max-w-[150px]">{{ $job->location }}</span>
                                    </div>
                                @endif
                            </div>
                        </a>

                        {{-- Quick actions bar --}}
                        <div class="flex items-center justify-between px-4 py-2.5 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-100 dark:border-gray-700">
                            <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                <span class="px-2 py-1 rounded-md font-medium {{ $job->job_type === 'ac' ? 'bg-sky-100 text-sky-700 dark:bg-sky-900/50 dark:text-sky-300' : 'bg-orange-100 text-orange-700 dark:bg-orange-900/50 dark:text-orange-300' }}">
                                    {{ $job->job_type === 'ac' ? 'AC' : 'Bike' }}
                                </span>
                                @if($job->assignees->count())
                                    <span class="text-gray-600 dark:text-gray-400">{{ $job->assignees->first()->name }}</span>
                                    @if($job->assignees->count() > 1)
                                        <span class="text-gray-400">+{{ $job->assignees->count() - 1 }}</span>
                                    @endif
                                @endif
                            </div>
                            {{-- WhatsApp quick action --}}
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $job->customer_phone) }}"
                               class="inline-flex items-center px-3 py-1.5 bg-green-500 text-white rounded-lg text-sm font-medium active:bg-green-600"
                               onclick="event.stopPropagation()">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                </svg>
                                WhatsApp
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-8 text-center">
                        <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <p class="text-gray-500 dark:text-gray-400">No jobs found</p>
                        <a href="{{ route('jobs.create') }}" class="inline-flex items-center mt-4 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium">
                            + Add First Job
                        </a>
                    </div>
                @endforelse
            </div>

            {{-- Desktop table view --}}
            <div class="hidden sm:block bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
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

            <div class="mt-4">
                {{ $jobs->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
