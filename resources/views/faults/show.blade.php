<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
            <div class="flex items-center gap-3">
                <a href="{{ route('faults.index') }}"
                   class="inline-flex items-center justify-center w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-full hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Fault Ticket</div>
                    <h2 class="font-bold text-xl text-gray-800 dark:text-gray-200">
                        {{ $faultTicket->ticket_number }}
                    </h2>
                </div>
            </div>

            <div class="flex items-center gap-2">
                @php
                    $statusColors = [
                        'open' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                        'in_progress' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                        'resolved' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                        'closed' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
                    ];
                @endphp
                <span class="inline-flex px-3 py-1 rounded-full text-sm font-semibold {{ $statusColors[$faultTicket->status] ?? '' }}">
                    {{ str_replace('_', ' ', ucfirst($faultTicket->status)) }}
                </span>
                <span class="inline-flex px-3 py-1 rounded-full text-sm font-semibold
                    {{ $faultTicket->priority === 'urgent' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' }}">
                    {{ ucfirst($faultTicket->priority) }}
                </span>
                <span class="inline-flex px-3 py-1 rounded-full text-sm font-medium
                    {{ $faultTicket->business_unit === 'moto' ? 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200' : 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900 dark:text-cyan-200' }}">
                    {{ $faultTicket->business_unit === 'moto' ? 'Micro Moto' : 'Micro Cool' }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-4 sm:py-6">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-3 sm:space-y-4">

            {{-- Alerts --}}
            @if (session('success'))
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                    <p class="text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                    <p class="text-sm text-red-800 dark:text-red-200">{{ session('error') }}</p>
                </div>
            @endif

            {{-- Overdue Warning --}}
            @if($faultTicket->isOverdue())
                <div class="bg-red-50 dark:bg-red-900/20 border-2 border-red-300 dark:border-red-700 rounded-lg p-4">
                    <div class="flex items-center gap-3">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-bold text-red-800 dark:text-red-200">OVERDUE</p>
                            <p class="text-sm text-red-700 dark:text-red-300">
                                Deadline was {{ $faultTicket->deadline_at->format('M d, Y g:i A') }} &mdash; overdue by {{ $faultTicket->deadline_at->diffForHumans(null, true) }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                {{-- Main Content --}}
                <div class="lg:col-span-2 space-y-4">
                    {{-- Fault Details --}}
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ $faultTicket->title }}</h3>
                        <div class="prose prose-sm dark:prose-invert max-w-none text-gray-700 dark:text-gray-300">
                            {!! nl2br(e($faultTicket->description)) !!}
                        </div>
                    </div>

                    {{-- Resolution Notes (if resolved) --}}
                    @if($faultTicket->resolution_notes)
                        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 shadow-sm sm:rounded-lg p-4 sm:p-6">
                            <h3 class="text-sm font-semibold text-green-800 dark:text-green-200 mb-2">Resolution Notes</h3>
                            <div class="text-sm text-green-700 dark:text-green-300">
                                {!! nl2br(e($faultTicket->resolution_notes)) !!}
                            </div>
                            @if($faultTicket->resolver)
                                <p class="text-xs text-green-600 dark:text-green-400 mt-3">
                                    Resolved by {{ $faultTicket->resolver->name }} on {{ $faultTicket->resolved_at->format('M d, Y g:i A') }}
                                    @if($faultTicket->getResolutionHours() !== null)
                                        ({{ $faultTicket->getResolutionHours() }}h)
                                    @endif
                                    &mdash;
                                    @if($faultTicket->metSla())
                                        <span class="font-semibold text-green-700 dark:text-green-300">SLA Met</span>
                                    @else
                                        <span class="font-semibold text-red-700 dark:text-red-300">SLA Missed</span>
                                    @endif
                                </p>
                            @endif
                        </div>
                    @endif

                    {{-- Action Panel --}}
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4 uppercase tracking-wide">Actions</h3>

                        @if($faultTicket->status === 'open')
                            <div class="space-y-3">
                                {{-- Assign --}}
                                <form method="POST" action="{{ route('faults.update', $faultTicket) }}" class="flex flex-col sm:flex-row gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="action" value="assign">
                                    <select name="assigned_to" required
                                            class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Select staff to assign...</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ $faultTicket->assigned_to == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit"
                                            class="px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700 transition whitespace-nowrap">
                                        Assign
                                    </button>
                                </form>

                                {{-- Start Work --}}
                                <form method="POST" action="{{ route('faults.update', $faultTicket) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="action" value="start_work">
                                    <button type="submit"
                                            class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition">
                                        Start Work
                                    </button>
                                </form>
                            </div>
                        @endif

                        @if($faultTicket->status === 'in_progress')
                            <form method="POST" action="{{ route('faults.update', $faultTicket) }}" x-data="{ showNotes: false }" class="space-y-3">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="action" value="resolve">

                                <button type="button" @click="showNotes = !showNotes"
                                        class="w-full sm:w-auto px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 transition">
                                    Resolve Ticket
                                </button>

                                <div x-show="showNotes" x-cloak x-transition class="space-y-3">
                                    <textarea name="resolution_notes" rows="3" required
                                              placeholder="Describe what was done to resolve the fault..."
                                              class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                    @error('resolution_notes')
                                        <p class="text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                    <button type="submit"
                                            class="px-4 py-2 bg-green-700 text-white text-sm font-medium rounded-md hover:bg-green-800 transition">
                                        Confirm Resolution
                                    </button>
                                </div>
                            </form>
                        @endif

                        @if($faultTicket->status === 'resolved')
                            <div class="flex flex-wrap gap-2">
                                @if(Auth::user()->hasAnyRole(['admin', 'manager']))
                                    <form method="POST" action="{{ route('faults.update', $faultTicket) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="action" value="close">
                                        <button type="submit"
                                                class="px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700 transition">
                                            Close Ticket
                                        </button>
                                    </form>
                                @endif

                                <form method="POST" action="{{ route('faults.update', $faultTicket) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="action" value="reopen">
                                    <button type="submit"
                                            class="px-4 py-2 bg-yellow-600 text-white text-sm font-medium rounded-md hover:bg-yellow-700 transition">
                                        Reopen
                                    </button>
                                </form>
                            </div>
                        @endif

                        @if($faultTicket->status === 'closed')
                            <p class="text-sm text-gray-500 dark:text-gray-400">This ticket is closed. No further actions available.</p>
                        @endif

                        {{-- Delete (Admin only) --}}
                        @if(Auth::user()->isAdmin())
                            <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <form method="POST" action="{{ route('faults.destroy', $faultTicket) }}"
                                      onsubmit="return confirm('Are you sure you want to delete this ticket? This cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 transition">
                                        Delete Ticket
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="space-y-4">
                    {{-- Deadline --}}
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4">
                        <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Deadline</h4>
                        @if(!in_array($faultTicket->status, ['resolved', 'closed']))
                            @if($faultTicket->isOverdue())
                                <p class="text-lg font-bold text-red-600 dark:text-red-400">
                                    OVERDUE by {{ $faultTicket->deadline_at->diffForHumans(null, true) }}
                                </p>
                            @else
                                <p class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                    Due {{ $faultTicket->deadline_at->diffForHumans() }}
                                </p>
                            @endif
                        @else
                            @if($faultTicket->metSla())
                                <p class="text-lg font-bold text-green-600 dark:text-green-400">SLA Met</p>
                            @else
                                <p class="text-lg font-bold text-red-600 dark:text-red-400">SLA Missed</p>
                            @endif
                        @endif
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            {{ $faultTicket->deadline_at->format('M d, Y g:i A') }}
                        </p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ $faultTicket->priority === 'urgent' ? '24h SLA' : '48h SLA' }}
                        </p>
                    </div>

                    {{-- Customer Info --}}
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4">
                        <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Customer</h4>
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $faultTicket->customer_name }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $faultTicket->customer_phone }}</p>
                        @if($faultTicket->customer)
                            <a href="{{ route('customers.show', $faultTicket->customer) }}"
                               class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline mt-1 inline-block">
                                View Customer Profile
                            </a>
                        @endif
                    </div>

                    {{-- Related Job --}}
                    @if($faultTicket->job)
                        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4">
                            <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Related Job</h4>
                            <a href="{{ route('jobs.show', $faultTicket->job) }}"
                               class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline font-medium">
                                Job #{{ $faultTicket->job->id }}
                            </a>
                        </div>
                    @endif

                    {{-- Staff Info --}}
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4">
                        <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Staff</h4>
                        <div class="space-y-2 text-sm">
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Created by:</span>
                                <span class="text-gray-900 dark:text-gray-100 font-medium ml-1">{{ $faultTicket->creator->name ?? '—' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Assigned to:</span>
                                <span class="text-gray-900 dark:text-gray-100 font-medium ml-1">{{ $faultTicket->assignee->name ?? 'Unassigned' }}</span>
                            </div>
                            @if($faultTicket->resolver)
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Resolved by:</span>
                                    <span class="text-gray-900 dark:text-gray-100 font-medium ml-1">{{ $faultTicket->resolver->name }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Timestamps --}}
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4">
                        <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Timeline</h4>
                        <div class="space-y-2 text-xs text-gray-500 dark:text-gray-400">
                            <div>Created: {{ $faultTicket->created_at->format('M d, Y g:i A') }}</div>
                            @if($faultTicket->resolved_at)
                                <div>Resolved: {{ $faultTicket->resolved_at->format('M d, Y g:i A') }}</div>
                            @endif
                            <div>Last updated: {{ $faultTicket->updated_at->format('M d, Y g:i A') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
