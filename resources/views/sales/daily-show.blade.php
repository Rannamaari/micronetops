<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ $log->business_unit === 'moto' ? 'Micro Moto' : 'Micro Cool' }} — {{ $log->date->format('D, d M Y') }}
                </h2>
                <div class="flex items-center gap-2 mt-1">
                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $log->isSubmitted() ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }}">
                        {{ ucfirst($log->status) }}
                    </span>
                    @if($log->submittedByUser)
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            Submitted by {{ $log->submittedByUser->name }} at {{ $log->submitted_at->format('g:i A') }}
                        </span>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('sales.daily.index', ['date' => $log->date->toDateString()]) }}"
                   class="inline-flex items-center gap-1 px-3 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    Back
                </a>

                @if(!$log->isSubmitted())
                    <form method="POST" action="{{ route('sales.daily.submit', $log) }}" onsubmit="return confirm('Submit this log? Stock will be deducted for parts.')">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Submit
                        </button>
                    </form>
                @elseif(Auth::user()->hasAnyRole(['admin', 'manager']))
                    <form method="POST" action="{{ route('sales.daily.reopen', $log) }}" onsubmit="return confirm('Reopen this log? Stock movements will be reversed.')">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            Reopen
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-4 lg:px-8 space-y-6">
            {{-- Flash messages --}}
            @if(session('success'))
                <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 px-4 py-3 rounded-lg text-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-3 rounded-lg text-sm">
                    {{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-3 rounded-lg text-sm">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Add Line Form --}}
            @if(!$log->isSubmitted())
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6"
                     x-data="{
                        mode: 'item',
                        selectedItemId: '',
                        customDescription: '',
                        unitPrice: '',
                        qty: 1,
                        paymentMethod: 'cash',
                        note: '',
                        items: {{ Js::from($inventoryItems->map(fn($i) => ['id' => $i->id, 'name' => $i->name, 'sell_price' => $i->sell_price, 'is_service' => $i->is_service, 'quantity' => $i->quantity])) }},
                        search: '',
                        showDropdown: false,
                        get filteredItems() {
                            if (!this.search) return this.items;
                            const s = this.search.toLowerCase();
                            return this.items.filter(i => i.name.toLowerCase().includes(s));
                        },
                        selectItem(item) {
                            this.selectedItemId = item.id;
                            this.search = item.name;
                            this.unitPrice = item.sell_price;
                            this.showDropdown = false;
                        },
                        clearItem() {
                            this.selectedItemId = '';
                            this.search = '';
                            this.unitPrice = '';
                        },
                        get lineTotal() {
                            return (parseFloat(this.unitPrice || 0) * parseInt(this.qty || 0)).toFixed(2);
                        }
                     }">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide mb-4">Add Sale Line</h3>

                    {{-- Mode toggle --}}
                    <div class="flex gap-2 mb-4">
                        <button type="button" @click="mode = 'item'; clearItem()"
                                :class="mode === 'item' ? 'bg-indigo-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                                class="px-3 py-1.5 text-sm font-medium rounded-lg transition">
                            Inventory Item
                        </button>
                        <button type="button" @click="mode = 'custom'; clearItem()"
                                :class="mode === 'custom' ? 'bg-indigo-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                                class="px-3 py-1.5 text-sm font-medium rounded-lg transition">
                            Custom Item
                        </button>
                    </div>

                    <form method="POST" action="{{ route('sales.daily.add-line', $log) }}">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4 items-end">
                            {{-- Item selection / Custom description --}}
                            <div class="lg:col-span-4">
                                <template x-if="mode === 'item'">
                                    <div class="relative">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Item</label>
                                        <input type="hidden" name="inventory_item_id" :value="selectedItemId">
                                        <input type="text" x-model="search" @focus="showDropdown = true" @click.away="showDropdown = false"
                                               placeholder="Search items..."
                                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                        <div x-show="showDropdown && filteredItems.length > 0" x-cloak
                                             class="absolute z-20 w-full mt-1 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                                            <template x-for="item in filteredItems" :key="item.id">
                                                <button type="button" @click="selectItem(item)"
                                                        class="w-full text-left px-3 py-2 text-sm hover:bg-indigo-50 dark:hover:bg-gray-600 flex justify-between items-center">
                                                    <span x-text="item.name" class="text-gray-900 dark:text-gray-100"></span>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                                        <span x-text="item.sell_price"></span> MVR
                                                        <template x-if="!item.is_service">
                                                            <span class="ml-1" x-text="'(Stock: ' + item.quantity + ')'"></span>
                                                        </template>
                                                    </span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="mode === 'custom'">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                                        <input type="text" name="description" x-model="customDescription" placeholder="Item description"
                                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                    </div>
                                </template>
                            </div>

                            {{-- Qty --}}
                            <div class="lg:col-span-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Qty</label>
                                <input type="number" name="qty" x-model="qty" min="1" value="1"
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            </div>

                            {{-- Unit Price --}}
                            <div class="lg:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Price (MVR)</label>
                                <input type="number" name="unit_price" x-model="unitPrice" step="0.01" min="0" placeholder="0.00"
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            </div>

                            {{-- Payment Method --}}
                            <div class="lg:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Payment</label>
                                <div class="flex rounded-lg overflow-hidden border border-gray-300 dark:border-gray-600">
                                    <button type="button" @click="paymentMethod = 'cash'"
                                            :class="paymentMethod === 'cash' ? 'bg-green-600 text-white' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                                            class="flex-1 px-3 py-2 text-sm font-medium transition text-center">
                                        Cash
                                    </button>
                                    <button type="button" @click="paymentMethod = 'transfer'"
                                            :class="paymentMethod === 'transfer' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                                            class="flex-1 px-3 py-2 text-sm font-medium transition text-center">
                                        Transfer
                                    </button>
                                </div>
                                <input type="hidden" name="payment_method" :value="paymentMethod">
                            </div>

                            {{-- Line Total Preview --}}
                            <div class="lg:col-span-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Total</label>
                                <div class="px-3 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg text-sm font-semibold text-gray-900 dark:text-gray-100 text-center"
                                     x-text="lineTotal + ' MVR'"></div>
                            </div>

                            {{-- Add Button --}}
                            <div class="lg:col-span-2">
                                <button type="submit"
                                        class="w-full px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                                    + Add Line
                                </button>
                            </div>
                        </div>

                        {{-- Note (optional, collapsible) --}}
                        <div class="mt-3" x-data="{ showNote: false }">
                            <button type="button" @click="showNote = !showNote" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">
                                + Add note
                            </button>
                            <div x-show="showNote" x-cloak class="mt-2">
                                <input type="text" name="note" placeholder="Optional note for this line..."
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            </div>
                        </div>
                    </form>
                </div>
            @endif

            {{-- Lines Table --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                <div class="p-4 sm:p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">
                        Sale Lines ({{ $log->lines->count() }})
                    </h3>
                </div>

                @if($log->lines->isEmpty())
                    <div class="p-6 text-center text-gray-500 dark:text-gray-400 text-sm">
                        No lines yet. Add your first sale above.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Item</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Qty</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Price</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Payment</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total</th>
                                    @if(!$log->isSubmitted())
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-16"></th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($log->lines as $line)
                                    <tr>
                                        <td class="px-4 py-3">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $line->description }}</div>
                                            @if($line->is_stock_item)
                                                <span class="text-xs text-blue-600 dark:text-blue-400">Part</span>
                                            @elseif($line->inventory_item_id)
                                                <span class="text-xs text-purple-600 dark:text-purple-400">Service</span>
                                            @else
                                                <span class="text-xs text-gray-400 dark:text-gray-500">Custom</span>
                                            @endif
                                            @if($line->note)
                                                <div class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ $line->note }}</div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center text-sm text-gray-700 dark:text-gray-300">{{ $line->qty }}</td>
                                        <td class="px-4 py-3 text-right text-sm text-gray-700 dark:text-gray-300">{{ number_format($line->unit_price, 2) }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                                                {{ $line->payment_method === 'cash' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' }}">
                                                {{ ucfirst($line->payment_method) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right text-sm font-medium text-gray-900 dark:text-gray-100">{{ number_format($line->line_total, 2) }}</td>
                                        @if(!$log->isSubmitted())
                                            <td class="px-4 py-3 text-center">
                                                <form method="POST" action="{{ route('sales.daily.remove-line', [$log, $line]) }}" onsubmit="return confirm('Remove this line?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 transition">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    </button>
                                                </form>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- Running Totals --}}
            @if($log->lines->isNotEmpty())
                @php $totals = $log->totals; @endphp
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6">
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Cash</p>
                            <p class="text-xl font-bold text-green-600 dark:text-green-400 mt-1">{{ number_format($totals['cash'], 2) }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Transfer</p>
                            <p class="text-xl font-bold text-blue-600 dark:text-blue-400 mt-1">{{ number_format($totals['transfer'], 2) }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Grand Total</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ number_format($totals['grand'], 2) }} <span class="text-sm font-normal">MVR</span></p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
