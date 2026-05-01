{{-- resources/views/jobs/create.blade.php - Mobile-First Quick Job Creation --}}

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            New Job
        </h2>
    </x-slot>

    <div class="py-2 sm:py-6">
        <div class="max-w-lg mx-auto px-3 sm:px-6">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                <form method="POST" action="{{ route('jobs.store') }}" class="p-4 space-y-4"
                      x-data="{
                          jobType: {{ Js::from(old('job_type', 'moto')) }},
                          phone: {{ Js::from(old('customer_phone', $preselectedCustomer?->phone ?? '')) }},
                          customerName: {{ Js::from(old('customer_name', $preselectedCustomer?->name ?? '')) }},
                          customerId: {{ Js::from((string) old('customer_id', $preselectedCustomer?->id ?? '')) }},
                          customerAddress: {{ Js::from(old('location', $preselectedCustomer?->address ?? '')) }},
                          customerAddressId: {{ Js::from((string) old('customer_address_id', '')) }},
                          phoneError: '',

                          searchResults: [],
                          selectedCustomer: {{ Js::from($preselectedCustomer ? [
                              'id' => $preselectedCustomer->id,
                              'name' => $preselectedCustomer->name,
                              'phone' => $preselectedCustomer->phone,
                              'address' => $preselectedCustomer->address ?? '',
                              'addresses' => $preselectedCustomer->addresses->map(fn ($address) => [
                                  'id' => $address->id,
                                  'label' => $address->label,
                                  'address' => $address->address,
                                  'contact_name' => $address->contact_name,
                                  'contact_phone' => $address->contact_phone,
                                  'is_default' => (bool) $address->is_default,
                                  'summary' => trim($address->label . ' - ' . $address->address),
                              ])->values()->all(),
                          ] : null) }},
                          customerAddresses: {{ Js::from($preselectedCustomer?->addresses?->map(fn ($address) => [
                              'id' => $address->id,
                              'label' => $address->label,
                              'address' => $address->address,
                              'contact_name' => $address->contact_name,
                              'contact_phone' => $address->contact_phone,
                              'is_default' => (bool) $address->is_default,
                              'summary' => trim($address->label . ' - ' . $address->address),
                          ])->values()->all() ?? []) }},
                          searching: false,
                          searched: false,
                          searchTimeout: null,
                          showDropdown: false,

                          init() {
                              this.populateAddressSelection();
                          },

                          normalizePhone(p) {
                              return p.replace(/[\s\-\(\)\.]/g, '').replace(/^\+960/, '');
                          },

                          populateAddressSelection() {
                              this.customerAddresses = Array.isArray(this.selectedCustomer?.addresses) ? this.selectedCustomer.addresses : [];

                              if (!this.customerAddresses.length) {
                                  this.customerAddressId = '';
                                  return;
                              }

                              if (this.customerAddressId) {
                                  const existing = this.customerAddresses.find(a => String(a.id) === String(this.customerAddressId));
                                  if (existing) {
                                      this.applySelectedAddress(existing, false);
                                      return;
                                  }
                              }

                              const defaultAddress = this.customerAddresses.find(a => a.is_default) || this.customerAddresses[0];
                              if (defaultAddress && !this.customerAddress) {
                                  this.applySelectedAddress(defaultAddress, false);
                              }
                          },

                          applySelectedAddress(address, forceLocation = true) {
                              if (!address) {
                                  this.customerAddressId = '';
                                  return;
                              }

                              this.customerAddressId = String(address.id);
                              if (forceLocation || !this.customerAddress) {
                                  this.customerAddress = address.address || '';
                              }
                          },

                          selectAddressById() {
                              const selected = this.customerAddresses.find(a => String(a.id) === String(this.customerAddressId));
                              this.applySelectedAddress(selected, true);
                          },

                          searchCustomer() {
                              clearTimeout(this.searchTimeout);
                              this.phoneError = '';
                              let normalized = this.normalizePhone(this.phone);

                              if (this.selectedCustomer) {
                                  this.clearCustomer(false);
                              }

                              if (normalized.length < 3) {
                                  this.searchResults = [];
                                  this.showDropdown = false;
                                  this.searched = false;
                                  return;
                              }

                              this.searchTimeout = setTimeout(() => {
                                  this.searching = true;
                                  fetch('/jobs/search/customers?q=' + encodeURIComponent(normalized))
                                      .then(r => r.json())
                                      .then(data => {
                                          this.searching = false;
                                          this.searched = true;
                                          this.searchResults = data.results || [];

                                          if (this.searchResults.length === 1) {
                                              this.selectCustomer(this.searchResults[0]);
                                          } else if (this.searchResults.length > 1) {
                                              this.showDropdown = true;
                                          } else {
                                              this.showDropdown = false;
                                          }
                                      })
                                      .catch(() => {
                                          this.searching = false;
                                          this.searched = true;
                                          this.searchResults = [];
                                      });
                              }, 400);
                          },

                          selectCustomer(customer) {
                              this.selectedCustomer = customer;
                              this.customerId = customer.id;
                              this.customerName = customer.name;
                              this.customerAddress = '';
                              this.phone = customer.phone;
                              this.customerAddressId = '';
                              this.showDropdown = false;
                              this.searchResults = [];
                              this.phoneError = '';
                              this.populateAddressSelection();
                          },

                          clearCustomer(refocus = true) {
                              this.selectedCustomer = null;
                              this.customerId = '';
                              this.customerName = '';
                              this.customerAddress = '';
                              this.customerAddressId = '';
                              this.customerAddresses = [];
                              this.showDropdown = false;
                              if (refocus) {
                                  this.$nextTick(() => {
                                      this.$refs.phoneInput.focus();
                                      this.searchCustomer();
                                  });
                              }
                          },

                          submitJob() {
                              if (!this.selectedCustomer && this.searched && this.searchResults.length > 0) {
                                  this.phoneError = 'Please select the existing customer for this phone number.';
                                  this.showDropdown = this.searchResults.length > 1;
                                  return false;
                              }
                              return true;
                          }
                      }"
                      @submit.prevent="if (submitJob()) $el.submit()">
                    @csrf

	                    {{-- Service Type - Large touch targets --}}
	                    <div>
	                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
	                            <label class="cursor-pointer" @click="jobType = 'moto'">
	                                <input type="radio" name="job_type" value="moto" class="sr-only" x-model="jobType">
	                                <div class="flex items-center justify-center p-4 border-2 rounded-xl transition-all"
	                                     :class="jobType === 'moto'
	                                         ? 'border-orange-500 bg-orange-100 dark:bg-orange-900/30'
	                                         : 'border-gray-200 dark:border-gray-600'">
	                                    <span class="text-2xl mr-2">🏍️</span>
	                                    <span class="font-bold text-lg text-gray-900 dark:text-gray-100">Micro Moto</span>
	                                </div>
	                            </label>
	                            <label class="cursor-pointer" @click="jobType = 'ac'">
	                                <input type="radio" name="job_type" value="ac" class="sr-only" x-model="jobType">
	                                <div class="flex items-center justify-center p-4 border-2 rounded-xl transition-all"
	                                     :class="jobType === 'ac'
	                                         ? 'border-sky-500 bg-sky-100 dark:bg-sky-900/30'
	                                         : 'border-gray-200 dark:border-gray-600'">
	                                    <span class="text-2xl mr-2">❄️</span>
	                                    <span class="font-bold text-lg text-gray-900 dark:text-gray-100">Micro Cool</span>
	                                </div>
	                            </label>
	                            <label class="cursor-pointer" @click="jobType = 'it'">
	                                <input type="radio" name="job_type" value="it" class="sr-only" x-model="jobType">
	                                <div class="flex items-center justify-center p-4 border-2 rounded-xl transition-all"
	                                     :class="jobType === 'it'
	                                         ? 'border-indigo-500 bg-indigo-100 dark:bg-indigo-900/30'
	                                         : 'border-gray-200 dark:border-gray-600'">
	                                    <span class="text-2xl mr-2">🖥️</span>
	                                    <span class="font-bold text-lg text-gray-900 dark:text-gray-100">Micronet</span>
	                                </div>
	                            </label>
	                            <label class="cursor-pointer" @click="jobType = 'easyfix'">
	                                <input type="radio" name="job_type" value="easyfix" class="sr-only" x-model="jobType">
	                                <div class="flex items-center justify-center p-4 border-2 rounded-xl transition-all"
	                                     :class="jobType === 'easyfix'
	                                         ? 'border-emerald-500 bg-emerald-100 dark:bg-emerald-900/30'
	                                         : 'border-gray-200 dark:border-gray-600'">
	                                    <span class="text-2xl mr-2">🛠️</span>
	                                    <span class="font-bold text-lg text-gray-900 dark:text-gray-100">Easy Fix</span>
	                                </div>
	                            </label>
	                        </div>
	                    </div>

                    {{-- Phone - with customer autocomplete --}}
                    <div class="relative" @click.away="showDropdown = false">
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Phone</label>
                        <div class="relative">
                            <input type="tel" name="customer_phone" id="customer_phone"
                                   x-ref="phoneInput"
                                   x-model="phone"
                                   @input="searchCustomer()"
                                   @focus="if (searchResults.length > 1) showDropdown = true"
                                   class="block w-full text-xl p-4 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 pr-12"
                                   placeholder="7XXXXXX"
                                   inputmode="tel"
                                   autofocus
                                   required>

                            {{-- Right-side status icon inside the input --}}
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none">
                                {{-- Spinner --}}
                                <svg x-show="searching" x-cloak class="animate-spin h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                {{-- Checkmark when selected --}}
                                <svg x-show="selectedCustomer && !searching" x-cloak class="h-5 w-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                        </div>

                        {{-- Autocomplete dropdown --}}
                        <div x-show="showDropdown && searchResults.length > 1"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             x-cloak
                             class="absolute z-20 left-0 right-0 mt-1 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl shadow-lg overflow-hidden max-h-60 overflow-y-auto">
                            <template x-for="customer in searchResults" :key="customer.id">
                                <button type="button"
                                        @click="selectCustomer(customer)"
                                        class="w-full text-left px-4 py-3 hover:bg-indigo-50 dark:hover:bg-gray-600 border-b border-gray-100 dark:border-gray-600 last:border-b-0 transition-colors">
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="customer.name"></span>
                                        <span class="text-xs text-gray-400 dark:text-gray-500" x-text="customer.phone"></span>
                                    </div>
                                    <p x-show="customer.address" class="text-xs text-gray-400 dark:text-gray-500 mt-0.5 truncate" x-text="customer.address"></p>
                                </button>
                            </template>
                        </div>

                        {{-- Selected customer — compact inline row --}}
                        <div x-show="selectedCustomer"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-cloak
                             class="mt-1.5 flex items-center gap-1.5 px-3 py-2 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800/60">
                            <svg class="w-3.5 h-3.5 text-indigo-500 dark:text-indigo-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"></path>
                            </svg>
                            <span class="text-sm font-medium text-indigo-800 dark:text-indigo-300 truncate" x-text="selectedCustomer?.name"></span>
                            <span class="text-xs text-indigo-400 dark:text-indigo-500 shrink-0">&middot;</span>
                            <span class="text-xs text-indigo-500 dark:text-indigo-400 shrink-0" x-text="selectedCustomer?.phone"></span>
                            <template x-if="selectedCustomer?.address">
                                <span class="text-xs text-indigo-400 dark:text-indigo-500 truncate hidden sm:inline" x-text="'&middot; ' + selectedCustomer?.address"></span>
                            </template>
                            <button type="button"
                                    @click="clearCustomer()"
                                    class="ml-auto text-xs text-indigo-500 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 font-medium whitespace-nowrap">
                                Change
                            </button>
                        </div>

                        <input type="hidden" name="customer_id" :value="customerId">

                        {{-- New customer hint --}}
                        <p x-show="!selectedCustomer && !searching && searched && searchResults.length === 0 && !phoneError"
                           x-cloak
                           class="mt-1.5 text-xs text-gray-400 dark:text-gray-500">
                            New customer — will be created on save
                        </p>

                        {{-- Client-side validation error --}}
                        <p x-show="phoneError" x-text="phoneError" x-cloak
                           class="mt-1.5 text-xs text-red-600 dark:text-red-400"></p>

                        @error('customer_phone')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

	                    {{-- Name --}}
	                    <div>
	                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Name</label>
	                        <input type="text" name="customer_name"
                               x-model="customerName"
                               :readonly="!!selectedCustomer"
                               class="block w-full text-lg p-3 rounded-xl border-gray-300 dark:border-gray-600 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 transition-colors"
                               :class="selectedCustomer
                                   ? 'bg-gray-50 dark:bg-gray-600 text-gray-500 dark:text-gray-400 cursor-default'
                                   : 'dark:bg-gray-700'"
                               placeholder="Customer name"
	                               required>
	                        @error('customer_name')
	                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
	                        @enderror
	                    </div>

	                    {{-- Customer GST No --}}
	                    <div>
	                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Customer GST No (optional)</label>
	                        <input type="text" name="customer_gst_number"
	                               value="{{ old('customer_gst_number') }}"
	                               class="block w-full text-lg p-3 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 transition-colors"
	                               placeholder="Example: 1063676GST501">
	                        @error('customer_gst_number')
	                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
	                        @enderror
	                    </div>

                    {{-- Title / Issue --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Issue</label>
                        <input type="text" name="title"
                               value="{{ old('title') }}"
                               class="block w-full text-lg p-3 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500"
	                               placeholder="Network down, CCTV offline, device issue...">
                    </div>

                    {{-- Location --}}
                    <div>
                        <template x-if="selectedCustomer && customerAddresses.length > 0">
                            <div class="mb-3">
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Saved Address</label>
                                <select name="customer_address_id"
                                        x-model="customerAddressId"
                                        @change="selectAddressById()"
                                        class="block w-full p-3 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500">
                                    <template x-for="address in customerAddresses" :key="address.id">
                                        <option :value="String(address.id)" x-text="address.summary"></option>
                                    </template>
                                </select>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Choose a saved address, or edit the location field below for a one-off visit.</p>
                                @error('customer_address_id')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </template>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Location</label>
                        <input type="text" name="location"
                               x-model="customerAddress"
                               class="block w-full p-3 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500"
                               placeholder="Hulhumale Phase 2, Flat 101...">
                    </div>

                    {{-- Invoice Due Date --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Invoice due date (optional)</label>
                        <input type="date" name="due_date"
                               value="{{ old('due_date') }}"
                               class="block w-full p-3 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Leave blank for “Due upon receipt”.</div>
                    </div>

                    {{-- Customer Notes (visible on invoice/quotation) --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Customer notes (optional)</label>
                        <textarea name="customer_notes" rows="3"
                                  class="block w-full p-3 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                  placeholder="Add any notes the customer should see on the quotation/invoice...">{{ old('customer_notes') }}</textarea>
                        <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Shown on printed quotation/invoice only when filled.</div>
                    </div>

                    {{-- Schedule & Priority - Collapsible --}}
                    <div x-data="{ showMore: {{ old('scheduled_at') || old('priority') ? 'true' : 'false' }} }">
                        <button type="button" @click="showMore = !showMore"
                                class="flex items-center text-sm text-indigo-600 dark:text-indigo-400 font-medium">
                            <svg class="w-4 h-4 mr-1 transition-transform" :class="{ 'rotate-90': showMore }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                            Schedule & Priority
                        </button>
                        <div x-show="showMore" x-collapse class="mt-3 space-y-3">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Date & Time</label>
                                    <input type="datetime-local" name="scheduled_at"
                                           value="{{ old('scheduled_at', $scheduledAt) }}"
                                           class="block w-full p-3 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Priority</label>
                                    <select name="priority"
                                            class="block w-full p-3 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm">
                                        <option value="normal" {{ old('priority', 'normal') === 'normal' ? 'selected' : '' }}>Normal</option>
                                        <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
                                        <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>🔴 Urgent</option>
                                        <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Assign Technician --}}
                            @if($technicians->count() > 0)
                            <div>
                                <label class="block text-xs text-gray-500 mb-2">Assign to</label>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($technicians as $tech)
                                        <label class="cursor-pointer">
                                            <input type="checkbox" name="assignees[]" value="{{ $tech->id }}" class="sr-only peer"
                                                   {{ in_array($tech->id, old('assignees', [])) ? 'checked' : '' }}>
                                            <span class="inline-block px-3 py-2 rounded-lg text-sm border-2 transition-all
                                                         peer-checked:border-indigo-500 peer-checked:bg-indigo-50 dark:peer-checked:bg-indigo-900/30
                                                         border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300">
                                                {{ $tech->name }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Hidden fields --}}
                    <input type="hidden" name="job_date" value="{{ date('Y-m-d') }}">
                    <input type="hidden" name="job_category" value="general">

                    {{-- Sticky Submit Button --}}
                    <div class="sticky bottom-0 pt-4 pb-2 bg-white dark:bg-gray-800 -mx-4 px-4 border-t border-gray-100 dark:border-gray-700">
                        <button type="submit"
                                class="w-full py-4 px-6 bg-indigo-600 text-white text-lg font-bold rounded-xl hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 active:bg-indigo-800">
                            Create Job
                        </button>
                        <a href="{{ route('jobs.index') }}"
                           class="block text-center mt-2 py-2 text-gray-500 dark:text-gray-400 text-sm">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const phoneInput = document.getElementById('customer_phone');
            if (phoneInput && !phoneInput.value) {
                setTimeout(() => phoneInput.focus(), 100);
            }
        });
    </script>
</x-app-layout>
