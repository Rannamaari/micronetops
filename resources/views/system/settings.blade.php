<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('System Settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Success/Error Messages --}}
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="mb-6 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700">
                <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-700 sm:px-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">GST Reporting Settings</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Enter the TIN and taxable activity numbers exactly as shown on the MIRA GST Registration Certificate.</p>
                </div>
                <form method="POST" action="{{ route('system.settings.gst.update') }}" class="p-5 sm:p-6">
                    @csrf
                    @method('PATCH')
                    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        <div class="sm:col-span-2 lg:col-span-1">
                            <label for="taxpayer_tin" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Taxpayer TIN</label>
                            <input id="taxpayer_tin" name="taxpayer_tin" value="{{ old('taxpayer_tin', $gstSetting?->taxpayer_tin ?: config('gst.taxpayer_tin')) }}" required class="mt-1 w-full rounded-lg border-gray-300 font-mono uppercase dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" placeholder="1063676GST501">
                            @error('taxpayer_tin')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        @foreach([
                            'activity_number_moto' => 'Micro Moto',
                            'activity_number_cool' => 'Micro Cool',
                            'activity_number_it' => 'Micronet',
                            'activity_number_easyfix' => 'EasyFix',
                            'activity_number_shared' => 'Shared Expenses',
                        ] as $field => $label)
                            @php
                                $configKey = match($field) {
                                    'activity_number_moto' => 'moto',
                                    'activity_number_cool' => 'cool',
                                    'activity_number_it' => 'it',
                                    'activity_number_easyfix' => 'easyfix',
                                    default => 'shared',
                                };
                            @endphp
                            <div>
                                <label for="{{ $field }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }} Activity Number</label>
                                <input id="{{ $field }}" name="{{ $field }}" value="{{ old($field, $gstSetting?->{$field} ?: config('gst.activity_numbers.'.$configKey)) }}" class="mt-1 w-full rounded-lg border-gray-300 font-mono uppercase dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" placeholder="From MIRA certificate">
                                @error($field)<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Changes apply immediately to monthly, quarterly, and CSV GST reports.</p>
                        <button class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Save GST Settings</button>
                    </div>
                </form>
            </div>

            {{-- Danger Zone --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold text-red-600 dark:text-red-400 mb-4 flex items-center">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        Danger Zone
                    </h3>

                    <div class="border-2 border-red-200 dark:border-red-800 rounded-lg p-6 bg-red-50 dark:bg-red-900/20">
                        <h4 class="font-semibold text-red-800 dark:text-red-300 mb-2">Purge Business Data</h4>
                        <p class="text-sm text-gray-700 dark:text-gray-300 mb-3">
                            This action will permanently delete all transactional data:
                        </p>
                        <ul class="list-disc list-inside text-sm text-gray-700 dark:text-gray-300 mb-3 space-y-1">
                            <li>All jobs, job items, payments, and job notes</li>
                            <li>All daily sales logs and lines</li>
                            <li>All expenses and inventory purchases</li>
                            <li>All EOD reconciliations</li>
                            <li>All leads and lead interactions</li>
                            <li>All account transactions and transfers</li>
                            <li>All bills</li>
                            <li>Inventory quantities and account balances reset to zero</li>
                        </ul>

                        <p class="text-sm text-green-700 dark:text-green-300 font-medium mb-3">
                            Preserved: customers, vehicles, AC units, inventory items & categories, expense categories, accounts, vendors, recurring expenses, petty cash, employees, users & roles
                        </p>

                        <p class="text-sm text-red-600 dark:text-red-400 font-semibold mb-4">
                            This action cannot be undone.
                        </p>

                        <button
                            type="button"
                            onclick="document.getElementById('purgeModal').classList.remove('hidden')"
                            class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                        >
                            Purge Business Data
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Confirmation Modal --}}
    <div id="purgeModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" onclick="if(event.target === this) this.classList.add('hidden')">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800" onclick="event.stopPropagation()">
            <div class="mt-3">
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 dark:bg-red-900 rounded-full mb-4">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 text-center">Confirm Data Purge</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-600 dark:text-gray-400 text-center mb-4">
                        This will delete all jobs, sales, expenses, leads, EOD, bills, and transactions. Inventory quantities and account balances will be reset to zero. This action cannot be undone.
                    </p>

                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded p-3 mb-4">
                        <p class="text-xs text-red-700 dark:text-red-300 font-semibold">
                            Type "DELETE ALL DATA" to confirm:
                        </p>
                        <input
                            type="text"
                            id="confirmationText"
                            class="mt-2 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500"
                            placeholder="DELETE ALL DATA"
                        >
                    </div>
                </div>
                <div class="flex gap-3 px-4 py-3">
                    <button
                        type="button"
                        onclick="document.getElementById('purgeModal').classList.add('hidden'); document.getElementById('confirmationText').value = '';"
                        class="flex-1 bg-gray-300 hover:bg-gray-400 dark:bg-gray-600 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200 font-bold py-2 px-4 rounded"
                    >
                        Cancel
                    </button>
                    <form method="POST" action="{{ route('system.purge') }}" class="flex-1">
                        @csrf
                        <button
                            type="submit"
                            id="confirmPurgeBtn"
                            disabled
                            class="w-full bg-red-600 hover:bg-red-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white font-bold py-2 px-4 rounded"
                        >
                            Confirm Purge
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Enable the confirm button only when the correct text is entered
        document.getElementById('confirmationText').addEventListener('input', function(e) {
            const confirmBtn = document.getElementById('confirmPurgeBtn');
            if (e.target.value === 'DELETE ALL DATA') {
                confirmBtn.disabled = false;
            } else {
                confirmBtn.disabled = true;
            }
        });
    </script>
</x-app-layout>
