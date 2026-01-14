<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Leads
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-4 lg:px-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    All Leads
                </h3>

                {{-- Search Bar --}}
                <div class="flex flex-1 max-w-md gap-2">
                    <form method="GET" action="{{ route('leads.index') }}" class="flex-1 flex gap-2">
                        <input type="text"
                               name="search"
                               value="{{ $search ?? '' }}"
                               placeholder="Search by name, phone, email..."
                               class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <input type="hidden" name="status" value="{{ $statusFilter }}">
                        <input type="hidden" name="priority" value="{{ $priorityFilter }}">
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none">
                            Search
                        </button>
                        @if($search ?? false)
                            <a href="{{ route('leads.index', ['status' => $statusFilter, 'priority' => $priorityFilter]) }}"
                               class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-500 focus:outline-none">
                                Clear
                            </a>
                        @endif
                    </form>
                </div>

                <a href="{{ route('leads.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none whitespace-nowrap">
                    + New Lead
                </a>
            </div>

            @if (session('success'))
                <div class="mb-4 text-sm text-green-600 dark:text-green-400">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 text-sm text-red-600 dark:text-red-400">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Status Filter Tabs --}}
            <div class="mb-4 flex flex-wrap gap-2">
                @foreach(['all' => 'All', 'new' => 'New', 'contacted' => 'Contacted', 'interested' => 'Interested', 'qualified' => 'Qualified', 'converted' => 'Converted', 'lost' => 'Lost'] as $status => $label)
                    <a href="{{ route('leads.index', ['status' => $status, 'priority' => $priorityFilter, 'search' => $search]) }}"
                       class="px-3 py-1.5 rounded-md text-xs font-medium transition
                           {{ $statusFilter === $status
                               ? 'bg-indigo-600 text-white'
                               : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600' }}">
                        {{ $label }} ({{ $statusCounts[$status] ?? 0 }})
                    </a>
                @endforeach
            </div>

            {{-- Priority Filter --}}
            <div class="mb-4 flex gap-2 items-center">
                <span class="text-sm text-gray-600 dark:text-gray-400">Priority:</span>
                @foreach(['all' => 'All', 'high' => 'High', 'medium' => 'Medium', 'low' => 'Low'] as $priority => $label)
                    <a href="{{ route('leads.index', ['status' => $statusFilter, 'priority' => $priority, 'search' => $search]) }}"
                       class="px-2 py-1 rounded text-xs font-medium transition
                           {{ $priorityFilter === $priority
                               ? 'bg-indigo-600 text-white'
                               : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            {{-- Mobile Card View --}}
            <div class="block md:hidden space-y-3">
                @forelse($leads as $lead)
                    <a href="{{ route('leads.show', $lead) }}" class="block bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 active:bg-gray-50 dark:active:bg-gray-700 transition touch-manipulation border-l-4
                        {{ $lead->priority === 'high' ? 'border-red-500' : ($lead->priority === 'medium' ? 'border-yellow-500' : 'border-gray-300 dark:border-gray-600') }}">

                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1">
                                <div class="font-semibold text-base text-gray-900 dark:text-gray-100 mb-1">{{ $lead->name }}</div>
                                <a href="tel:{{ $lead->phone }}" onclick="event.stopPropagation()" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                                    {{ $lead->phone }}
                                </a>
                                @if($lead->email)
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $lead->email }}</div>
                                @endif
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <span class="inline-flex px-2 py-1 rounded text-xs font-medium
                                    {{ $lead->status === 'new' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' :
                                       ($lead->status === 'contacted' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' :
                                       ($lead->status === 'interested' ? 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200' :
                                       ($lead->status === 'qualified' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' :
                                       ($lead->status === 'converted' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' :
                                       'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200')))) }}">
                                    {{ ucfirst($lead->status) }}
                                </span>
                                <span class="inline-flex px-2 py-1 rounded text-xs font-medium
                                    {{ $lead->priority === 'high' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' :
                                       ($lead->priority === 'medium' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' :
                                       'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200') }}">
                                    {{ ucfirst($lead->priority) }}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mb-2">
                            <div class="flex items-center gap-4">
                                <span class="inline-flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"></path>
                                    </svg>
                                    {{ ucfirst($lead->source) }}
                                </span>
                                <span>{{ ucfirst($lead->interested_in) }}</span>
                            </div>
                            <span class="text-gray-400">{{ $lead->created_at?->diffForHumans() }}</span>
                        </div>

                        @if($lead->follow_up_date)
                            <div class="flex items-center gap-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                                <svg class="w-4 h-4 {{ $lead->follow_up_date->isPast() && $lead->status !== 'converted' && $lead->status !== 'lost' ? 'text-red-600 dark:text-red-400' : 'text-gray-400' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-xs {{ $lead->follow_up_date->isPast() && $lead->status !== 'converted' && $lead->status !== 'lost' ? 'text-red-600 dark:text-red-400 font-bold' : 'text-gray-600 dark:text-gray-400' }}">
                                    Follow-up: {{ $lead->follow_up_date->format('M d, Y') }}
                                    @if($lead->follow_up_date->isPast() && $lead->status !== 'converted' && $lead->status !== 'lost')
                                        <span class="ml-1 font-bold">• OVERDUE</span>
                                    @elseif($lead->follow_up_date->isToday() && $lead->status !== 'converted' && $lead->status !== 'lost')
                                        <span class="ml-1 text-orange-600 dark:text-orange-400 font-medium">• TODAY</span>
                                    @endif
                                </span>
                            </div>
                        @endif
                    </a>
                @empty
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-8 text-center">
                        <p class="text-gray-500 dark:text-gray-400">No leads found.</p>
                    </div>
                @endforelse
            </div>

            {{-- Desktop Table View --}}
            <div class="hidden md:block bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Name</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Phone</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Source</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Priority</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Follow Up</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Created</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($leads as $lead)
                        <tr onclick="window.location.href='{{ route('leads.show', $lead) }}'"
                            class="touch-row cursor-pointer transition-colors duration-150 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 active:bg-indigo-100 dark:active:bg-indigo-900/30 touch-manipulation">
                            <td class="px-4 py-4">
                                <div class="text-gray-900 dark:text-gray-100 font-medium">{{ $lead->name }}</div>
                                @if($lead->email)
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $lead->email }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-300">
                                {{ $lead->phone }}
                            </td>
                            <td class="px-4 py-4">
                                <span class="inline-flex px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                    {{ ucfirst($lead->source) }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <span class="inline-flex px-2 py-1 rounded text-xs font-medium
                                    {{ $lead->status === 'new' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' :
                                       ($lead->status === 'contacted' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' :
                                       ($lead->status === 'interested' ? 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200' :
                                       ($lead->status === 'qualified' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' :
                                       ($lead->status === 'converted' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' :
                                       'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200')))) }}">
                                    {{ ucfirst($lead->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <span class="inline-flex px-2 py-1 rounded text-xs font-medium
                                    {{ $lead->priority === 'high' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' :
                                       ($lead->priority === 'medium' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' :
                                       'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200') }}">
                                    {{ ucfirst($lead->priority) }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-xs {{ $lead->follow_up_date && $lead->follow_up_date->isPast() && $lead->status !== 'converted' && $lead->status !== 'lost' ? 'text-red-600 dark:text-red-400 font-semibold' : 'text-gray-500 dark:text-gray-400' }}">
                                @if($lead->follow_up_date)
                                    <div class="{{ $lead->follow_up_date->isPast() && $lead->status !== 'converted' && $lead->status !== 'lost' ? 'font-bold' : '' }}">
                                        {{ $lead->follow_up_date->format('Y-m-d') }}
                                    </div>
                                    @if($lead->follow_up_date->isPast() && $lead->status !== 'converted' && $lead->status !== 'lost')
                                        <div class="flex items-center gap-1 text-red-600 dark:text-red-400 font-bold">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                            </svg>
                                            OVERDUE
                                        </div>
                                    @elseif($lead->follow_up_date->isToday() && $lead->status !== 'converted' && $lead->status !== 'lost')
                                        <div class="text-orange-600 dark:text-orange-400 font-medium">Today</div>
                                    @endif
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-xs text-gray-500 dark:text-gray-400">
                                <div>{{ $lead->created_at?->format('Y-m-d') }}</div>
                                <div class="text-gray-400 dark:text-gray-500">{{ $lead->created_at?->diffForHumans() }}</div>
                            </td>
                            <td class="px-4 py-4" onclick="event.stopPropagation()">
                                @if(Auth::user()->canDelete())
                                    <form action="{{ route('leads.destroy', $lead) }}" method="POST"
                                          onsubmit="return confirm('Are you sure you want to delete this lead?')">
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
                            <td colspan="8" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                No leads found.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $leads->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
