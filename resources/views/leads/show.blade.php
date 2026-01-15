<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
            <div class="flex items-center gap-3">
                <a href="{{ route('leads.index') }}"
                   class="inline-flex items-center justify-center w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-full hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Lead</div>
                    <h2 class="font-bold text-xl text-gray-800 dark:text-gray-200">
                        {{ $lead->name }}
                    </h2>
                </div>
            </div>

            {{-- Convert to Customer Button --}}
            @if($lead->status !== 'converted')
                <div class="hidden sm:block">
                    <form action="{{ route('leads.convert', $lead) }}" method="POST"
                          onsubmit="return confirm('Convert this lead to a customer?')">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 rounded-lg font-semibold text-sm text-white shadow-sm transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Convert to Customer</span>
                        </button>
                    </form>
                </div>
            @endif
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
            @if (session('warning'))
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                    <p class="text-sm text-yellow-800 dark:text-yellow-200">{{ session('warning') }}</p>
                </div>
            @endif

            {{-- Call Attempts Warning --}}
            @if($lead->call_attempts >= 2 && $lead->status !== 'converted' && $lead->status !== 'lost')
                <div class="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-orange-600 dark:text-orange-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <div>
                                <div class="text-sm font-semibold text-orange-900 dark:text-orange-100">
                                    {{ $lead->call_attempts }} call attempts made
                                </div>
                                <div class="text-sm text-orange-800 dark:text-orange-200 mt-1">
                                    This lead has been called {{ $lead->call_attempts }} times. Consider marking as lost if customer is not interested.
                                </div>
                            </div>
                        </div>
                        <button onclick="document.getElementById('mark-lost-modal').classList.remove('hidden')"
                                class="inline-flex items-center px-3 py-1.5 bg-orange-600 hover:bg-orange-700 rounded-md text-xs font-medium text-white transition whitespace-nowrap">
                            Mark as Lost
                        </button>
                    </div>
                </div>
            @endif

            {{-- Do Not Contact Warning --}}
            @if($lead->do_not_contact)
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                        </svg>
                        <div class="flex-1">
                            <div class="text-sm font-semibold text-red-900 dark:text-red-100">
                                DO NOT CONTACT
                            </div>
                            <div class="text-sm text-red-800 dark:text-red-200">
                                Customer has requested not to be contacted.
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Convert Button - Mobile --}}
            @if($lead->status !== 'converted')
                <div class="block sm:hidden">
                    <form action="{{ route('leads.convert', $lead) }}" method="POST"
                          onsubmit="return confirm('Convert this lead to a customer?')">
                        @csrf
                        <button type="submit"
                                class="flex items-center justify-center gap-2 w-full py-4 bg-green-600 hover:bg-green-700 rounded-xl font-semibold text-white shadow-lg transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Convert to Customer</span>
                        </button>
                    </form>
                </div>
            @endif

            {{-- Lead Info Card --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden">
                <div class="p-5 sm:p-4">
                    <div class="flex justify-between items-start mb-4 sm:mb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-14 h-14 sm:w-12 sm:h-12 bg-indigo-100 dark:bg-indigo-900 rounded-full flex items-center justify-center">
                                <svg class="w-7 h-7 sm:w-6 sm:h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Lead Name</div>
                                <div class="text-lg sm:text-base font-bold text-gray-900 dark:text-gray-100">
                                    {{ $lead->name }}
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('leads.edit', $lead) }}"
                           class="inline-flex items-center gap-1 px-3 py-2 sm:px-2.5 sm:py-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            <span class="hidden sm:inline">Edit</span>
                        </a>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="flex items-start gap-3 p-3 sm:p-2.5 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <svg class="w-5 h-5 sm:w-4 sm:h-4 text-green-600 dark:text-green-400 mt-0.5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                            </svg>
                            <div class="flex-1">
                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">WhatsApp</div>
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $lead->phone) }}" target="_blank" class="text-base sm:text-sm font-semibold text-green-600 dark:text-green-400 hover:text-green-700 dark:hover:text-green-300">
                                    {{ $lead->phone }}
                                </a>
                            </div>
                        </div>

                        @if($lead->email)
                            <div class="flex items-start gap-3 p-3 sm:p-2.5 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                <svg class="w-5 h-5 sm:w-4 sm:h-4 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                <div class="flex-1">
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Email</div>
                                    <a href="mailto:{{ $lead->email }}" class="text-base sm:text-sm font-semibold text-gray-900 dark:text-gray-100 hover:text-indigo-600 dark:hover:text-indigo-400">
                                        {{ $lead->email }}
                                    </a>
                                </div>
                            </div>
                        @endif

                        @if($lead->address)
                            <div class="flex items-start gap-3 p-3 sm:p-2.5 bg-gray-50 dark:bg-gray-700/50 rounded-lg sm:col-span-2">
                                <svg class="w-5 h-5 sm:w-4 sm:h-4 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <div class="flex-1">
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Address</div>
                                    <div class="text-base sm:text-sm text-gray-900 dark:text-gray-100">
                                        {{ $lead->address }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Quick Status Update --}}
            @if($lead->status !== 'converted' && $lead->status !== 'lost')
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-4">
                    <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Quick Status Update</div>
                    <div class="flex flex-wrap gap-2">
                        @foreach(['new' => 'New', 'contacted' => 'Contacted', 'interested' => 'Interested', 'qualified' => 'Qualified'] as $statusValue => $statusLabel)
                            <form action="{{ route('leads.update-status', $lead) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="{{ $statusValue }}">
                                <button type="submit"
                                        class="px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200
                                        {{ $lead->status === $statusValue
                                            ? 'bg-indigo-600 text-white shadow-sm cursor-default'
                                            : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}"
                                        {{ $lead->status === $statusValue ? 'disabled' : '' }}>
                                    @if($lead->status === $statusValue)
                                        <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                    @endif
                                    {{ $statusLabel }}
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Lead Details Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-4">
                    <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Current Status</div>
                    <span class="inline-flex px-3 py-1.5 rounded-lg text-sm font-semibold
                        {{ $lead->status === 'new' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' :
                           ($lead->status === 'contacted' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' :
                           ($lead->status === 'interested' ? 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200' :
                           ($lead->status === 'qualified' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' :
                           ($lead->status === 'converted' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' :
                           'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200')))) }}">
                        {{ ucfirst($lead->status) }}
                    </span>
                </div>

                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-4">
                    <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Priority</div>
                    <span class="inline-flex px-3 py-1.5 rounded-lg text-sm font-semibold
                        {{ $lead->priority === 'high' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' :
                           ($lead->priority === 'medium' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' :
                           'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200') }}">
                        {{ ucfirst($lead->priority) }}
                    </span>
                </div>

                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-4">
                    <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Lead Score</div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $lead->getLeadScore() }}</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">/100</span>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-4">
                    <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Source</div>
                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ ucfirst($lead->source) }}</div>
                </div>

                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-4">
                    <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Interested In</div>
                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ ucfirst($lead->interested_in) }}</div>
                </div>

                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-4">
                    <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Follow-up Date</div>
                    <div class="text-sm font-medium {{ $lead->follow_up_date && $lead->follow_up_date->isPast() ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-gray-100' }}">
                        {{ $lead->follow_up_date ? $lead->follow_up_date->format('M d, Y') : '—' }}
                        @if($lead->follow_up_date && $lead->follow_up_date->isPast())
                            <span class="text-xs">(Overdue)</span>
                        @endif
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-4">
                    <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Call Attempts</div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-bold {{ $lead->call_attempts >= 3 ? 'text-red-600 dark:text-red-400' : ($lead->call_attempts >= 2 ? 'text-orange-600 dark:text-orange-400' : 'text-gray-900 dark:text-gray-100') }}">
                            {{ $lead->call_attempts }}
                        </span>
                        @if($lead->call_attempts >= 3)
                            <span class="text-xs text-red-600 dark:text-red-400">High</span>
                        @elseif($lead->call_attempts >= 2)
                            <span class="text-xs text-orange-600 dark:text-orange-400">Medium</span>
                        @endif
                    </div>
                </div>
            </div>

            @if($lead->notes)
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-4">
                    <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Notes</div>
                    <div class="text-sm text-gray-900 dark:text-gray-100 whitespace-pre-wrap">{{ $lead->notes }}</div>
                </div>
            @endif

            {{-- Converted Customer Info --}}
            @if($lead->status === 'converted' && $lead->convertedToCustomer)
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm font-semibold text-green-900 dark:text-green-100 mb-1">
                                Converted to Customer
                            </div>
                            <div class="text-sm text-green-800 dark:text-green-200">
                                {{ $lead->convertedToCustomer->name }} - {{ $lead->convertedToCustomer->phone }}
                            </div>
                            <div class="text-xs text-green-600 dark:text-green-400 mt-1">
                                {{ $lead->converted_at?->diffForHumans() }}
                            </div>
                        </div>
                        <a href="{{ route('customers.show', $lead->convertedToCustomer) }}"
                           class="inline-flex items-center px-3 py-1.5 bg-green-600 hover:bg-green-700 rounded-md text-xs font-medium text-white transition">
                            View Customer
                        </a>
                    </div>
                </div>
            @endif

            {{-- Interaction Timeline --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Interactions</h3>
                    <button onclick="document.getElementById('interaction-form').classList.toggle('hidden')"
                            class="inline-flex items-center px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 rounded-md text-xs font-medium text-white transition">
                        + Add Interaction
                    </button>
                </div>

                {{-- Add Interaction Form --}}
                <div id="interaction-form" class="hidden p-4 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                    <form action="{{ route('leads.interactions.store', $lead) }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
                            <select id="type" name="type" required
                                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="call">Phone Call</option>
                                <option value="email">Email</option>
                                <option value="sms">SMS</option>
                                <option value="whatsapp">WhatsApp</option>
                                <option value="meeting">Meeting</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                            <textarea id="notes" name="notes" rows="2" required
                                      class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                      placeholder="What was discussed?"></textarea>
                        </div>
                        <div class="flex justify-end gap-2">
                            <button type="button" onclick="document.getElementById('interaction-form').classList.add('hidden')"
                                    class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                                Cancel
                            </button>
                            <button type="submit"
                                    class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 rounded-md text-sm font-medium text-white">
                                Save Interaction
                            </button>
                        </div>
                    </form>
                </div>

                <div class="p-4">
                    @forelse($lead->interactions as $interaction)
                        <div class="flex gap-3 {{ !$loop->last ? 'mb-4 pb-4 border-b border-gray-200 dark:border-gray-700' : '' }}">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-start justify-between mb-1">
                                    <div>
                                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                            {{ ucfirst($interaction->type) }}
                                        </span>
                                        @if($interaction->user)
                                            <span class="text-xs text-gray-500 dark:text-gray-400 ml-2">
                                                by {{ $interaction->user->name }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $interaction->created_at?->diffForHumans() }}
                                    </div>
                                </div>
                                <div class="text-sm text-gray-900 dark:text-gray-100">
                                    {{ $interaction->notes }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-sm text-gray-500 dark:text-gray-400">
                            No interactions yet. Add one to track communications with this lead.
                        </div>
                    @endforelse
                </div>
            </div>

            @if($lead->lost_reason)
                <div class="bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl p-4">
                    <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Lost Reason</div>
                    <div class="text-sm text-gray-900 dark:text-gray-100">{{ $lead->lost_reason }}</div>
                </div>
            @endif
        </div>
    </div>

    {{-- Mark as Lost Modal --}}
    <div id="mark-lost-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Mark Lead as Lost</h3>

            <form action="{{ route('leads.mark-as-lost', $lead) }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="lost_reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Reason <span class="text-red-500">*</span>
                    </label>
                    <textarea id="lost_reason" name="lost_reason" rows="3" required
                              class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500"
                              placeholder="E.g., Not interested, Too expensive, Went with competitor..."></textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" id="do_not_contact" name="do_not_contact" value="1"
                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="do_not_contact" class="text-sm text-gray-700 dark:text-gray-300">
                        Customer requested not to be contacted
                    </label>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('mark-lost-modal').classList.add('hidden')"
                            class="flex-1 px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 transition">
                        Cancel
                    </button>
                    <button type="submit"
                            class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 rounded-md text-sm font-medium text-white transition">
                        Mark as Lost
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
