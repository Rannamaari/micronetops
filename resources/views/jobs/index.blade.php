{{-- resources/views/jobs/index.blade.php --}}

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Jobs
        </h2>
    </x-slot>

    <style>
        /* Touch-friendly improvements */
        @media (max-width: 640px) {
            .touch-row {
                min-height: 60px;
            }
        }
        .touch-manipulation {
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
        }
    </style>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-4 lg:px-8">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    All Jobs
                </h3>
                <a href="{{ route('jobs.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none">
                    + New Job
                </a>
            </div>

            {{-- Status filter tabs --}}
            <div class="flex flex-wrap items-center gap-2 mb-4 -mx-2">
                @php
                    $currentStatus = $status ?? request('status', 'pending');
                @endphp

                @php
                    $tabs = [
                        'pending'     => 'Pending',
                        'in_progress' => 'In Progress',
                        'completed'   => 'Completed',
                        'all'         => 'All',
                    ];
                @endphp

                @foreach($tabs as $key => $label)
                    @php
                        $active = $currentStatus === $key || ($currentStatus === null && $key === 'pending');
                        $count  = $statusCounts[$key] ?? null;
                    @endphp

                    <a href="{{ route('jobs.index', array_merge(['status' => $key], request('date') ? ['date' => request('date')] : [])) }}"
                       class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium min-h-[44px] touch-manipulation
                              {{ $active
                                  ? 'bg-indigo-600 text-white'
                                  : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600' }}">
                        <span>{{ $label }}</span>
                        @if($count !== null)
                            <span class="ml-2 text-xs opacity-80">({{ $count }})</span>
                        @endif
                    </a>
                @endforeach
            </div>

            {{-- Date filter tabs --}}
            <div class="flex flex-wrap items-center gap-2 mb-4 -mx-2">
                @php
                    $currentDateFilter = $dateFilter ?? request('date');
                    $dateTabs = [
                        'today'          => 'Today',
                        'yesterday'      => 'Yesterday',
                        'previous_month' => 'Previous Month',
                        'current_month'  => 'Current Month',
                    ];
                @endphp

                @foreach($dateTabs as $key => $label)
                    @php
                        $active = $currentDateFilter === $key;
                    @endphp

                    <a href="{{ route('jobs.index', array_merge(['status' => $status ?? 'pending'], ['date' => $key])) }}"
                       class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium min-h-[44px] touch-manipulation
                              {{ $active
                                  ? 'bg-green-600 text-white'
                                  : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600' }}">
                        <span>{{ $label }}</span>
                    </a>
                @endforeach

                @if($currentDateFilter)
                    <a href="{{ route('jobs.index', ['status' => $status ?? 'pending']) }}"
                       class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium min-h-[44px] touch-manipulation
                              bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-600 dark:text-gray-300 dark:hover:bg-gray-500">
                        <span>Clear Date Filter</span>
                    </a>
                @endif
            </div>

            @if (session('success'))
                <div class="mb-4 text-sm text-green-600 dark:text-green-400">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">ID</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Type</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Customer</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Total</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Created</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($jobs as $job)
                        <tr onclick="window.location.href='{{ route('jobs.show', $job) }}'"
                            class="touch-row cursor-pointer transition-colors duration-150 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 active:bg-indigo-100 dark:active:bg-indigo-900/30 touch-manipulation">
                            <td class="px-4 py-4 text-gray-900 dark:text-gray-100 font-medium">
                                #{{ $job->id }}
                            </td>
                            <td class="px-4 py-4">
                                <span class="inline-flex px-2 py-1 rounded text-xs font-medium
                                    {{ $job->job_type === 'moto' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' }}">
                                    {{ strtoupper($job->job_type) }}
                                </span>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    {{ $job->job_category }}
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $job->customer?->name ?? '—' }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $job->customer?->phone }}
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <span class="inline-flex px-2 py-1 rounded text-xs font-medium
                                    {{ $job->status === 'completed' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                                       ($job->status === 'in_progress' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 
                                       'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200') }}">
                                    {{ ucfirst($job->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-sm font-semibold text-gray-900 dark:text-gray-100">
                                {{ number_format($job->total_amount, 2) }} MVR
                            </td>
                            <td class="px-4 py-4 text-xs text-gray-500 dark:text-gray-400">
                                {{ $job->created_at?->format('Y-m-d H:i') }}
                            </td>
                            <td class="px-4 py-4" onclick="event.stopPropagation()">
                                @if(Auth::user()->canDelete())
                                    {{-- Admin can delete any job --}}
                                    <form action="{{ route('jobs.destroy', $job) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this job?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 font-medium text-xs">
                                            Delete
                                        </button>
                                    </form>
                                @else
                                    <span class="text-gray-400 dark:text-gray-600 text-xs">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                No jobs yet.
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

