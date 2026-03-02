<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Fault Tickets
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-4 lg:px-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">All Fault Tickets</h3>

                {{-- Search --}}
                <div class="flex flex-1 max-w-md gap-2">
                    <form method="GET" action="{{ route('faults.index') }}" class="flex-1 flex gap-2">
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Search ticket#, customer, title..."
                               class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <input type="hidden" name="tab" value="{{ $tab }}">
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none">
                            Search
                        </button>
                        @if(request('search'))
                            <a href="{{ route('faults.index', ['tab' => $tab]) }}"
                               class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-500 focus:outline-none">
                                Clear
                            </a>
                        @endif
                    </form>
                </div>

                <a href="{{ route('faults.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none whitespace-nowrap">
                    + New Ticket
                </a>
            </div>

            @if (session('success'))
                <div class="mb-4 text-sm text-green-600 dark:text-green-400">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-4 text-sm text-red-600 dark:text-red-400">{{ session('error') }}</div>
            @endif

            {{-- Status Tabs --}}
            <div class="mb-4 flex flex-wrap gap-2">
                @foreach([
                    'open' => 'Open',
                    'in_progress' => 'In Progress',
                    'overdue' => 'Overdue',
                    'resolved' => 'Resolved',
                    'closed' => 'Closed',
                    'all' => 'All',
                ] as $key => $label)
                    @php
                        $tabActive = $tab === $key;
                        $tabClass = $tabActive
                            ? ($key === 'overdue' ? 'bg-red-600 text-white' : 'bg-gray-900 text-white')
                            : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600';
                        $badgeClass = $tabActive
                            ? 'bg-white/20 text-white'
                            : ($key === 'overdue' && $counts[$key] > 0 ? 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300' : 'bg-gray-200 dark:bg-gray-600 text-gray-600 dark:text-gray-300');
                    @endphp
                    <a href="{{ route('faults.index', array_merge(request()->only(['search', 'priority', 'unit']), ['tab' => $key])) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-medium transition {{ $tabClass }}">
                        {{ $label }}
                        <span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full text-xs font-bold {{ $badgeClass }}">
                            {{ $counts[$key] }}
                        </span>
                    </a>
                @endforeach
            </div>

            {{-- Filter Row --}}
            <div class="mb-4 flex flex-wrap gap-2">
                <form method="GET" action="{{ route('faults.index') }}" class="flex flex-wrap gap-2">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <select name="priority" onchange="this.form.submit()"
                            class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Priorities</option>
                        <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                        <option value="normal" {{ request('priority') === 'normal' ? 'selected' : '' }}>Normal</option>
                    </select>
                    <select name="unit" onchange="this.form.submit()"
                            class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Units</option>
                        <option value="moto" {{ request('unit') === 'moto' ? 'selected' : '' }}>Micro Moto</option>
                        <option value="cool" {{ request('unit') === 'cool' ? 'selected' : '' }}>Micro Cool</option>
                    </select>
                </form>
            </div>

            {{-- Desktop Table --}}
            <div class="hidden md:block bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Ticket</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Customer</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Title</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Priority</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Deadline</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Assigned</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($tickets as $ticket)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer {{ $ticket->isOverdue() ? 'bg-red-50 dark:bg-red-900/10' : '' }}"
                                onclick="window.location='{{ route('faults.show', $ticket) }}'">
                                <td class="px-4 py-3 text-sm font-mono font-medium text-gray-900 dark:text-gray-100">
                                    {{ $ticket->ticket_number }}
                                    <span class="block text-xs text-gray-500">{{ $ticket->business_unit === 'moto' ? 'Micro Moto' : 'Micro Cool' }}</span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                    {{ $ticket->customer_name }}
                                    <span class="block text-xs text-gray-500">{{ $ticket->customer_phone }}</span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300 max-w-[200px] truncate">{{ $ticket->title }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold
                                        {{ $ticket->priority === 'urgent' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' }}">
                                        {{ ucfirst($ticket->priority) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $statusColors = [
                                            'open' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                            'in_progress' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                            'resolved' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                            'closed' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
                                        ];
                                    @endphp
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold {{ $statusColors[$ticket->status] ?? '' }}">
                                        {{ str_replace('_', ' ', ucfirst($ticket->status)) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @if($ticket->isOverdue())
                                        <span class="text-red-600 dark:text-red-400 font-semibold">
                                            OVERDUE {{ $ticket->deadline_at->diffForHumans(null, true) }}
                                        </span>
                                    @elseif(!in_array($ticket->status, ['resolved', 'closed']))
                                        <span class="text-gray-600 dark:text-gray-400">
                                            {{ $ticket->deadline_at->diffForHumans() }}
                                        </span>
                                    @elseif($ticket->metSla() === true)
                                        <span class="text-green-600 dark:text-green-400">SLA Met</span>
                                    @elseif($ticket->metSla() === false)
                                        <span class="text-red-600 dark:text-red-400">SLA Missed</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                    {{ $ticket->assignee->name ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No fault tickets found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile Cards --}}
            <div class="md:hidden space-y-3">
                @forelse($tickets as $ticket)
                    <a href="{{ route('faults.show', $ticket) }}"
                       class="block bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4 {{ $ticket->isOverdue() ? 'border-l-4 border-red-500' : 'border-l-4 border-transparent' }}">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <span class="text-sm font-mono font-semibold text-gray-900 dark:text-gray-100">{{ $ticket->ticket_number }}</span>
                                <span class="ml-2 text-xs text-gray-500">{{ $ticket->business_unit === 'moto' ? 'Micro Moto' : 'Micro Cool' }}</span>
                            </div>
                            <div class="flex gap-1">
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold
                                    {{ $ticket->priority === 'urgent' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' }}">
                                    {{ ucfirst($ticket->priority) }}
                                </span>
                                @php
                                    $statusColors = [
                                        'open' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                        'in_progress' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                        'resolved' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                        'closed' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
                                    ];
                                @endphp
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold {{ $statusColors[$ticket->status] ?? '' }}">
                                    {{ str_replace('_', ' ', ucfirst($ticket->status)) }}
                                </span>
                            </div>
                        </div>
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-1">{{ $ticket->title }}</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mb-2">{{ $ticket->customer_name }} &middot; {{ $ticket->customer_phone }}</p>
                        <div class="flex justify-between items-center text-xs">
                            @if($ticket->isOverdue())
                                <span class="text-red-600 dark:text-red-400 font-semibold">OVERDUE {{ $ticket->deadline_at->diffForHumans(null, true) }}</span>
                            @elseif(!in_array($ticket->status, ['resolved', 'closed']))
                                <span class="text-gray-500">Due {{ $ticket->deadline_at->diffForHumans() }}</span>
                            @else
                                <span class="text-gray-500">{{ $ticket->metSla() ? 'SLA Met' : 'SLA Missed' }}</span>
                            @endif
                            <span class="text-gray-500">{{ $ticket->assignee->name ?? 'Unassigned' }}</span>
                        </div>
                    </a>
                @empty
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-8 text-center text-gray-500 dark:text-gray-400">
                        No fault tickets found.
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            <div class="mt-4">
                {{ $tickets->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
