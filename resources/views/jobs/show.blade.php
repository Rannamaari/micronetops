{{-- resources/views/jobs/show.blade.php --}}

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Job #{{ $job->id }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-4 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="text-sm text-green-600 dark:text-green-400">
                    {{ session('success') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="text-sm text-red-600 dark:text-red-400">
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- Job header --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6 space-y-4">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Customer</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ $job->customer?->name }} ({{ $job->customer?->phone }})
                        </div>
                        @if($job->customer?->address)
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                {{ $job->customer->address }}
                            </div>
                        @endif
                    </div>
                    <div class="text-right">
                        <span class="inline-flex px-2 py-1 rounded text-xs
                            {{ $job->job_type === 'moto' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                            {{ strtoupper($job->job_type) }}
                        </span>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            {{ $job->job_category }} • {{ ucfirst($job->status) }}
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    @if($job->vehicle)
                        <div>
                            <div class="text-gray-500 dark:text-gray-400">Vehicle</div>
                            <div class="text-gray-900 dark:text-gray-100">
                                {{ $job->vehicle->brand }} {{ $job->vehicle->model }}
                                {{ $job->vehicle->registration_number ? '(' . $job->vehicle->registration_number . ')' : '' }}
                            </div>
                        </div>
                    @endif
                    @if($job->acUnit)
                        <div>
                            <div class="text-gray-500 dark:text-gray-400">AC Unit</div>
                            <div class="text-gray-900 dark:text-gray-100">
                                {{ $job->acUnit->brand }} - {{ $job->acUnit->btu }} BTU ({{ $job->acUnit->gas_type }})
                                @if($job->acUnit->location_description)
                                    • {{ $job->acUnit->location_description }}
                                @endif
                            </div>
                        </div>
                    @endif
                    @if($job->address)
                        <div class="sm:col-span-2">
                            <div class="text-gray-500 dark:text-gray-400">Address / Location</div>
                            <div class="text-gray-900 dark:text-gray-100">{{ $job->address }}</div>
                        </div>
                    @endif
                    @if($job->pickup_location)
                        <div class="sm:col-span-2">
                            <div class="text-gray-500 dark:text-gray-400">Pickup Location</div>
                            <div class="text-gray-900 dark:text-gray-100">{{ $job->pickup_location }}</div>
                        </div>
                    @endif
                </div>

                @if($job->problem_description)
                    <div class="text-sm">
                        <div class="text-gray-500 dark:text-gray-400 mb-1">Problem / Notes</div>
                        <div class="text-gray-900 dark:text-gray-100 whitespace-pre-line">
                            {{ $job->problem_description }}
                        </div>
                    </div>
                @endif
            </div>

            {{-- Charges: labour (read-only), travel, discount --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">
                    Charges
                </h3>
                <form method="POST" action="{{ route('jobs.update', $job) }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Labour (from services)
                        </label>
                        <input type="text"
                               value="{{ number_format($job->labour_total, 2) }} MVR"
                               class="block w-full rounded-md border-gray-300 text-sm bg-gray-100 dark:bg-gray-700
                                      text-gray-700 dark:text-gray-200"
                               disabled>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Travel (MVR)
                        </label>
                        <input type="number" step="0.01" name="travel_charges"
                               value="{{ old('travel_charges', $job->travel_charges) }}"
                               class="block w-full rounded-md border-gray-300 text-sm
                                      focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Discount (MVR)
                        </label>
                        <input type="number" step="0.01" name="discount"
                               value="{{ old('discount', $job->discount) }}"
                               class="block w-full rounded-md border-gray-300 text-sm
                                      focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div class="flex justify-end sm:justify-start">
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md
                                       font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700
                                       focus:outline-none">
                            Update
                        </button>
                    </div>
                </form>
            </div>

            {{-- Services (labour items) --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6 space-y-4">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                    Services (Labour)
                </h3>

                {{-- Existing service lines --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 dark:text-gray-400">Service</th>
                            <th class="px-2 py-1 text-right font-medium text-gray-500 dark:text-gray-400">Qty</th>
                            <th class="px-2 py-1 text-right font-medium text-gray-500 dark:text-gray-400">Unit Price</th>
                            <th class="px-2 py-1 text-right font-medium text-gray-500 dark:text-gray-400">Subtotal</th>
                            <th class="px-2 py-1"></th>
                        </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($job->items->where('is_service', true) as $item)
                            <tr>
                                <td class="px-2 py-1">
                                    {{ $item->inventoryItem?->name ?? 'Service #' . $item->inventory_item_id }}
                                </td>
                                <td class="px-2 py-1 text-right">{{ $item->quantity }}</td>
                                <td class="px-2 py-1 text-right">{{ number_format($item->unit_price, 2) }}</td>
                                <td class="px-2 py-1 text-right">{{ number_format($item->subtotal, 2) }}</td>
                                <td class="px-2 py-1 text-right">
                                    <form method="POST" action="{{ route('jobs.items.destroy', [$job, $item]) }}"
                                          onsubmit="return confirm('Remove this service?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="text-xs text-red-600 dark:text-red-400 hover:underline">
                                            Remove
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-2 py-2 text-center text-gray-500 dark:text-gray-400">
                                    No services added yet.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Add service --}}
                <form method="POST" action="{{ route('jobs.items.store', $job) }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                    @csrf
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Service
                        </label>
                        <select name="inventory_item_id"
                                class="block w-full rounded-md border-gray-300 text-sm
                                       focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select service</option>
                            @foreach($inventoryItems->where('is_service', true) as $inv)
                                <option value="{{ $inv->id }}">
                                    {{ $inv->name }} • {{ number_format($inv->sell_price, 2) }} MVR
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Quantity
                        </label>
                        <input type="number" name="quantity" min="1" value="1"
                               class="block w-full rounded-md border-gray-300 text-sm
                                      focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Unit Price (optional)
                        </label>
                        <input type="number" step="0.01" name="unit_price"
                               class="block w-full rounded-md border-gray-300 text-sm
                                      focus:border-indigo-500 focus:ring-indigo-500"
                               placeholder="Default = service price">
                    </div>

                    <div class="md:col-span-4 flex justify-end">
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md
                                       font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700
                                       focus:outline-none">
                            Add Service
                        </button>
                    </div>
                </form>
            </div>

            {{-- Parts & Materials (non-service items) --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6 space-y-4">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                    Parts & Materials
                </h3>

                {{-- Existing parts --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 dark:text-gray-400">Item</th>
                            <th class="px-2 py-1 text-right font-medium text-gray-500 dark:text-gray-400">Qty</th>
                            <th class="px-2 py-1 text-right font-medium text-gray-500 dark:text-gray-400">Unit Price</th>
                            <th class="px-2 py-1 text-right font-medium text-gray-500 dark:text-gray-400">Subtotal</th>
                            <th class="px-2 py-1"></th>
                        </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($job->items->where('is_service', false) as $item)
                            <tr>
                                <td class="px-2 py-1">
                                    {{ $item->inventoryItem?->name ?? 'Item #' . $item->inventory_item_id }}
                                </td>
                                <td class="px-2 py-1 text-right">{{ $item->quantity }}</td>
                                <td class="px-2 py-1 text-right">{{ number_format($item->unit_price, 2) }}</td>
                                <td class="px-2 py-1 text-right">{{ number_format($item->subtotal, 2) }}</td>
                                <td class="px-2 py-1 text-right">
                                    <form method="POST" action="{{ route('jobs.items.destroy', [$job, $item]) }}"
                                          onsubmit="return confirm('Remove this item?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="text-xs text-red-600 dark:text-red-400 hover:underline">
                                            Remove
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-2 py-2 text-center text-gray-500 dark:text-gray-400">
                                    No parts added yet.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Add part --}}
                <form method="POST" action="{{ route('jobs.items.store', $job) }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                    @csrf

                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Inventory Item
                        </label>
                        <select name="inventory_item_id"
                                class="block w-full rounded-md border-gray-300 text-sm
                                       focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select item</option>
                            @foreach($inventoryItems->where('is_service', false) as $inv)
                                <option value="{{ $inv->id }}">
                                    {{ $inv->name }} ({{ $inv->category }}) • Stock: {{ $inv->quantity }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Quantity
                        </label>
                        <input type="number" name="quantity" min="1" value="1"
                               class="block w-full rounded-md border-gray-300 text-sm
                                      focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Unit Price (optional)
                        </label>
                        <input type="number" step="0.01" name="unit_price"
                               class="block w-full rounded-md border-gray-300 text-sm
                                      focus:border-indigo-500 focus:ring-indigo-500"
                               placeholder="Default = item price">
                    </div>

                    <div class="md:col-span-4 flex justify-end">
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md
                                       font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700
                                       focus:outline-none">
                            Add Item
                        </button>
                    </div>
                </form>
            </div>

            {{-- Payments --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6 space-y-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                        Payments
                    </h3>
                    <span class="text-xs px-2 py-1 rounded
                        @if($job->payment_status === 'paid')
                            bg-green-100 text-green-800
                        @elseif($job->payment_status === 'partial')
                            bg-yellow-100 text-yellow-800
                        @else
                            bg-red-100 text-red-800
                        @endif">
                        {{ strtoupper($job->payment_status) }}
                    </span>
                </div>

                {{-- Payment summary --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
                    <div>
                        <div class="text-gray-500 dark:text-gray-400">Total Amount</div>
                        <div class="font-semibold text-gray-900 dark:text-gray-100">
                            {{ number_format($job->total_amount, 2) }} MVR
                        </div>
                    </div>
                    <div>
                        <div class="text-gray-500 dark:text-gray-400">Paid</div>
                        <div class="font-semibold text-gray-900 dark:text-gray-100">
                            {{ number_format($job->paid_amount, 2) }} MVR
                        </div>
                    </div>
                    <div>
                        <div class="text-gray-500 dark:text-gray-400">Balance</div>
                        <div class="font-semibold text-gray-900 dark:text-gray-100">
                            {{ number_format($job->balance_amount, 2) }} MVR
                        </div>
                    </div>
                </div>

                {{-- Existing payments list --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 dark:text-gray-400">Date</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 dark:text-gray-400">Method</th>
                            <th class="px-2 py-1 text-right font-medium text-gray-500 dark:text-gray-400">Amount</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 dark:text-gray-400">Reference</th>
                            <th class="px-2 py-1"></th>
                        </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($job->payments as $payment)
                            <tr>
                                <td class="px-2 py-1">
                                    {{ $payment->created_at?->format('Y-m-d H:i') }}
                                </td>
                                <td class="px-2 py-1">
                                    {{ ucfirst($payment->method) }}
                                </td>
                                <td class="px-2 py-1 text-right">
                                    {{ number_format($payment->amount, 2) }} MVR
                                </td>
                                <td class="px-2 py-1">
                                    {{ $payment->reference }}
                                </td>
                                <td class="px-2 py-1 text-right">
                                    <form method="POST" action="{{ route('jobs.payments.destroy', [$job, $payment]) }}"
                                          onsubmit="return confirm('Remove this payment?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="text-xs text-red-600 dark:text-red-400 hover:underline">
                                            Remove
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-2 py-2 text-center text-gray-500 dark:text-gray-400">
                                    No payments recorded yet.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Add payment --}}
                <form method="POST" action="{{ route('jobs.payments.store', $job) }}"
                      class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                    @csrf

                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Amount (MVR)
                        </label>
                        <input type="number" step="0.01" name="amount"
                               value="{{ old('amount', $job->balance_amount) }}"
                               class="block w-full rounded-md border-gray-300 text-sm
                                      focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Method
                        </label>
                        <select name="method"
                                class="block w-full rounded-md border-gray-300 text-sm
                                       focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="transfer">Bank Transfer</option>
                            <option value="credit">Customer Credit</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Reference (optional)
                        </label>
                        <input type="text" name="reference"
                               value="{{ old('reference') }}"
                               class="block w-full rounded-md border-gray-300 text-sm
                                      focus:border-indigo-500 focus:ring-indigo-500"
                               placeholder="Slip no / txn id">
                    </div>

                    <div class="flex justify-end md:justify-start">
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md
                                       font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700
                                       focus:outline-none">
                            Add Payment
                        </button>
                    </div>
                </form>
            </div>

            {{-- Totals summary --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Labour</span>
                    <span class="text-gray-900 dark:text-gray-100">
                        {{ number_format($job->labour_total, 2) }} MVR
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Travel</span>
                    <span class="text-gray-900 dark:text-gray-100">
                        {{ number_format($job->travel_charges, 2) }} MVR
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Parts</span>
                    <span class="text-gray-900 dark:text-gray-100">
                        {{ number_format($job->parts_total, 2) }} MVR
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Discount</span>
                    <span class="text-gray-900 dark:text-gray-100">
                        -{{ number_format($job->discount, 2) }} MVR
                    </span>
                </div>
                <div class="mt-2 border-t border-gray-200 dark:border-gray-700 pt-2 flex justify-between font-semibold">
                    <span class="text-gray-900 dark:text-gray-100">Total</span>
                    <span class="text-gray-900 dark:text-gray-100">
                        {{ number_format($job->total_amount, 2) }} MVR
                    </span>
                </div>

                <div class="pt-3 flex justify-between items-center">
                    <a href="{{ route('jobs.index') }}"
                       class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                        ← Back to jobs
                    </a>

                    <a href="{{ route('jobs.invoice', $job) }}" target="_blank"
                       class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md
                              font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-900
                              focus:outline-none">
                        View Invoice
                    </a>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
