<x-app-layout>
    @php
        $screen = $screen ?? 'builder';
        $workflowCustomerReady = (bool) $log->customer_id;
        $workflowApprovalReady = $log->isApprovalReady();
        $workflowItemsReady = $log->lines->isNotEmpty();
        $workflowInvoiceReady = $log->isReadyForInvoice();
        $stepTwoHasErrors = $errors->has('approval_method')
            || $errors->has('po_number')
            || $errors->has('quotation_validity_days')
            || $errors->has('notes')
            || $errors->has('search_note')
            || $errors->has('due_date');
    @endphp
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div>
                <h2 class="font-semibold text-lg sm:text-xl text-gray-800 dark:text-gray-200 leading-tight">
	                    Sale #{{ $log->id }} — {{ $log->business_unit === 'moto' ? 'Micro Moto' : ($log->business_unit === 'cool' ? 'Micro Cool' : ($log->business_unit === 'easyfix' ? 'Micronet - Easy Fix' : 'Micronet')) }} — {{ $log->date->format('D, d M Y') }}
                </h2>
                <div class="flex items-center gap-2 mt-1 flex-wrap">
                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $log->status === \App\Models\DailySalesLog::STATUS_DRAFT ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '' }}
                        {{ $log->status === \App\Models\DailySalesLog::STATUS_QUOTATION ? 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200' : '' }}
                        {{ $log->status === \App\Models\DailySalesLog::STATUS_INVOICED ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200' : '' }}
                        {{ $log->status === \App\Models\DailySalesLog::STATUS_PARTIAL_PAID ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : '' }}
                        {{ in_array($log->status, ['submitted', \App\Models\DailySalesLog::STATUS_PAID], true) ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}">
                        {{ $log->status_label }}
                    </span>
                    @if($log->submittedByUser)
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            Submitted by {{ $log->submittedByUser->name }} at {{ $log->submitted_at->format('g:i A') }}
                        </span>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-2 w-full overflow-x-auto sm:w-auto sm:flex-wrap pb-1 sm:pb-0">
                <a href="{{ route('sales.daily.index', ['date' => $log->date->toDateString()]) }}"
                   class="shrink-0 inline-flex items-center gap-1 px-3 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    Back
                </a>

                @if($log->canEditQuotation())
	                    <a href="{{ route('sales.daily.quotation', $log) }}" target="_blank"
	                       class="shrink-0 inline-flex items-center gap-1 px-3 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium rounded-lg transition">
	                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
	                        Preview Quotation
	                    </a>
                        <a href="{{ route('sales.daily.quotation-builder', $log) }}"
                           class="shrink-0 inline-flex items-center gap-1 px-3 py-2 {{ $screen === 'builder' ? 'bg-gray-900 text-white dark:bg-gray-100 dark:text-gray-900' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700' }} text-sm font-medium rounded-lg transition">
                            Builder
                        </a>
                        <a href="{{ route('sales.daily.invoice-workflow', $log) }}"
                           class="shrink-0 inline-flex items-center gap-1 px-3 py-2 {{ $screen === 'invoice' ? 'bg-gray-900 text-white dark:bg-gray-100 dark:text-gray-900' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700' }} text-sm font-medium rounded-lg transition">
                            Invoice
                        </a>
	                    @if($log->job_id)
	                        <a href="{{ route('jobs.invoice', $log->job_id) }}" target="_blank"
		                           class="shrink-0 inline-flex items-center gap-1 px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
	                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
	                            Preview Invoice
	                        </a>
	                    @else
                            <div class="w-full sm:w-auto">
	                            <form method="POST" action="{{ route('sales.daily.convert-invoice', $log) }}" target="_blank" class="w-full sm:w-auto">
	                                @csrf
	                                <button type="submit"
                                            {{ $workflowInvoiceReady ? '' : 'disabled' }}
	                                    class="w-full sm:w-auto shrink-0 inline-flex items-center justify-center gap-1 px-3 py-2 text-sm font-medium rounded-lg transition {{ $workflowInvoiceReady ? 'bg-indigo-600 hover:bg-indigo-700 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500 cursor-not-allowed' }}">
	                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
	                                    Create Invoice
	                                </button>
	                            </form>
                                @unless($workflowInvoiceReady)
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Complete customer, approval, and item details first.</p>
                                @endunless
                            </div>
	                    @endif
	                @elseif($log->status === \App\Models\DailySalesLog::STATUS_INVOICED)
                        <a href="{{ route('sales.daily.invoice-workflow', $log) }}"
                           class="shrink-0 inline-flex items-center gap-1 px-3 py-2 bg-gray-900 text-white dark:bg-gray-100 dark:text-gray-900 text-sm font-medium rounded-lg transition">
                            Invoice
                        </a>
                        @if($log->job_id)
                            <a href="{{ route('jobs.invoice', $log->job_id) }}" target="_blank"
                               class="shrink-0 inline-flex items-center gap-1 px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                Preview Invoice
                            </a>
                        @endif
                        <a href="#submit-sale-panel"
                           class="shrink-0 inline-flex items-center gap-1 px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Submit Sale
                        </a>
                        @if(Auth::user()->hasAnyRole(['admin', 'manager']))
                            <form method="POST" action="{{ route('sales.daily.reopen', $log) }}" onsubmit="return confirm('Reopen this invoice back to quotation stage? Stock movements will be reversed.')">
                                @csrf
                                <button type="submit" class="shrink-0 inline-flex items-center gap-1 px-3 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                    Reopen
                                </button>
                            </form>
                        @endif
	                @else
	                    <form method="POST" action="{{ route('sales.daily.open') }}">
	                        @csrf
	                        <input type="hidden" name="date" value="{{ $log->date->toDateString() }}">
	                        <input type="hidden" name="business_unit" value="{{ $log->business_unit }}">
                        <button type="submit"
                                class="shrink-0 inline-flex items-center gap-1 px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            New Sale
                        </button>
                    </form>
                    @if($log->job_id)
                        <a href="{{ route('jobs.invoice', $log->job_id) }}" target="_blank"
                           class="shrink-0 inline-flex items-center gap-1 px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            Print Invoice
                        </a>
                    @endif
                    @if(Auth::user()->hasAnyRole(['admin', 'manager']))
                        <form method="POST" action="{{ route('sales.daily.reopen', $log) }}" onsubmit="return confirm('Reopen this sale? Stock movements will be reversed.')">
                            @csrf
                            <button type="submit" class="shrink-0 inline-flex items-center gap-1 px-3 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition">
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

            @if($log->canEditQuotation() && $screen === 'builder')
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3">
                    <div class="rounded-2xl border {{ $workflowCustomerReady ? 'border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-900/20' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800' }} p-4">
                        <div class="text-xs font-semibold uppercase tracking-wide {{ $workflowCustomerReady ? 'text-emerald-700 dark:text-emerald-300' : 'text-gray-500 dark:text-gray-400' }}">Step 1</div>
                        <div class="mt-1 text-base font-semibold text-gray-900 dark:text-gray-100">Customer & Address</div>
                        <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ $workflowCustomerReady ? ($log->customer?->name . ($log->customer_address_text ? ' · address selected' : ' · no address yet')) : 'Select the customer and the correct service address.' }}
                        </div>
                    </div>
                    <div class="rounded-2xl border {{ $workflowApprovalReady ? 'border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-900/20' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800' }} p-4">
                        <div class="text-xs font-semibold uppercase tracking-wide {{ $workflowApprovalReady ? 'text-emerald-700 dark:text-emerald-300' : 'text-gray-500 dark:text-gray-400' }}">Step 2</div>
                        <div class="mt-1 text-base font-semibold text-gray-900 dark:text-gray-100">Quotation Setup</div>
                        <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ ($log->approval_method ?? 'not_applicable') === 'signed_copy' ? 'Signed-copy approval selected.' : (($log->approval_method ?? 'not_applicable') === 'po' ? ($log->po_number ? 'PO ready: ' . $log->po_number : 'PO selected - add PO number.') : 'Optional - leave as not applicable unless needed.') }}
                        </div>
                    </div>
                    <div class="rounded-2xl border {{ $workflowItemsReady ? 'border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-900/20' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800' }} p-4">
                        <div class="text-xs font-semibold uppercase tracking-wide {{ $workflowItemsReady ? 'text-emerald-700 dark:text-emerald-300' : 'text-gray-500 dark:text-gray-400' }}">Step 3</div>
                        <div class="mt-1 text-base font-semibold text-gray-900 dark:text-gray-100">Build Quotation</div>
                        <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ $workflowItemsReady ? $log->lines->count() . ' item(s) added. Review and preview the quotation.' : 'Add items, notes, warranty, and pricing lines.' }}
                        </div>
                    </div>
                    <div class="rounded-2xl border {{ $workflowInvoiceReady ? 'border-indigo-200 dark:border-indigo-800 bg-indigo-50 dark:bg-indigo-900/20' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800' }} p-4">
                        <div class="text-xs font-semibold uppercase tracking-wide {{ $workflowInvoiceReady ? 'text-indigo-700 dark:text-indigo-300' : 'text-gray-500 dark:text-gray-400' }}">Step 4</div>
                        <div class="mt-1 text-base font-semibold text-gray-900 dark:text-gray-100">Invoice Creation</div>
                        <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ $workflowInvoiceReady ? 'Ready to create or preview the invoice.' : 'Complete steps 1 to 3 before converting to invoice.' }}
                        </div>
                    </div>
                </div>
            @endif

            {{-- Step 1: Customer --}}
            @if($log->canEditQuotation() && $screen === 'builder')
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
                        newAddress: '',
                        selectedCustomer: {{ $log->customer ? Js::from([
                            'id' => $log->customer->id,
                            'name' => $log->customer->name,
                            'phone' => $log->customer->phone,
                        ]) : 'null' }},
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
                            this.selectedCustomer = {
                                id: c.id,
                                name: c.name,
                                phone: c.phone,
                            };
                            this.showDropdown = false;
                            this.phone = '';
                            this.searched = false;
                            this.$refs.customerIdInput.value = c.id;
                            this.$refs.customerAddressIdInput.value = '';
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
                            this.newAddress = '';
                        }
                     }">
                    <div class="mb-3">
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Step 1</div>
                        <h3 class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">Customer, Address, and Approval Target</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Choose the customer first, then lock the correct address before building the quotation.</p>
                    </div>

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

                    @if($log->customer && $log->customer->addresses->isNotEmpty())
                        @php
                            $selectedSaleAddressId = $log->customer_address_id
                                ?? optional($log->customer->addresses->firstWhere('is_default', true))->id
                                ?? optional($log->customer->addresses->first())->id;
                            $selectedSaleAddress = $log->customer->addresses->firstWhere('id', $selectedSaleAddressId);
                        @endphp
                        <form class="mt-3" method="POST" action="{{ route('sales.daily.set-customer', $log) }}">
                            @csrf
                            <input type="hidden" name="customer_id" value="{{ $log->customer->id }}">
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Address for this Sale</label>
                            <select name="customer_address_id"
                                    onchange="this.form.submit()"
                                    class="w-full sm:max-w-xl rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                @foreach($log->customer->addresses as $address)
                                    <option value="{{ $address->id }}" {{ (string) $selectedSaleAddressId === (string) $address->id ? 'selected' : '' }}>
                                        {{ $address->label }} - {{ $address->address }}
                                    </option>
                                @endforeach
                            </select>
                            @if($selectedSaleAddress?->address)
                                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ $selectedSaleAddress->address }}</p>
                            @elseif($log->customer_address_text)
                                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ $log->customer_address_text }}</p>
                            @endif
                            @error('customer_address_id')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </form>
                    @endif

                    {{-- Inline New Customer Form --}}
                    <div x-show="showNewForm" x-cloak class="mt-3 p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                        <h4 class="text-sm font-medium text-green-800 dark:text-green-300 mb-2">Create New Customer</h4>
	                        <form method="POST" action="{{ route('sales.daily.create-customer', $log) }}" class="flex flex-col gap-2">
	                            @csrf
                                <div class="flex flex-col sm:flex-row gap-2">
	                                <input type="text" name="name" x-model="newName" placeholder="Customer name" required
	                                       class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-green-500 focus:border-green-500 text-sm">
	                                <input type="text" name="phone" x-model="newPhone" placeholder="Phone number" required
	                                       class="flex-1 sm:max-w-[180px] rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-green-500 focus:border-green-500 text-sm">
	                                <input type="text" name="gst_number" placeholder="GST No (optional)"
	                                       class="flex-1 sm:max-w-[200px] rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-green-500 focus:border-green-500 text-sm">
                                </div>
                                <textarea name="address" x-model="newAddress" rows="2" placeholder="Address (optional)"
                                          class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-green-500 focus:border-green-500 text-sm"></textarea>
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
                        <input type="hidden" name="customer_address_id" x-ref="customerAddressIdInput" value="">
                    </form>
                    <form x-ref="clearForm" method="POST" action="{{ route('sales.daily.set-customer', $log) }}" class="hidden">
                        @csrf
                        <input type="hidden" name="customer_id" value="">
                        <input type="hidden" name="customer_address_id" value="">
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
                        @if($log->customer_address_text)
                            <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ $log->customer_address_text }}</div>
                        @endif
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 dark:bg-gray-700 rounded-lg text-sm text-gray-500 dark:text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            Walk-in Customer
                        </span>
                    @endif
                </div>
            @endif

            {{-- Step 2: Quotation settings --}}
            @if($log->canEditQuotation() && $screen === 'builder')
                <div x-data="{ open: {{ $stepTwoHasErrors ? 'true' : 'false' }} }"
                     class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <button type="button"
                            @click="open = !open"
                            class="w-full flex items-start justify-between gap-4 p-4 sm:p-5 text-left bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-750 transition">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Step 2 · Optional</div>
                            <h3 class="mt-1 text-lg sm:text-xl font-semibold text-gray-900 dark:text-gray-100">Quotation Settings</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Expand only if you need to add due dates, validity, approval method, or customer notes.</p>
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            <span class="hidden sm:inline text-xs text-gray-500 dark:text-gray-400" x-text="open ? 'Hide details' : 'Edit if needed'"></span>
                            <svg class="w-5 h-5 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </button>

                    <div x-show="open" x-collapse class="px-4 pb-4 sm:px-5 sm:pb-5 border-t border-gray-100 dark:border-gray-700">
                        <div class="pt-4 grid grid-cols-1 xl:grid-cols-2 gap-4 sm:gap-6">
                        <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/40 p-4 sm:p-5 space-y-4">
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Dates & Validity</h4>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Define when the invoice is due and how long this quotation should remain valid.</p>
                            </div>

                            <form method="POST" action="{{ route('sales.daily.update-due-date', $log) }}" class="space-y-3">
                                @csrf
                                @method('PATCH')
                                <div>
                                    <label for="due-date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Invoice Due Date</label>
                                    <input id="due-date" type="date" name="due_date" value="{{ $log->due_date ? $log->due_date->format('Y-m-d') : '' }}"
                                           class="w-full h-12 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm px-4 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Leave blank if payment is due upon receipt.</p>
                                </div>
                                <div class="flex justify-end">
                                    <button type="submit"
                                            class="inline-flex items-center justify-center px-4 py-2.5 bg-white hover:bg-gray-100 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-xl border border-gray-200 dark:border-gray-600 transition">
                                        Save Due Date
                                    </button>
                                </div>
                            </form>

                            <form method="POST" action="{{ route('sales.daily.update-quotation-validity', $log) }}" class="space-y-3">
                                @csrf
                                @method('PATCH')
                                <div>
                                    <label for="quotation-validity-days" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Quotation Validity (Days)</label>
                                    <input id="quotation-validity-days" type="number" name="quotation_validity_days"
                                           value="{{ old('quotation_validity_days', $log->quotation_validity_days ?? 3) }}"
                                           min="1" max="365"
                                           class="w-full h-12 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm px-4 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                           placeholder="3">
                                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Default validity is 3 days unless you set a different period here.</p>
                                </div>
                                <div class="flex justify-end">
                                    <button type="submit"
                                            class="inline-flex items-center justify-center px-4 py-2.5 bg-white hover:bg-gray-100 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-xl border border-gray-200 dark:border-gray-600 transition">
                                        Save Validity
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/40 p-4 sm:p-5 space-y-4">
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Approval & Customer Notes</h4>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Capture the customer’s approval path, what should print on the document, and any internal search reference you may need later.</p>
                            </div>

                            <form method="POST" action="{{ route('sales.daily.update-approval-method', $log) }}" class="space-y-4"
                                  x-data="{ approvalMethod: {{ Js::from(old('approval_method', $log->approval_method ?? 'not_applicable')) }} }">
                                @csrf
                                @method('PATCH')
                                <div>
                                    <label for="approval-method" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Approval Method</label>
                                    <select id="approval-method" name="approval_method" x-model="approvalMethod"
                                            class="w-full h-12 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm px-4 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="not_applicable">Not applicable</option>
                                        <option value="po">Purchase Order (PO)</option>
                                        <option value="signed_copy">Signed copy via WhatsApp</option>
                                    </select>
                                    @error('approval_method')
                                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="po-number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">PO Number</label>
                                    <input id="po-number" type="text" name="po_number"
                                           value="{{ old('po_number', $log->po_number) }}"
                                           :disabled="approvalMethod !== 'po'"
                                           :class="approvalMethod !== 'po' ? 'bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500 cursor-not-allowed border-gray-200 dark:border-gray-700' : ''"
                                           class="w-full h-12 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm px-4 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                           placeholder="Enter purchase order number">
                                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400" x-text="approvalMethod === 'po' ? 'Required before converting this quotation into an invoice.' : (approvalMethod === 'signed_copy' ? 'PO number is not required when approval is by signed copy.' : 'You can leave this section unchanged unless the customer needs a specific approval path.')"></p>
                                    @error('po_number')
                                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="flex justify-end">
                                    <button type="submit"
                                            class="inline-flex items-center justify-center px-4 py-2.5 bg-white hover:bg-gray-100 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-xl border border-gray-200 dark:border-gray-600 transition">
                                        Save Approval
                                    </button>
                                </div>
                            </form>

                            <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                                <form method="POST" action="{{ route('sales.daily.update-notes', $log) }}" class="space-y-3">
                                    @csrf
                                    @method('PATCH')
                                    <div>
                                        <label for="customer-notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Customer Notes</label>
                                        <textarea id="customer-notes" name="notes" rows="5"
                                                  class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm px-4 py-3 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                                  placeholder="Add any message, scope note, or commercial detail the customer should see on the quotation/invoice...">{{ old('notes', $log->notes) }}</textarea>
                                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">These notes are printed on the quotation and invoice.</p>
                                    </div>
                                    <div class="flex justify-end">
                                        <button type="submit"
                                                class="inline-flex items-center justify-center px-4 py-2.5 bg-white hover:bg-gray-100 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-xl border border-gray-200 dark:border-gray-600 transition">
                                            Save Notes
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                                <form method="POST" action="{{ route('sales.daily.update-search-note', $log) }}" class="space-y-3">
                                    @csrf
                                    @method('PATCH')
                                    <div>
                                        <label for="search-note" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Internal Search Note / Address Reference</label>
                                        <textarea id="search-note" name="search_note" rows="4"
                                                  class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm px-4 py-3 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                                  placeholder="Example: Blue house behind STO, Hulhumale lot 11249, old customer from Orchid Magu, office upstairs...">{{ old('search_note', $log->search_note) }}</textarea>
                                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Internal only. Use this when the phone number is missing and you need a searchable address or reference later. It will not print on the quotation or invoice.</p>
                                        @error('search_note')
                                            <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="flex justify-end">
                                        <button type="submit"
                                                class="inline-flex items-center justify-center px-4 py-2.5 bg-white hover:bg-gray-100 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-xl border border-gray-200 dark:border-gray-600 transition">
                                            Save Search Note
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
            @endif

            {{-- Add Line Form --}}
            @if($log->canEditQuotation() && $screen === 'builder')
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6"
                     x-data="{
                        mode: 'item',
                        selectedItemId: '',
                        customDescription: '',
                        unitPrice: '',
                        qty: 1,
                        note: '',
                        isGst: false,
                        warrantyValue: '',
                        warrantyUnit: '',
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
                        },
                        resetWarrantyIfNeeded() {
                            if (!this.warrantyUnit) {
                                this.warrantyValue = '';
                            }
                        }
                     }">
                    <div class="mb-4">
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Step 3</div>
                        <h3 class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">Build the Quotation</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Add inventory or custom lines, set pricing, notes, GST, and warranty, then review the quotation preview.</p>
                    </div>

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

                            {{-- Warranty --}}
                            <div class="lg:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Warranty</label>
                                <input type="number" name="warranty_value" x-model="warrantyValue" min="1" placeholder="30"
                                       :disabled="!warrantyUnit"
                                       :class="!warrantyUnit ? 'bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500 cursor-not-allowed border-gray-200 dark:border-gray-700' : 'bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-200 border-gray-300 dark:border-gray-600'"
                                       class="w-full rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 disabled:focus:ring-0 disabled:focus:border-gray-200 text-sm">
                            </div>

                            <div class="lg:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Warranty Unit</label>
                                <select name="warranty_unit" x-model="warrantyUnit" @change="resetWarrantyIfNeeded()"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                    <option value="">No warranty</option>
                                    <option value="days">Days</option>
                                    <option value="months">Months</option>
                                </select>
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
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">If warranty applies, enter the duration and select days or months.</p>
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
                                    @if($log->canEditQuotation())
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
                                            @if($line->warranty_value && $line->warranty_unit)
                                                <div class="text-xs text-emerald-600 dark:text-emerald-400 mt-0.5">
                                                    Warranty: {{ $line->warranty_value }} {{ $line->warranty_value == 1 ? rtrim($line->warranty_unit, 's') : $line->warranty_unit }}
                                                </div>
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
                                        @if($log->canEditQuotation())
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
                                        @if($line->warranty_value && $line->warranty_unit)
                                            <div class="text-xs text-emerald-600 dark:text-emerald-400 mt-0.5">
                                                Warranty: {{ $line->warranty_value }} {{ $line->warranty_value == 1 ? rtrim($line->warranty_unit, 's') : $line->warranty_unit }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <span class="text-sm font-semibold text-gray-900 dark:text-gray-100 tabular-nums">{{ number_format($line->line_total + $line->gst_amount, 2) }}</span>
                                        @if($log->canEditQuotation())
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

            {{-- Step 4: Totals / invoice / payment --}}
            @if($log->lines->isNotEmpty())
                @php $totals = $log->totals; @endphp
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6">
                    <div class="mb-4">
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $log->isSubmitted() ? 'Completed Sale' : 'Step 4' }}</div>
                        <h3 class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $log->isSubmitted() ? 'Invoice and Payment Summary' : ($screen === 'invoice' ? 'Invoice Conversion and Payment' : 'Invoice Creation and Payment') }}</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ $log->isSubmitted() ? 'Review the final totals, invoice state, and payment/account trail.' : ($screen === 'invoice' ? 'Convert the approved quotation to an invoice, then capture payment and submission here.' : 'Once the quotation is approved, review totals and convert or submit from this section.') }}
                        </p>
                    </div>
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

	                {{-- Submit Sale Panel (invoice ready / unpaid only) --}}
	                @if(!$log->isSubmitted() && $screen === 'invoice')
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6 border border-slate-200 dark:border-slate-700 mb-4">
                        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                            <div class="min-w-0">
                                <div class="text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-400">Internal Reference</div>
                                <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">Save a searchable landmark, address clue, or backlog reference for this invoice. This does not print on customer documents.</div>
                            </div>
                            <form method="POST" action="{{ route('sales.daily.update-search-note', $log) }}" class="w-full lg:w-[28rem] space-y-3">
                                @csrf
                                @method('PATCH')
                                <textarea name="search_note" rows="3"
                                          class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm px-4 py-3 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                          placeholder="Add searchable internal note...">{{ old('search_note', $log->search_note) }}</textarea>
                                <div class="flex justify-end">
                                    <button type="submit"
                                            class="inline-flex items-center justify-center px-4 py-2.5 bg-white hover:bg-gray-100 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-xl border border-gray-200 dark:border-gray-600 transition">
                                        Save Search Note
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

	                    <div id="submit-sale-panel" class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6 border border-indigo-100 dark:border-indigo-900/40 mb-4">
	                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
	                            <div>
	                                <div class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">Invoice Workflow</div>
	                                <div class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $log->job_id ? 'Invoice created - ready to collect payment' : 'Convert the approved quotation' }}</div>
	                                <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $log->job_id ? 'The invoice is already created. Use Submit Sale below to mark it paid and finish the sale.' : 'Make sure customer, address, items, approval method, and PO/signed-copy approval are all ready before creating the invoice.' }}</div>
	                            </div>
	                            <div class="flex flex-wrap gap-2">
	                                @if($log->canEditQuotation())
    	                                <a href="{{ route('sales.daily.quotation-builder', $log) }}"
    	                                   class="inline-flex items-center gap-1 px-4 py-2 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 text-sm font-medium rounded-lg transition">
    	                                    Back to Builder
    	                                </a>
                                    @endif
	                                @if(!$log->job_id)
	                                    <form method="POST" action="{{ route('sales.daily.convert-invoice', $log) }}" target="_blank">
	                                        @csrf
	                                        <button type="submit"
                                                    {{ $workflowInvoiceReady ? '' : 'disabled' }}
	                                                class="inline-flex items-center gap-1 px-4 py-2 text-sm font-medium rounded-lg transition {{ $workflowInvoiceReady ? 'bg-indigo-600 hover:bg-indigo-700 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500 cursor-not-allowed' }}">
	                                            Create Invoice
	                                        </button>
	                                    </form>
	                                @else
                                        @php
                                            $reminderSmsPreview = $log->job ? app(\App\Services\InvoiceReminderSmsService::class)->previewMessage($log->job) : null;
                                        @endphp
	                                    <a href="{{ route('jobs.invoice', $log->job_id) }}" target="_blank"
	                                       class="inline-flex items-center gap-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
	                                        Preview Invoice
	                                    </a>
                                        @if($log->job && $log->job->balance_amount > 0)
                                            <div class="basis-full rounded-xl border border-blue-200 dark:border-blue-900/50 bg-blue-50/70 dark:bg-blue-900/10 p-3">
                                                <div class="flex items-center justify-between gap-3 mb-2">
                                                    <div class="text-sm font-semibold text-blue-900 dark:text-blue-100">SMS Reminder Preview</div>
                                                    <button type="button"
                                                            onclick="navigator.clipboard.writeText(@js($reminderSmsPreview))"
                                                            class="inline-flex items-center justify-center px-3 py-1.5 bg-white dark:bg-gray-800 border border-blue-200 dark:border-blue-800 rounded-lg text-xs font-semibold text-blue-700 dark:text-blue-200 hover:bg-blue-100 dark:hover:bg-blue-900/30">
                                                        Copy SMS
                                                    </button>
                                                </div>
                                                <div class="text-xs text-blue-800 dark:text-blue-200 whitespace-pre-line leading-6">{{ $reminderSmsPreview }}</div>
                                            </div>
                                            <form method="POST" action="{{ route('jobs.send-invoice-reminder', $log->job) }}"
                                                  onsubmit="return confirm('Send invoice due reminder SMS to this customer now?')">
                                                @csrf
                                                <button type="submit"
                                                        class="inline-flex items-center gap-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                                                    Send SMS Reminder
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('jobs.send-invoice-reminder-email', $log->job) }}"
                                                  onsubmit="return confirm('Send invoice due reminder email to this customer now?')">
                                                @csrf
                                                <button type="submit"
                                                        class="inline-flex items-center gap-1 px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-lg transition">
                                                    Send Email Reminder
                                                </button>
                                            </form>
                                        @endif
	                                @endif
	                            </div>
                                @unless($log->job_id || $workflowInvoiceReady)
                                    <p class="text-sm text-amber-600 dark:text-amber-400">Finish customer, approval, and line items before creating the invoice.</p>
                                @endunless
	                        </div>
	                    </div>

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
