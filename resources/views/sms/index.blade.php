<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                SMS
            </h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-4 lg:px-8">
            @if($dryRun)
                <div class="mb-4 bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-sm text-yellow-900">
                    Local mode: <strong>DHIRAAGU_SMS_DRY_RUN</strong> is enabled. Messages will be logged as sent without contacting Dhiraagu.
                </div>
            @endif

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

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6 mb-6"
                 x-data="smsPage('{{ route('sms.customers.all') }}', '{{ route('sms.customers.search') }}')"
                 x-init="init()">
                <div class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4">
                    Send SMS
                </div>

                <form method="POST" action="{{ route('sms.send') }}" class="space-y-4" x-data="{ deliveryTiming: '{{ old('delivery_timing', 'now') }}' }">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Audience</label>
                            <div class="flex flex-col sm:flex-row gap-2">
                                <label class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 cursor-pointer">
                                    <input type="radio" name="audience" value="manual"
                                           x-model="audience"
                                           {{ old('audience', 'manual') === 'manual' ? 'checked' : '' }}>
                                    <span class="text-sm text-gray-800 dark:text-gray-200">Manual Numbers</span>
                                </label>
                                <label class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 cursor-pointer">
                                    <input type="radio" name="audience" value="all_customers"
                                           x-model="audience"
                                           {{ old('audience') === 'all_customers' ? 'checked' : '' }}>
                                    <span class="text-sm text-gray-800 dark:text-gray-200">All Customers</span>
                                </label>
                            </div>
                            @error('audience')
                                <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="source" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sender ID (source)</label>
                            <input id="source" name="source" type="text"
                                   value="{{ old('source', $defaultSource) }}"
                                   class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   placeholder="Micronet">
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">If empty, the system default will be used.</div>
                            @error('source')
                                <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div x-show="audience === 'all_customers'" x-cloak class="space-y-3">
                        <div class="bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                <div>
                                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">All Customers</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        Included: <span class="font-semibold" x-text="includedCount()"></span>
                                        , Excluded: <span class="font-semibold" x-text="excludedIds.length"></span>
                                        <template x-if="allLoading">
                                            <span class="ml-2">Loading...</span>
                                        </template>
                                    </div>
                                </div>

                                <div class="flex flex-col sm:flex-row gap-2 sm:items-center">
                                    <select x-model="customerCategoryFilter"
                                            class="w-full sm:w-48 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="all">All business lines</option>
                                        <option value="moto">Micro Moto</option>
                                        <option value="ac">Micro Cool</option>
                                        <option value="it">Micronet</option>
                                        <option value="easyfix">Easy Fix</option>
                                    </select>
                                    <input type="month"
                                           x-model="customerAddedMonth"
                                           class="w-full sm:w-44 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <input type="text"
                                           x-model="allFilter"
                                           class="w-full sm:w-72 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                           placeholder="Filter customers...">
                                    <button type="button"
                                            @click="resetAudienceFilters()"
                                            class="px-3 py-2 rounded-md bg-gray-200 dark:bg-gray-700 text-xs font-semibold text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600">
                                        Reset Filters
                                    </button>
                                </div>
                            </div>

                            <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                Use business line and month to target customers added during a specific period.
                            </div>

                            <div class="mt-3 max-h-72 overflow-auto rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                                <template x-for="c in filteredAllCustomers()" :key="c.id">
                                    <div class="flex items-center justify-between gap-3 px-3 py-2 border-b border-gray-100 dark:border-gray-700">
                                        <div class="min-w-0">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate" x-text="c.name"></div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                                <span x-text="c.phone"></span>
                                                <span class="mx-1">•</span>
                                                <span x-text="categoryLabel(c.category)"></span>
                                                <span class="mx-1">•</span>
                                                <span x-text="formatAddedAt(c.created_at)"></span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <template x-if="isExcluded(c.id)">
                                                <span class="px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200">Excluded</span>
                                            </template>
                                            <button type="button"
                                                    @click="toggleExclude(c.id)"
                                                    class="px-3 py-1.5 rounded-md text-xs font-semibold border"
                                                    :class="isExcluded(c.id) ? 'bg-green-50 text-green-700 border-green-200 hover:bg-green-100 dark:bg-green-900/20 dark:text-green-200 dark:border-green-800' : 'bg-red-50 text-red-700 border-red-200 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-200 dark:border-red-800'">
                                                <span x-text="isExcluded(c.id) ? 'Add back' : 'Remove'"></span>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="!allLoading && allCustomers.length === 0">
                                    <div class="px-3 py-3 text-xs text-gray-500 dark:text-gray-400">No customers found.</div>
                                </template>
                            </div>

                            <input type="hidden" name="exclude_customer_ids" :value="excludeIdsValue()">
                            <input type="hidden" name="customer_category_filter" :value="customerCategoryFilter">
                            <input type="hidden" name="customer_added_month" :value="customerAddedMonth">
                            @error('exclude_customer_ids')
                                <div class="text-xs text-red-600 mt-2">{{ $message }}</div>
                            @enderror
                            @error('customer_category_filter')
                                <div class="text-xs text-red-600 mt-2">{{ $message }}</div>
                            @enderror
                            @error('customer_added_month')
                                <div class="text-xs text-red-600 mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div x-show="audience === 'manual'" x-cloak>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div class="bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700 rounded-lg p-3"
                                 x-data="smsCustomerPicker('{{ route('sms.customers.search') }}')"
                                 x-init="init()">
                                <div class="flex items-center justify-between gap-3 mb-2">
                                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Pick Customers</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Click a result to add</div>
                                </div>

                                <div class="flex gap-2">
                                    <input type="text"
                                           x-model="q"
                                           @input.debounce.250ms="search()"
                                           class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                           placeholder="Search customers by name/phone...">
                                    <button type="button"
                                            @click="clearAll()"
                                            class="px-3 py-2 rounded-md bg-gray-200 dark:bg-gray-700 text-xs font-semibold text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600">
                                        Clear
                                    </button>
                                </div>

                                <div class="mt-3">
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Selected recipients</div>
                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="(r, idx) in selected" :key="r.number">
                                            <span class="inline-flex items-center gap-2 px-2.5 py-1.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-200">
                                                <span x-text="r.label"></span>
                                                <span class="text-indigo-600/70 dark:text-indigo-300/70" x-text="r.number"></span>
                                                <button type="button" class="text-indigo-700 dark:text-indigo-200 hover:opacity-70" @click="remove(idx)">x</button>
                                            </span>
                                        </template>
                                        <template x-if="selected.length === 0">
                                            <span class="text-xs text-gray-400 dark:text-gray-500">None selected</span>
                                        </template>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Results</div>
                                    <div class="max-h-56 overflow-auto rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                                        <template x-for="c in results" :key="c.id">
                                            <button type="button"
                                                    @click="addCustomer(c)"
                                                    class="w-full text-left px-3 py-2 border-b border-gray-100 dark:border-gray-700 hover:bg-indigo-50 dark:hover:bg-indigo-900/10">
                                                <div class="flex items-center justify-between gap-3">
                                                    <div class="min-w-0">
                                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate" x-text="c.name"></div>
                                                        <div class="text-xs text-gray-500 dark:text-gray-400 truncate" x-text="c.phone"></div>
                                                    </div>
                                                    <div class="text-xs text-gray-400 dark:text-gray-500" x-text="'#' + c.id"></div>
                                                </div>
                                            </button>
                                        </template>
                                        <template x-if="loading">
                                            <div class="px-3 py-2 text-xs text-gray-500 dark:text-gray-400">Searching...</div>
                                        </template>
                                        <template x-if="!loading && q.length > 0 && results.length === 0">
                                            <div class="px-3 py-2 text-xs text-gray-500 dark:text-gray-400">No matches</div>
                                        </template>
                                    </div>
                                </div>

                                <input type="hidden" name="numbers" :value="numbersValue()">
                            </div>

                            <div>
                                <label for="numbers_textarea" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone Numbers (optional)</label>
                                <textarea id="numbers_textarea" rows="8"
                                          x-ref="manualNumbers"
                                          @input="window.__smsPickerSync?.()"
                                          class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                          placeholder="You can still paste numbers here...&#10;Example:&#10;9607777777&#10;7777777 / 8888888">{{ old('numbers') }}</textarea>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    The system will auto-prefix <code class="px-1 rounded bg-gray-100 dark:bg-gray-800">960</code> for 7-digit numbers.
                                </div>
                            </div>
                        </div>
                        @error('numbers')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label for="content" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Message</label>
                        <textarea id="content" name="content" rows="5" required
                                  class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                  placeholder="Type your SMS message...">{{ old('content') }}</textarea>
                        @error('content')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40 p-4 space-y-4">
                        <div>
                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Delivery Timing</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Send immediately, or schedule the SMS for later.</div>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-2">
                            <label class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 cursor-pointer">
                                <input type="radio" name="delivery_timing" value="now" x-model="deliveryTiming" {{ old('delivery_timing', 'now') === 'now' ? 'checked' : '' }}>
                                <span class="text-sm text-gray-800 dark:text-gray-200">Send now</span>
                            </label>
                            <label class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 cursor-pointer">
                                <input type="radio" name="delivery_timing" value="later" x-model="deliveryTiming" {{ old('delivery_timing') === 'later' ? 'checked' : '' }}>
                                <span class="text-sm text-gray-800 dark:text-gray-200">Schedule for later</span>
                            </label>
                        </div>

                        <div x-show="deliveryTiming === 'later'" x-cloak>
                            <label for="scheduled_for" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Schedule Date & Time</label>
                            <input id="scheduled_for" name="scheduled_for" type="datetime-local"
                                   value="{{ old('scheduled_for') }}"
                                   class="w-full sm:w-80 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Time uses your current app timezone.</div>
                            @error('scheduled_for')
                                <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        @error('delivery_timing')
                            <div class="text-xs text-red-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <button type="submit"
                                @click="if (deliveryTiming === 'now' && !confirm('Send this SMS now?')) { $event.preventDefault(); } if (deliveryTiming === 'later' && !confirm('Schedule this SMS for later?')) { $event.preventDefault(); }"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none">
                            <span x-text="deliveryTiming === 'later' ? 'Schedule SMS' : 'Send SMS'"></span>
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-x-auto">
                <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Recent SMS</div>
                </div>
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Date</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">By</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Audience</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Status</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Scheduled</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Sent</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Failed</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Message</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($recent as $row)
                            <tr class="hover:bg-indigo-50 dark:hover:bg-indigo-900/10">
                                <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                    <div>{{ $row->created_at?->format('Y-m-d') }}</div>
                                    <div>{{ $row->created_at?->format('H:i') }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $row->user?->name ?? '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex px-2 py-1 rounded text-xs font-medium
                                        {{ $row->audience === 'all_customers' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' }}">
                                        {{ $row->audience === 'all_customers' ? 'All Customers' : 'Manual' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex px-2 py-1 rounded text-xs font-medium
                                        {{ $row->status === \App\Models\SmsMessage::STATUS_SCHEDULED ? 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200' : '' }}
                                        {{ $row->status === \App\Models\SmsMessage::STATUS_SENT ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                                        {{ $row->status === \App\Models\SmsMessage::STATUS_FAILED ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : '' }}
                                        {{ $row->status === \App\Models\SmsMessage::STATUS_CANCELLED ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' : '' }}
                                        {{ $row->status === \App\Models\SmsMessage::STATUS_SENDING ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : '' }}
                                        {{ $row->status === \App\Models\SmsMessage::STATUS_DRAFT ? 'bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-200' : '' }}">
                                        {{ str($row->status)->replace('_', ' ')->title() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                    @if($row->scheduled_for)
                                        <div>{{ $row->scheduled_for->format('Y-m-d') }}</div>
                                        <div>{{ $row->scheduled_for->format('H:i') }}</div>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                    {{ $row->sent_count }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                    {{ $row->failed_count }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                    <div class="max-w-md line-clamp-2">{{ $row->content }}</div>
                                    @if($row->error_message)
                                        <div class="mt-1 text-xs text-red-500 dark:text-red-400 line-clamp-2">{{ $row->error_message }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                    @if($row->status === \App\Models\SmsMessage::STATUS_SCHEDULED)
                                        <form method="POST" action="{{ route('sms.cancel', $row) }}" onsubmit="return confirm('Cancel this scheduled SMS?')">
                                            @csrf
                                            <button type="submit"
                                                    class="inline-flex items-center px-3 py-1.5 rounded-md bg-red-50 text-red-700 border border-red-200 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-200 dark:border-red-800 text-xs font-semibold">
                                                Cancel
                                            </button>
                                        </form>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No SMS sent yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
function smsPage(allUrl, searchUrl) {
    return {
        audience: '{{ old('audience', 'manual') }}',
        allUrl,
        searchUrl,
        allCustomers: [],
        allLoading: false,
        allFilter: '',
        customerCategoryFilter: '{{ old('customer_category_filter', 'all') }}',
        customerAddedMonth: '{{ old('customer_added_month', '') }}',
        excludedIds: [],
        init() {
            // Restore exclusions if validation failed (optional, simple).
            const oldExcluded = @json(old('exclude_customer_ids', ''));
            if (oldExcluded) {
                for (const p of String(oldExcluded).split(/[\s,;]+/)) {
                    const t = p.trim();
                    if (t && !this.excludedIds.includes(t)) this.excludedIds.push(t);
                }
            }
            this.$watch('audience', (val) => {
                if (val === 'all_customers') this.loadAllCustomers();
            });
            this.$watch('customerCategoryFilter', () => {
                if (this.audience === 'all_customers') this.reloadAllCustomers();
            });
            this.$watch('customerAddedMonth', () => {
                if (this.audience === 'all_customers') this.reloadAllCustomers();
            });
            if (this.audience === 'all_customers') {
                this.loadAllCustomers();
            }
        },
        buildAllCustomersUrl() {
            const url = new URL(this.allUrl, window.location.origin);
            if (this.customerCategoryFilter && this.customerCategoryFilter !== 'all') {
                url.searchParams.set('customer_category_filter', this.customerCategoryFilter);
            }
            if (this.customerAddedMonth) {
                url.searchParams.set('customer_added_month', this.customerAddedMonth);
            }
            return url.toString();
        },
        async loadAllCustomers() {
            if (this.allLoading || this.allCustomers.length > 0) return;
            await this.fetchAllCustomers();
        },
        async reloadAllCustomers() {
            if (this.allLoading) return;
            this.allCustomers = [];
            await this.fetchAllCustomers();
        },
        async fetchAllCustomers() {
            this.allLoading = true;
            try {
                const res = await fetch(this.buildAllCustomersUrl(), {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });
                const json = await res.json();
                this.allCustomers = Array.isArray(json.data) ? json.data : [];
                const validIds = this.allCustomers.map(c => String(c.id));
                this.excludedIds = this.excludedIds.filter(id => validIds.includes(String(id)));
            } catch (e) {
                this.allCustomers = [];
            } finally {
                this.allLoading = false;
            }
        },
        isExcluded(id) {
            return this.excludedIds.includes(String(id));
        },
        toggleExclude(id) {
            const key = String(id);
            const idx = this.excludedIds.indexOf(key);
            if (idx >= 0) this.excludedIds.splice(idx, 1);
            else this.excludedIds.push(key);
        },
        resetAudienceFilters() {
            this.customerCategoryFilter = 'all';
            this.customerAddedMonth = '';
            this.allFilter = '';
            this.excludedIds = [];
        },
        excludeIdsValue() {
            return this.excludedIds.join(',');
        },
        includedCount() {
            return Math.max(0, this.allCustomers.length - this.excludedIds.length);
        },
        categoryLabel(category) {
            return ({
                moto: 'Micro Moto',
                ac: 'Micro Cool',
                it: 'Micronet',
                easyfix: 'Easy Fix',
            })[category] || 'Uncategorized';
        },
        formatAddedAt(value) {
            if (!value) return 'No date';
            const d = new Date(value);
            if (Number.isNaN(d.getTime())) return 'No date';
            return 'Added ' + d.toLocaleDateString('en-CA', { year: 'numeric', month: 'short' });
        },
        filteredAllCustomers() {
            const f = (this.allFilter || '').trim().toLowerCase();
            if (!f) return this.allCustomers;
            return this.allCustomers.filter(c => {
                const name = String(c.name || '').toLowerCase();
                const phone = String(c.phone || '').toLowerCase();
                const email = String(c.email || '').toLowerCase();
                const category = this.categoryLabel(c.category).toLowerCase();
                return name.includes(f) || phone.includes(f) || email.includes(f) || category.includes(f);
            });
        }
    }
}

function smsCustomerPicker(searchUrl) {
    return {
        q: '',
        results: [],
        loading: false,
        selected: [],
        init() {
            // Keep a global hook so the manual textarea can trigger sync without tight coupling.
            window.__smsPickerSync = () => this.syncFromManualTextarea();
            this.syncFromManualTextarea();
        },
        async search() {
            const query = (this.q || '').trim();
            if (!query) {
                this.results = [];
                return;
            }
            this.loading = true;
            try {
                const res = await fetch(searchUrl + '?q=' + encodeURIComponent(query), {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });
                const json = await res.json();
                this.results = Array.isArray(json.data) ? json.data : [];
            } catch (e) {
                this.results = [];
            } finally {
                this.loading = false;
            }
        },
        clearAll() {
            this.selected = [];
            this.q = '';
            this.results = [];
            this.syncToManualTextarea();
        },
        remove(idx) {
            this.selected.splice(idx, 1);
            this.syncToManualTextarea();
        },
        addCustomer(c) {
            const tokens = this.extractPhoneTokens(String(c.phone || ''));
            for (const t of tokens) {
                const n = this.normalizeDestination(t);
                if (!n) continue;
                if (!this.selected.find(x => x.number === n)) {
                    this.selected.push({ number: n, label: (c.name || 'Customer') });
                }
            }
            this.syncToManualTextarea();
        },
        numbersValue() {
            // Hidden input for the form submit: selected + manual textarea extras, normalized.
            const selectedNums = this.selected.map(x => x.number);
            const manual = this.getManualTextareaValue();
            const manualNums = [];
            for (const t of this.extractPhoneTokens(manual)) {
                const n = this.normalizeDestination(t);
                if (n) manualNums.push(n);
            }
            const all = Array.from(new Set(selectedNums.concat(manualNums)));
            return all.join('\n');
        },
        getManualTextareaValue() {
            const el = this.$refs.manualNumbers;
            return el ? String(el.value || '') : '';
        },
        syncToManualTextarea() {
            // Put selected recipients into the manual textarea (so user can see/edit).
            const el = this.$refs.manualNumbers;
            if (!el) return;
            const current = el.value || '';
            const manualNums = [];
            for (const t of this.extractPhoneTokens(String(current))) {
                const n = this.normalizeDestination(t);
                if (n) manualNums.push(n);
            }
            const merged = Array.from(new Set(this.selected.map(x => x.number).concat(manualNums)));
            el.value = merged.join('\n');
        },
        syncFromManualTextarea() {
            // When the user pastes numbers, keep the chips list roughly aligned.
            const el = this.$refs.manualNumbers;
            if (!el) return;
            const nums = [];
            for (const t of this.extractPhoneTokens(String(el.value || ''))) {
                const n = this.normalizeDestination(t);
                if (n) nums.push(n);
            }
            const uniq = Array.from(new Set(nums));
            // Keep existing labels if present; fallback label.
            const next = [];
            for (const n of uniq) {
                const existing = this.selected.find(x => x.number === n);
                next.push(existing || { number: n, label: 'Manual' });
            }
            this.selected = next;
        },
        extractPhoneTokens(raw) {
            const s = (raw || '').trim();
            if (!s) return [];
            return s.split(/[\n,;\/|]+/).map(x => x.trim()).filter(Boolean);
        },
        normalizeDestination(token) {
            let digits = String(token || '').replace(/\D+/g, '');
            if (!digits) return null;
            if (digits.length === 7) digits = '960' + digits;
            if (digits.length === 10 && digits.startsWith('960')) return digits;
            return null;
        }
    }
}
</script>
