<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('faults.index') }}"
               class="inline-flex items-center justify-center w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-full hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                New Fault Ticket
            </h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-4 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6">
                <form method="POST" action="{{ route('faults.store') }}" class="space-y-4" x-data="{
                    priority: '{{ old('priority', 'normal') }}',
                    businessUnit: '{{ old('business_unit', 'moto') }}',
                    customerSearch: '{{ $selectedCustomer ? $selectedCustomer->name : old('customer_name', '') }}',
                    customerPhone: '{{ $selectedCustomer ? $selectedCustomer->phone : old('customer_phone', '') }}',
                    customerId: '{{ $selectedCustomer ? $selectedCustomer->id : old('customer_id', '') }}',
                    results: [],
                    showResults: false,
                    customerJobs: [],
                    selectedJobId: '{{ old('job_id', '') }}',
                    loadingJobs: false,
                    async search() {
                        if (this.customerSearch.length < 2) { this.results = []; this.showResults = false; return; }
                        const resp = await fetch('{{ route('jobs.search-customers') }}?q=' + encodeURIComponent(this.customerSearch));
                        const data = await resp.json();
                        this.results = data.results;
                        this.showResults = true;
                    },
                    async selectCustomer(c) {
                        this.customerSearch = c.name;
                        this.customerPhone = c.phone;
                        this.customerId = c.id;
                        this.showResults = false;
                        this.selectedJobId = '';
                        await this.fetchJobs(c.id);
                    },
                    async fetchJobs(customerId) {
                        this.loadingJobs = true;
                        this.customerJobs = [];
                        try {
                            const resp = await fetch('/faults/customer-jobs/' + customerId);
                            const data = await resp.json();
                            this.customerJobs = data.jobs;
                        } catch (e) {
                            this.customerJobs = [];
                        }
                        this.loadingJobs = false;
                    },
                    clearCustomer() {
                        this.customerId = '';
                        this.customerJobs = [];
                        this.selectedJobId = '';
                    }
                }">
                    @csrf

                    {{-- Business Unit Toggle --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Business Unit <span class="text-red-500">*</span>
                        </label>
                        <div class="flex gap-3">
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" name="business_unit" value="moto" x-model="businessUnit" class="sr-only peer">
                                <div class="p-3 text-center rounded-lg border-2 transition
                                            peer-checked:border-orange-500 peer-checked:bg-orange-50 dark:peer-checked:bg-orange-900/20
                                            border-gray-200 dark:border-gray-600 hover:border-gray-300">
                                    <span class="text-sm font-semibold" :class="businessUnit === 'moto' ? 'text-orange-700 dark:text-orange-300' : 'text-gray-700 dark:text-gray-300'">Micro Moto</span>
                                </div>
                            </label>
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" name="business_unit" value="cool" x-model="businessUnit" class="sr-only peer">
                                <div class="p-3 text-center rounded-lg border-2 transition
                                            peer-checked:border-cyan-500 peer-checked:bg-cyan-50 dark:peer-checked:bg-cyan-900/20
                                            border-gray-200 dark:border-gray-600 hover:border-gray-300">
                                    <span class="text-sm font-semibold" :class="businessUnit === 'cool' ? 'text-cyan-700 dark:text-cyan-300' : 'text-gray-700 dark:text-gray-300'">Micro Cool</span>
                                </div>
                            </label>
                        </div>
                        @error('business_unit')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Priority Toggle --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Priority <span class="text-red-500">*</span>
                        </label>
                        <div class="flex gap-3">
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" name="priority" value="normal" x-model="priority" class="sr-only peer">
                                <div class="p-3 text-center rounded-lg border-2 transition
                                            peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900/20
                                            border-gray-200 dark:border-gray-600 hover:border-gray-300">
                                    <span class="text-sm font-semibold" :class="priority === 'normal' ? 'text-blue-700 dark:text-blue-300' : 'text-gray-700 dark:text-gray-300'">Normal</span>
                                    <span class="block text-xs mt-0.5" :class="priority === 'normal' ? 'text-blue-500 dark:text-blue-400' : 'text-gray-400'">Must resolve within 48h</span>
                                </div>
                            </label>
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" name="priority" value="urgent" x-model="priority" class="sr-only peer">
                                <div class="p-3 text-center rounded-lg border-2 transition
                                            peer-checked:border-red-500 peer-checked:bg-red-50 dark:peer-checked:bg-red-900/20
                                            border-gray-200 dark:border-gray-600 hover:border-gray-300">
                                    <span class="text-sm font-semibold" :class="priority === 'urgent' ? 'text-red-700 dark:text-red-300' : 'text-gray-700 dark:text-gray-300'">Urgent</span>
                                    <span class="block text-xs mt-0.5" :class="priority === 'urgent' ? 'text-red-500 dark:text-red-400' : 'text-gray-400'">Must resolve within 24h</span>
                                </div>
                            </label>
                        </div>
                        @error('priority')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Customer Search --}}
                    <div class="relative">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Customer Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               x-model="customerSearch"
                               @input.debounce.300ms="search()"
                               @focus="if(results.length) showResults = true"
                               @click.away="showResults = false"
                               name="customer_name"
                               placeholder="Search existing customer or type name..."
                               required
                               class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <input type="hidden" name="customer_id" :value="customerId">
                        @error('customer_name')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror

                        {{-- Search Results Dropdown --}}
                        <div x-show="showResults && results.length > 0" x-cloak
                             class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-md shadow-lg max-h-48 overflow-y-auto">
                            <template x-for="c in results" :key="c.id">
                                <button type="button"
                                        @click="selectCustomer(c)"
                                        class="w-full text-left px-4 py-2 hover:bg-gray-50 dark:hover:bg-gray-600 border-b border-gray-100 dark:border-gray-600 last:border-0">
                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="c.name"></span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 ml-2" x-text="c.phone"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- Customer Phone --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Customer Phone <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="customer_phone" x-model="customerPhone" required
                               class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('customer_phone')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Related Job --}}
                    <div x-show="customerId" x-cloak>
                        <label for="job_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Related Job (optional)
                        </label>
                        <template x-if="loadingJobs">
                            <p class="text-sm text-gray-500 dark:text-gray-400 py-2">Loading jobs...</p>
                        </template>
                        <template x-if="!loadingJobs && customerJobs.length === 0">
                            <p class="text-sm text-gray-500 dark:text-gray-400 py-2">No jobs found for this customer.</p>
                        </template>
                        <template x-if="!loadingJobs && customerJobs.length > 0">
                            <select name="job_id" id="job_id" x-model="selectedJobId"
                                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">— No linked job —</option>
                                <template x-for="job in customerJobs" :key="job.id">
                                    <option :value="job.id" x-text="job.label"></option>
                                </template>
                            </select>
                        </template>
                        @error('job_id')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Title --}}
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Fault Summary <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}" required
                               placeholder="Brief description of the fault..."
                               class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('title')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Description --}}
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Detailed Description <span class="text-red-500">*</span>
                        </label>
                        <textarea name="description" id="description" rows="4" required
                                  placeholder="Describe the fault in detail, what was the original service, what went wrong..."
                                  class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Assign To --}}
                    <div>
                        <label for="assigned_to" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Assign To (optional)
                        </label>
                        <select name="assigned_to" id="assigned_to"
                                class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">— Unassigned —</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('assigned_to') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                        @error('assigned_to')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Submit --}}
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <a href="{{ route('faults.index') }}"
                           class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-gray-100">
                            Cancel
                        </a>
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md
                                       font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none">
                            Create Ticket
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
