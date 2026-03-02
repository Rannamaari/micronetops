<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Sale #{{ $log->id }} — {{ $log->business_unit === 'moto' ? 'Micro Moto' : 'Micro Cool' }} — {{ $log->date->format('D, d M Y') }}
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
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('sales.daily.index', ['date' => $log->date->toDateString()]) }}"
                   class="inline-flex items-center gap-1 px-3 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    Back
                </a>

                @if(!$log->isSubmitted())
                    <a href="{{ route('sales.daily.quotation', $log) }}" target="_blank"
                       class="inline-flex items-center gap-1 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium rounded-lg transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Quotation
                    </a>
                @else
                    <form method="POST" action="{{ route('sales.daily.open') }}">
                        @csrf
                        <input type="hidden" name="date" value="{{ $log->date->toDateString() }}">
                        <input type="hidden" name="business_unit" value="{{ $log->business_unit }}">
                        <button type="submit"
                                class="inline-flex items-center gap-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            New Sale
                        </button>
                    </form>
                    @if($log->job_id)
                        <a href="{{ route('jobs.invoice', $log->job_id) }}" target="_blank"
                           class="inline-flex items-center gap-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            Print Invoice
                        </a>
                    @endif
                    @if(Auth::user()->hasAnyRole(['admin', 'manager']))
                        <form method="POST" action="{{ route('sales.daily.reopen', $log) }}" onsubmit="return confirm('Reopen this sale? Stock movements will be reversed.')">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-1 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                Reopen
                            </button>
                        </form>
                    @endif
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-4 sm:py-6">
        <div class="max-w-5xl mx-auto px-4 sm:px-4 lg:px-8 space-y-4 sm:space-y-6">
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

            {{-- Customer Picker --}}
            @if(!$log->isSubmitted())
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6"
                     x-data="{
                        phone: '',
                        results: [],
                        loading: false,
                        searched: false,
                        showDropdown: false,
                        showNewForm: false,
                        newName: '',
                        newPhone: '',
                        selectedCustomer: {{ $log->customer ? Js::from(['id' => $log->customer->id, 'name' => $log->customer->name, 'phone' => $log->customer->phone]) : 'null' }},
                        debounceTimer: null,
                        searchCustomers() {
                            clearTimeout(this.debounceTimer);
                            this.showNewForm = false;
                            if (this.phone.length < 3) { this.results = []; this.showDropdown = false; this.searched = false; return; }
                            this.debounceTimer = setTimeout(() => {
                                this.loading = true;
                                this.searched = true;
                                fetch('{{ route('jobs.search-customers') }}?q=' + encodeURIComponent(this.phone))
                                    .then(r => r.json())
                                    .then(data => {
                                        this.results = data.results || [];
                                        this.showDropdown = true;
                                        this.loading = false;
                                    })
                                    .catch(() => { this.loading = false; });
                            }, 300);
                        },
                        selectCustomer(c) {
                            this.selectedCustomer = { id: c.id, name: c.name, phone: c.phone };
                            this.showDropdown = false;
                            this.phone = '';
                            this.searched = false;
                            this.$refs.customerIdInput.value = c.id;
                            this.$refs.customerForm.submit();
                        },
                        clearCustomer() {
                            this.selectedCustomer = null;
                            this.$refs.clearForm.submit();
                        },
                        openNewCustomerForm() {
                            this.showDropdown = false;
                            this.showNewForm = true;
                            this.newPhone = this.phone;
                            this.newName = '';
                        }
                     }">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide mb-3">Customer</h3>

                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                        {{-- Current selection --}}
                        <div class="flex items-center gap-2">
                            <template x-if="selectedCustomer">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-700 rounded-lg text-sm font-medium text-indigo-700 dark:text-indigo-300">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                        <span x-text="selectedCustomer.name"></span>
                                        <span class="text-indigo-400 dark:text-indigo-500" x-text="'(' + selectedCustomer.phone + ')'"></span>
                                    </span>
                                    <button type="button" @click="clearCustomer()"
                                            class="text-xs text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 hover:underline">
                                        Clear
                                    </button>
                                </div>
                            </template>
                            <template x-if="!selectedCustomer">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 dark:bg-gray-700 rounded-lg text-sm text-gray-500 dark:text-gray-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    Walk-in Customer
                                </span>
                            </template>
                        </div>

                        {{-- Search input --}}
                        <div class="relative flex-1 w-full sm:max-w-xs" @click.away="showDropdown = false">
                            <input type="text" x-model="phone" @input="searchCustomers()" @focus="if(results.length) showDropdown = true"
                                   placeholder="Search by phone or name..."
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            <div x-show="loading" class="absolute right-3 top-2.5">
                                <svg class="animate-spin h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            </div>
                            <div x-show="showDropdown" x-cloak
                                 class="absolute z-30 w-full mt-1 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                                <template x-for="c in results" :key="c.id">
                                    <button type="button" @click="selectCustomer(c)"
                                            class="w-full text-left px-3 py-2 text-sm hover:bg-indigo-50 dark:hover:bg-gray-600 flex justify-between items-center">
                                        <span class="text-gray-900 dark:text-gray-100" x-text="c.name"></span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400" x-text="c.phone"></span>
                                    </button>
                                </template>
                                <template x-if="searched && results.length === 0 && !loading">
                                    <div class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400">No customers found.</div>
                                </template>
                                <button type="button" @click="openNewCustomerForm()"
                                        class="w-full text-left px-3 py-2 text-sm font-medium text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-gray-600 border-t border-gray-200 dark:border-gray-600 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    New Customer
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Inline New Customer Form --}}
                    <div x-show="showNewForm" x-cloak class="mt-3 p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                        <h4 class="text-sm font-medium text-green-800 dark:text-green-300 mb-2">Create New Customer</h4>
                        <form method="POST" action="{{ route('sales.daily.create-customer', $log) }}" class="flex flex-col sm:flex-row gap-2">
                            @csrf
                            <input type="text" name="name" x-model="newName" placeholder="Customer name" required
                                   class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-green-500 focus:border-green-500 text-sm">
                            <input type="text" name="phone" x-model="newPhone" placeholder="Phone number" required
                                   class="flex-1 sm:max-w-[180px] rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-green-500 focus:border-green-500 text-sm">
                            <div class="flex gap-2">
                                <button type="submit"
                                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition">
                                    Create & Select
                                </button>
                                <button type="button" @click="showNewForm = false"
                                        class="px-3 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm rounded-lg transition">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Hidden forms for set/clear customer --}}
                    <form x-ref="customerForm" method="POST" action="{{ route('sales.daily.set-customer', $log) }}" class="hidden">
                        @csrf
                        <input type="hidden" name="customer_id" x-ref="customerIdInput" value="">
                    </form>
                    <form x-ref="clearForm" method="POST" action="{{ route('sales.daily.set-customer', $log) }}" class="hidden">
                        @csrf
                        <input type="hidden" name="customer_id" value="">
                    </form>
                </div>
            @else
                {{-- Show assigned customer on submitted logs --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide mb-3">Customer</h3>
                    @if($log->customer)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-700 rounded-lg text-sm font-medium text-indigo-700 dark:text-indigo-300">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            {{ $log->customer->name }} ({{ $log->customer->phone }})
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 dark:bg-gray-700 rounded-lg text-sm text-gray-500 dark:text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            Walk-in Customer
                        </span>
                    @endif
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
                        note: '',
                        isGst: false,
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
                        get lineSubtotal() {
                            return parseFloat(this.unitPrice || 0) * parseInt(this.qty || 0);
                        },
                        get gstAmount() {
                            return this.isGst ? this.lineSubtotal * 0.08 : 0;
                        },
                        get lineTotal() {
                            return (this.lineSubtotal + this.gstAmount).toFixed(2);
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

                            {{-- GST Checkbox --}}
                            <div class="lg:col-span-2 flex items-end">
                                <label class="flex items-center gap-2 px-3 py-2 cursor-pointer">
                                    <input type="checkbox" x-model="isGst"
                                           class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">GST 8%</span>
                                </label>
                                <input type="hidden" name="is_gst_applicable" :value="isGst ? 1 : 0">
                            </div>

                            {{-- Line Total Preview --}}
                            <div class="lg:col-span-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Total</label>
                                <div class="px-3 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg text-sm font-semibold text-gray-900 dark:text-gray-100 text-center">
                                    <template x-if="isGst">
                                        <span>
                                            <span class="text-xs font-normal text-gray-500 dark:text-gray-400" x-text="lineSubtotal.toFixed(2) + ' + ' + gstAmount.toFixed(2)"></span>
                                            <br>
                                            <span x-text="lineTotal + ' MVR'"></span>
                                        </span>
                                    </template>
                                    <template x-if="!isGst">
                                        <span x-text="lineTotal + ' MVR'"></span>
                                    </template>
                                </div>
                            </div>

                            {{-- Add Button --}}
                            <div class="lg:col-span-1">
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
                <div class="px-4 sm:px-6 py-3 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">
                        Sale Lines ({{ $log->lines->count() }})
                    </h3>
                </div>

                @if($log->lines->isEmpty())
                    <div class="p-6 text-center text-gray-500 dark:text-gray-400 text-sm">
                        No lines yet. Add your first sale above.
                    </div>
                @else
                    {{-- Desktop Table --}}
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Item</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Qty</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Price</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">GST</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total</th>
                                    @if(!$log->isSubmitted())
                                        <th class="px-4 py-3 w-16"></th>
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
                                            @if($line->is_gst_applicable)
                                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200">8%</span>
                                            @else
                                                <span class="text-gray-400 dark:text-gray-500">&mdash;</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right text-sm font-medium text-gray-900 dark:text-gray-100">{{ number_format($line->line_total + $line->gst_amount, 2) }}</td>
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

                    {{-- Mobile Cards --}}
                    <div class="sm:hidden divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($log->lines as $line)
                            <div class="p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $line->description }}</span>
                                            @if($line->is_stock_item)
                                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300 font-medium">Part</span>
                                            @elseif($line->inventory_item_id)
                                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300 font-medium">Service</span>
                                            @else
                                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400 font-medium">Custom</span>
                                            @endif
                                            @if($line->is_gst_applicable)
                                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-300 font-medium">+GST</span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 tabular-nums">
                                            {{ $line->qty }} x {{ number_format($line->unit_price, 2) }} MVR
                                            @if($line->is_gst_applicable)
                                                <span class="text-amber-600 dark:text-amber-400">+ {{ number_format($line->gst_amount, 2) }} GST</span>
                                            @endif
                                        </div>
                                        @if($line->note)
                                            <div class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ $line->note }}</div>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <span class="text-sm font-semibold text-gray-900 dark:text-gray-100 tabular-nums">{{ number_format($line->line_total + $line->gst_amount, 2) }}</span>
                                        @if(!$log->isSubmitted())
                                            <form method="POST" action="{{ route('sales.daily.remove-line', [$log, $line]) }}" onsubmit="return confirm('Remove this line?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 rounded text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Running Totals --}}
            @if($log->lines->isNotEmpty())
                @php $totals = $log->totals; @endphp
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6">
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Subtotal</p>
                            <p class="text-lg font-bold text-gray-700 dark:text-gray-300 mt-1">{{ number_format($totals['subtotal'], 2) }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">GST 8%</p>
                            <p class="text-lg font-bold text-amber-600 dark:text-amber-400 mt-1">{{ number_format($totals['gst'], 2) }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Grand Total</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ number_format($totals['grand'], 2) }} <span class="text-sm font-normal">MVR</span></p>
                        </div>
                    </div>

                    {{-- Payment info for submitted sales --}}
                    @if($log->isSubmitted() && $log->payment_method)
                        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex flex-wrap items-center gap-4 justify-center">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Paid via</span>
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $log->payment_method === 'cash' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' }}">
                                        {{ ucfirst($log->payment_method) }}
                                    </span>
                                </div>
                                @if($log->payment_method === 'cash' && $log->cash_tendered)
                                    <div class="flex items-center gap-3 text-sm">
                                        <span class="text-gray-500 dark:text-gray-400">Cash Tendered: <strong class="text-gray-900 dark:text-gray-100">{{ number_format($log->cash_tendered, 2) }} MVR</strong></span>
                                        <span class="text-gray-500 dark:text-gray-400">Change: <strong class="text-green-600 dark:text-green-400">{{ number_format($log->cash_tendered - $totals['grand'], 2) }} MVR</strong></span>
                                    </div>
                                @endif
                                @if($log->payment_method === 'transfer' && $log->transferAccount)
                                    <div class="flex items-center gap-2 text-sm">
                                        <span class="text-gray-500 dark:text-gray-400">Account:</span>
                                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ $log->transferAccount->name }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Account Log (for submitted transfer sales) --}}
                @if($log->isSubmitted() && $log->payment_method === 'transfer' && $log->transferAccount)
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                        <div class="px-4 sm:px-6 py-3 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">Account Log</h3>
                        </div>
                        <div class="p-4 sm:p-6">
                            @if($accountTransaction)
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                                            <tr>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Account</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Type</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Description</th>
                                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                            <tr>
                                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">{{ $accountTransaction->occurred_at?->format('d M Y') }}</td>
                                                <td class="px-4 py-2 text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    <a href="{{ route('accounts.show', $log->transfer_account_id) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">
                                                        {{ $log->transferAccount->name }}
                                                    </a>
                                                </td>
                                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">
                                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                        Sale Transfer
                                                    </span>
                                                </td>
                                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">{{ $accountTransaction->description }}</td>
                                                <td class="px-4 py-2 text-sm text-right font-semibold text-green-600 dark:text-green-400">
                                                    +{{ number_format($accountTransaction->amount, 2) }} MVR
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                                    Account balance after: <strong class="text-gray-900 dark:text-gray-100">{{ number_format($log->transferAccount->balance, 2) }} MVR</strong>
                                </div>
                            @else
                                <p class="text-sm text-gray-500 dark:text-gray-400">No account transaction recorded for this sale. Reopen and re-submit to create the transaction.</p>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Submit Sale Panel (draft only) --}}
                @if(!$log->isSubmitted())
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6"
                         x-data="{
                            showPanel: false,
                            paymentMethod: 'cash',
                            cashTendered: '',
                            transferAccountId: '',
                            grandTotal: {{ $totals['grand'] }},
                            get change() {
                                let tendered = parseFloat(this.cashTendered) || 0;
                                return tendered - this.grandTotal;
                            },
                            get canSubmit() {
                                if (this.paymentMethod === 'transfer') return this.transferAccountId !== '';
                                return parseFloat(this.cashTendered) >= this.grandTotal;
                            }
                         }">
                        {{-- Initial Submit Button --}}
                        <div x-show="!showPanel">
                            <button type="button" @click="showPanel = true; cashTendered = grandTotal.toFixed(2)"
                                    class="w-full px-4 py-3 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg transition flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Submit Sale
                            </button>
                        </div>

                        {{-- Expanded Panel --}}
                        <div x-show="showPanel" x-cloak>
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide mb-4">Complete Sale</h3>

                            <form method="POST" action="{{ route('sales.daily.submit', $log) }}">
                                @csrf
                                <div class="space-y-4">
                                    {{-- Payment Method Toggle --}}
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Payment Method</label>
                                        <div class="flex rounded-lg overflow-hidden border border-gray-300 dark:border-gray-600 max-w-xs">
                                            <button type="button" @click="paymentMethod = 'cash'; cashTendered = grandTotal.toFixed(2)"
                                                    :class="paymentMethod === 'cash' ? 'bg-green-600 text-white' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                                                    class="flex-1 px-4 py-2.5 text-sm font-medium transition text-center">
                                                Cash
                                            </button>
                                            <button type="button" @click="paymentMethod = 'transfer'; cashTendered = ''"
                                                    :class="paymentMethod === 'transfer' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                                                    class="flex-1 px-4 py-2.5 text-sm font-medium transition text-center">
                                                Transfer
                                            </button>
                                        </div>
                                        <input type="hidden" name="payment_method" :value="paymentMethod">
                                    </div>

                                    {{-- Cash Tendered (only for cash) --}}
                                    <div x-show="paymentMethod === 'cash'" x-cloak>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cash Tendered (MVR)</label>
                                        <input type="number" name="cash_tendered" x-model="cashTendered" step="0.01" min="0" placeholder="0.00"
                                               class="w-full max-w-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-green-500 focus:border-green-500 text-sm">

                                        {{-- Change calculation --}}
                                        <div class="mt-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg max-w-xs">
                                            <div class="flex justify-between text-sm mb-1">
                                                <span class="text-gray-500 dark:text-gray-400">Grand Total</span>
                                                <span class="font-medium text-gray-900 dark:text-gray-100" x-text="grandTotal.toFixed(2) + ' MVR'"></span>
                                            </div>
                                            <div class="flex justify-between text-sm mb-1">
                                                <span class="text-gray-500 dark:text-gray-400">Cash Tendered</span>
                                                <span class="font-medium text-gray-900 dark:text-gray-100" x-text="(parseFloat(cashTendered) || 0).toFixed(2) + ' MVR'"></span>
                                            </div>
                                            <div class="flex justify-between text-sm pt-1 border-t border-gray-200 dark:border-gray-600">
                                                <span class="font-medium" :class="change >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">Change</span>
                                                <span class="font-bold" :class="change >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'" x-text="change.toFixed(2) + ' MVR'"></span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Transfer Account (only for transfer) --}}
                                    <div x-show="paymentMethod === 'transfer'" x-cloak>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Transfer To Account</label>
                                        <select name="transfer_account_id" x-model="transferAccountId"
                                                class="w-full max-w-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                                            <option value="">-- Select Account --</option>
                                            @foreach($accounts as $account)
                                                <option value="{{ $account->id }}">{{ $account->name }} ({{ number_format($account->balance, 2) }} MVR)</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Action Buttons --}}
                                    <div class="flex items-center gap-3 pt-2">
                                        <button type="submit" :disabled="!canSubmit"
                                                class="px-6 py-2.5 bg-green-600 hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-lg transition flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            Confirm Submit
                                        </button>
                                        <button type="button" @click="showPanel = false"
                                                class="px-4 py-2.5 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition">
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
