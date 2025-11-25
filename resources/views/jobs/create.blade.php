{{-- resources/views/jobs/create.blade.php --}}

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            New Job
        </h2>
    </x-slot>

    {{-- Include Select2 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* Dark mode styles for Select2 */
        .select2-container--default .select2-selection--single {
            background-color: white;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            height: 38px;
            padding: 4px 8px;
        }
        .dark .select2-container--default .select2-selection--single {
            background-color: #374151;
            border-color: #4b5563;
            color: #f3f4f6;
        }
        .dark .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #f3f4f6;
        }
        .dark .select2-dropdown {
            background-color: #374151;
            border-color: #4b5563;
        }
        .dark .select2-container--default .select2-results__option {
            color: #f3f4f6;
        }
        .dark .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #4f46e5;
        }
        .dark .select2-container--default .select2-search--dropdown .select2-search__field {
            background-color: #1f2937;
            border-color: #4b5563;
            color: #f3f4f6;
        }
        .select2-container {
            width: 100% !important;
        }
    </style>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-4 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6">
                <form method="POST" action="{{ route('jobs.store') }}" class="space-y-4">
                    @csrf

                    {{-- Job Date --}}
                    <div>
                        <label for="job_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Job Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="job_date" name="job_date"
                               value="{{ old('job_date', date('Y-m-d')) }}"
                               class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500"
                               required>
                        @error('job_date')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Select the date for this job (defaults to today).
                        </p>
                    </div>

                    {{-- Job Type --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Job Type
                        </label>
                        <div class="flex gap-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="job_type" value="moto"
                                       class="rounded border-gray-300 text-indigo-600"
                                       {{ old('job_type', 'moto') === 'moto' ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Motorcycle</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="job_type" value="ac"
                                       class="rounded border-gray-300 text-indigo-600"
                                       {{ old('job_type') === 'ac' ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">AC Service</span>
                            </label>
                        </div>
                        @error('job_type')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Job Category --}}
                    <div>
                        <label for="job_category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Job Category
                        </label>
                        <select id="job_category" name="job_category"
                                class="block w-full rounded-md border-gray-300 shadow-sm text-sm
                                       focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select category</option>
                            <option value="walkin" {{ old('job_category') === 'walkin' ? 'selected' : '' }}>Moto - Walk-in</option>
                            <option value="pickup" {{ old('job_category') === 'pickup' ? 'selected' : '' }}>Moto - Pickup</option>
                            <option value="ac_service" {{ old('job_category') === 'ac_service' ? 'selected' : '' }}>AC - Service</option>
                            <option value="ac_install" {{ old('job_category') === 'ac_install' ? 'selected' : '' }}>AC - Install/Relocate</option>
                            <option value="ac_repair" {{ old('job_category') === 'ac_repair' ? 'selected' : '' }}>AC - Repair</option>
                        </select>
                        @error('job_category')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Customer --}}
                    <div>
                        <label for="customer_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Customer
                        </label>
                        <select id="customer_id" name="customer_id"
                                class="block w-full rounded-md border-gray-300 shadow-sm text-sm
                                       focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Search or select customer...</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}"
                                    {{ (int)old('customer_id') === $customer->id ? 'selected' : '' }}
                                    data-vehicles="{{ json_encode($customer->vehicles->map(fn($v) => ['id' => $v->id, 'label' => trim(($v->brand ?? '') . ' ' . ($v->model ?? '') . ' ' . ($v->registration_number ? '(' . $v->registration_number . ')' : ''))])) }}"
                                    data-ac-units="{{ json_encode($customer->acUnits->map(fn($a) => ['id' => $a->id, 'label' => trim(($a->brand ?? 'AC') . ' ' . ($a->btu ? $a->btu . ' BTU ' : '') . ($a->location_description ? '(' . $a->location_description . ')' : ''))])) }}">
                                    {{ $customer->name }} ({{ $customer->phone }})
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror

                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Showing 5 recent customers. Type to search all customers.
                            <a href="{{ route('customers.create') }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline">
                                Create new customer
                            </a>
                        </p>
                    </div>

                    {{-- Vehicle (for moto jobs) --}}
                    <div id="vehicle-wrapper" class="{{ old('job_type', 'moto') === 'moto' ? '' : 'hidden' }}">
                        <label for="vehicle_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Vehicle (Bike)
                        </label>
                        <select id="vehicle_id" name="vehicle_id"
                                class="block w-full rounded-md border-gray-300 shadow-sm text-sm
                                       focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select vehicle (optional)</option>
                            {{-- Options filled by JS based on customer --}}
                        </select>
                        @error('vehicle_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Vehicles are managed from the <a href="#" id="customer-vehicle-link" class="text-indigo-600 dark:text-indigo-400 hover:underline">customer page</a>.
                        </p>
                    </div>

                    {{-- AC Unit (for AC jobs) --}}
                    <div id="ac-unit-wrapper" class="{{ old('job_type') === 'ac' ? '' : 'hidden' }}">
                        <label for="ac_unit_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            AC Unit
                        </label>
                        <select id="ac_unit_id" name="ac_unit_id"
                                class="block w-full rounded-md border-gray-300 shadow-sm text-sm
                                       focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select AC unit (optional)</option>
                            {{-- Options filled by JS based on customer --}}
                        </select>
                        @error('ac_unit_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            AC units are managed from the <a href="#" id="customer-ac-link" class="text-indigo-600 dark:text-indigo-400 hover:underline">customer page</a>.
                        </p>
                    </div>

                    {{-- Address / Location --}}
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Address / Location
                        </label>
                        <input type="text" id="address" name="address"
                               value="{{ old('address') }}"
                               class="block w-full rounded-md border-gray-300 shadow-sm text-sm
                                      focus:border-indigo-500 focus:ring-indigo-500"
                               placeholder="Hulhumale Phase 2, Vinares Bxx, Flat xxx">
                        @error('address')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Pickup Location --}}
                    <div>
                        <label for="pickup_location" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Pickup Location (for Moto pickups)
                        </label>
                        <input type="text" id="pickup_location" name="pickup_location"
                               value="{{ old('pickup_location') }}"
                               class="block w-full rounded-md border-gray-300 shadow-sm text-sm
                                      focus:border-indigo-500 focus:ring-indigo-500"
                               placeholder="Phase 1 / Phase 2 / Highway / Garage">
                        @error('pickup_location')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Problem Description --}}
                    <div>
                        <label for="problem_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Problem / Notes
                        </label>
                        <textarea id="problem_description" name="problem_description" rows="3"
                                  class="block w-full rounded-md border-gray-300 shadow-sm text-sm
                                         focus:border-indigo-500 focus:ring-indigo-500">{{ old('problem_description') }}</textarea>
                        @error('problem_description')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <a href="{{ route('jobs.index') }}"
                           class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-gray-100">
                            Cancel
                        </a>
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md
                                       font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700
                                       focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Save Job
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Include jQuery and Select2 JS --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            // Store for customer assets (vehicles and AC units)
            let customerAssetsCache = {};

            // Initialize Select2 with AJAX search
            $('#customer_id').select2({
                ajax: {
                    url: '{{ route('jobs.search-customers') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term // search term
                        };
                    },
                    processResults: function (data) {
                        // Cache the customer assets for later use
                        data.results.forEach(function(customer) {
                            customerAssetsCache[customer.id] = {
                                vehicles: customer.vehicles,
                                ac_units: customer.ac_units
                            };
                        });
                        return {
                            results: data.results
                        };
                    },
                    cache: true
                },
                placeholder: 'Search or select customer...',
                allowClear: true,
                minimumInputLength: 0,
                width: '100%'
            });

            // Auto-focus search input when dropdown opens
            $('#customer_id').on('select2:open', function() {
                setTimeout(function() {
                    document.querySelector('.select2-search__field').focus();
                }, 100);
            });

            // Cache initial customer data from pre-loaded options
            $('#customer_id option').each(function() {
                const $option = $(this);
                const customerId = $option.val();
                if (customerId) {
                    try {
                        customerAssetsCache[customerId] = {
                            vehicles: JSON.parse($option.data('vehicles') || '[]'),
                            ac_units: JSON.parse($option.data('ac-units') || '[]')
                        };
                    } catch (e) {
                        customerAssetsCache[customerId] = { vehicles: [], ac_units: [] };
                    }
                }
            });

            const jobTypeRadios = document.querySelectorAll('input[name="job_type"]');
            const vehicleWrapper = document.getElementById('vehicle-wrapper');
            const acWrapper = document.getElementById('ac-unit-wrapper');
            const vehicleSelect = document.getElementById('vehicle_id');
            const acSelect = document.getElementById('ac_unit_id');
            const customerVehicleLink = document.getElementById('customer-vehicle-link');
            const customerAcLink = document.getElementById('customer-ac-link');

            function getSelectedJobType() {
                const checked = Array.from(jobTypeRadios).find(r => r.checked);
                return checked ? checked.value : 'moto';
            }

            function updateTypeVisibility() {
                const type = getSelectedJobType();
                if (type === 'moto') {
                    vehicleWrapper.classList.remove('hidden');
                    acWrapper.classList.add('hidden');
                } else {
                    acWrapper.classList.remove('hidden');
                    vehicleWrapper.classList.add('hidden');
                }
            }

            function updateCustomerLinks() {
                const customerId = $('#customer_id').val();
                if (!customerId) {
                    if (customerVehicleLink) customerVehicleLink.href = '#';
                    if (customerAcLink) customerAcLink.href = '#';
                    return;
                }
                const url = "{{ url('customers') }}/" + customerId;
                if (customerVehicleLink) customerVehicleLink.href = url;
                if (customerAcLink) customerAcLink.href = url;
            }

            function populateAssets() {
                const customerId = $('#customer_id').val();
                const assets = customerAssetsCache[customerId] || { vehicles: [], ac_units: [] };

                // Clear existing options (except first)
                vehicleSelect.innerHTML = '<option value="">Select vehicle (optional)</option>';
                acSelect.innerHTML = '<option value="">Select AC unit (optional)</option>';

                assets.vehicles.forEach(v => {
                    const opt = document.createElement('option');
                    opt.value = v.id;
                    opt.textContent = v.label || ('Vehicle #' + v.id);
                    vehicleSelect.appendChild(opt);
                });

                assets.ac_units.forEach(a => {
                    const opt = document.createElement('option');
                    opt.value = a.id;
                    opt.textContent = a.label || ('AC Unit #' + a.id);
                    acSelect.appendChild(opt);
                });

                updateCustomerLinks();
            }

            // Events
            $('#customer_id').on('change', populateAssets);
            jobTypeRadios.forEach(r => r.addEventListener('change', updateTypeVisibility));

            // Init on load
            updateTypeVisibility();
            populateAssets();
        });
    </script>
</x-app-layout>
