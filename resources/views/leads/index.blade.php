<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Leads
        </h2>
    </x-slot>

    <div class="py-6" x-data="{
        selectedIds: [],
        selectAll: false,
        toggleAll() {
            if (this.selectAll) {
                this.selectedIds = {{ $leads->pluck('id') }};
            } else {
                this.selectedIds = [];
            }
        },
        toggleId(id) {
            const idx = this.selectedIds.indexOf(id);
            if (idx > -1) {
                this.selectedIds.splice(idx, 1);
            } else {
                this.selectedIds.push(id);
            }
            this.selectAll = this.selectedIds.length === {{ $leads->count() }};
        },
        isSelected(id) {
            return this.selectedIds.includes(id);
        },
        openMenu: null
    }">
        <div class="max-w-7xl mx-auto sm:px-4 lg:px-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    {{ $statusFilter === 'archived' ? 'Archived Leads' : 'All Leads' }}
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
            @if (session('warning'))
                <div class="mb-4 text-sm text-yellow-600 dark:text-yellow-400">
                    {{ session('warning') }}
                </div>
            @endif

            {{-- Bulk Action Bar --}}
            <div x-show="selectedIds.length > 0" x-cloak
                 class="mb-4 bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-700 rounded-lg p-3 flex flex-wrap items-center gap-3">
                <span class="text-sm font-medium text-indigo-700 dark:text-indigo-300" x-text="selectedIds.length + ' selected'"></span>

                <form method="POST" action="{{ route('leads.bulk-action') }}" class="inline">
                    @csrf
                    <template x-for="id in selectedIds" :key="id">
                        <input type="hidden" name="lead_ids[]" :value="id">
                    </template>
                    <input type="hidden" name="action" value="archive">
                    <button type="submit"
                            class="inline-flex items-center px-3 py-1.5 bg-gray-600 text-white text-xs font-medium rounded-md hover:bg-gray-700">
                        Archive Selected
                    </button>
                </form>

                @if(Auth::user()->canDelete())
                    <form method="POST" action="{{ route('leads.bulk-action') }}" class="inline"
                          onsubmit="return confirm('Permanently delete selected leads? This cannot be undone.')">
                        @csrf
                        <template x-for="id in selectedIds" :key="id">
                            <input type="hidden" name="lead_ids[]" :value="id">
                        </template>
                        <input type="hidden" name="action" value="delete">
                        <button type="submit"
                                class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded-md hover:bg-red-700">
                            Delete Selected
                        </button>
                    </form>
                @endif

                <button @click="selectedIds = []; selectAll = false"
                        class="text-xs text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 underline">
                    Clear selection
                </button>
            </div>

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
                {{-- Archived Tab --}}
                <a href="{{ route('leads.index', ['status' => 'archived', 'search' => $search]) }}"
                   class="px-3 py-1.5 rounded-md text-xs font-medium transition
                       {{ $statusFilter === 'archived'
                           ? 'bg-gray-500 text-white'
                           : 'bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400 hover:bg-gray-300 dark:hover:bg-gray-600' }}">
                    Archived ({{ $statusCounts['archived'] ?? 0 }})
                </a>
            </div>

            {{-- Priority Filter (hide on archived tab) --}}
            @if($statusFilter !== 'archived')
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
            @endif

            {{-- Mobile Card View --}}
            <div class="block md:hidden space-y-3">
                @forelse($leads as $lead)
                    <div class="flex items-start gap-3">
                        {{-- Checkbox --}}
                        <div class="pt-4 flex-shrink-0">
                            <input type="checkbox"
                                   :checked="isSelected({{ $lead->id }})"
                                   @change="toggleId({{ $lead->id }})"
                                   class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                        </div>
                        <a href="{{ route('leads.show', $lead) }}" class="flex-1 block bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 active:bg-gray-50 dark:active:bg-gray-700 transition touch-manipulation border-l-4
                            {{ $lead->priority === 'high' ? 'border-red-500' : ($lead->priority === 'medium' ? 'border-yellow-500' : 'border-gray-300 dark:border-gray-600') }}">

                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1">
                                    <div class="font-semibold text-base text-gray-900 dark:text-gray-100 mb-1">{{ $lead->name }}</div>
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $lead->phone) }}" onclick="event.stopPropagation()" target="_blank" class="inline-flex items-center gap-1 text-sm text-green-600 dark:text-green-400 hover:underline">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                        </svg>
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

                            @if($lead->assignedUser)
                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                                    <span class="font-medium">Assigned:</span> {{ $lead->assignedUser->name }}
                                </div>
                            @endif

                            @if($lead->follow_up_date)
                                <div class="flex items-center gap-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                                    <svg class="w-4 h-4 {{ $lead->follow_up_is_overdue ? 'text-red-600 dark:text-red-400' : 'text-gray-400' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="text-xs {{ $lead->follow_up_is_overdue ? 'text-red-600 dark:text-red-400 font-bold' : ($lead->follow_up_display === 'Today' ? 'text-orange-600 dark:text-orange-400 font-medium' : 'text-gray-600 dark:text-gray-400') }}">
                                        Follow-up: {{ $lead->follow_up_display }}
                                    </span>
                                </div>
                            @endif
                        </a>
                    </div>
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
                        <th class="px-3 py-3 text-left">
                            <input type="checkbox" x-model="selectAll" @change="toggleAll()"
                                   class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                        </th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Name</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Phone</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Source</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Priority</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Assigned</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Follow Up</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Created</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400"></th>
                    </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($leads as $lead)
                        <tr onclick="window.location.href='{{ route('leads.show', $lead) }}'"
                            class="touch-row cursor-pointer transition-colors duration-150 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 active:bg-indigo-100 dark:active:bg-indigo-900/30 touch-manipulation">
                            <td class="px-3 py-4" onclick="event.stopPropagation()">
                                <input type="checkbox"
                                       :checked="isSelected({{ $lead->id }})"
                                       @change="toggleId({{ $lead->id }})"
                                       class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                            </td>
                            <td class="px-4 py-4">
                                <div class="text-gray-900 dark:text-gray-100 font-medium">{{ $lead->name }}</div>
                                @if($lead->email)
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $lead->email }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-sm" onclick="event.stopPropagation()">
                                <a href="tel:{{ $lead->phone }}" class="inline-flex items-center gap-1 text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                    {{ $lead->phone }}
                                </a>
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
                            <td class="px-4 py-4 text-xs text-gray-600 dark:text-gray-400">
                                {{ $lead->assignedUser?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-4 text-xs">
                                @if($lead->follow_up_display)
                                    <span class="{{ $lead->follow_up_is_overdue ? 'text-red-600 dark:text-red-400 font-bold' : ($lead->follow_up_display === 'Today' ? 'text-orange-600 dark:text-orange-400 font-medium' : 'text-gray-500 dark:text-gray-400') }}">
                                        {{ $lead->follow_up_display }}
                                    </span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-xs text-gray-500 dark:text-gray-400">
                                <div>{{ $lead->created_at?->diffForHumans() }}</div>
                            </td>
                            <td class="px-4 py-4" onclick="event.stopPropagation()">
                                {{-- 3-dot dropdown menu --}}
                                <div x-data="{ btnEl: null }" class="relative">
                                    <button x-ref="menubtn"
                                            @click.stop="btnEl = $refs.menubtn; openMenu = (openMenu === {{ $lead->id }}) ? null : {{ $lead->id }}"
                                            class="p-1 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                                        </svg>
                                    </button>

                                    <template x-teleport="body">
                                        <div x-show="openMenu === {{ $lead->id }}"
                                             x-cloak
                                             @click.outside="openMenu = null"
                                             x-init="$watch('openMenu', val => {
                                                 if (val === {{ $lead->id }} && btnEl) {
                                                     const rect = btnEl.getBoundingClientRect();
                                                     const spaceBelow = window.innerHeight - rect.bottom;
                                                     $el.style.position = 'fixed';
                                                     $el.style.left = (rect.right - 192) + 'px';
                                                     if (spaceBelow < 200) {
                                                         $el.style.bottom = (window.innerHeight - rect.top + 4) + 'px';
                                                         $el.style.top = 'auto';
                                                     } else {
                                                         $el.style.top = (rect.bottom + 4) + 'px';
                                                         $el.style.bottom = 'auto';
                                                     }
                                                 }
                                             })"
                                             class="w-48 bg-white dark:bg-gray-700 rounded-md shadow-lg border border-gray-200 dark:border-gray-600 z-[9999] py-1">

                                            @if($statusFilter === 'archived')
                                                <form method="POST" action="{{ route('leads.unarchive', $lead) }}">
                                                    @csrf
                                                    <button type="submit" @click="openMenu = null"
                                                            class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">
                                                        Restore from Archive
                                                    </button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('leads.archive', $lead) }}">
                                                    @csrf
                                                    <button type="submit" @click="openMenu = null"
                                                            class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">
                                                        Archive
                                                    </button>
                                                </form>
                                            @endif

                                            @if(!in_array($lead->status, ['converted', 'lost']))
                                                <a href="{{ route('leads.show', $lead) }}?mark_as_lost=1" @click="openMenu = null"
                                                   class="block px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-600">
                                                    Mark as Lost
                                                </a>
                                            @endif

                                            @if(Auth::user()->canDelete())
                                                <form method="POST" action="{{ route('leads.destroy', $lead) }}"
                                                      onsubmit="return confirm('Permanently delete this lead? This cannot be undone.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" @click="openMenu = null"
                                                            class="w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">
                                                        Delete Permanently
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </template>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
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
