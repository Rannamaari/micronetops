<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Customer: {{ $customer->name }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-4 lg:px-8 space-y-6">

            {{-- Alerts --}}
            @if (session('success'))
                <div class="text-sm text-green-600 dark:text-green-400">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="text-sm text-red-600 dark:text-red-400">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Customer info --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Customer</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ $customer->name }}
                        </div>
                        <div class="text-sm text-gray-700 dark:text-gray-300">
                            {{ $customer->phone }}
                        </div>
                        @if($customer->address)
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                {{ $customer->address }}
                            </div>
                        @endif
                    </div>
                    <div class="text-right text-xs text-gray-500 dark:text-gray-400">
                        Category: <span class="font-semibold text-gray-800 dark:text-gray-200">
                            {{ ucfirst($customer->category) }}
                        </span>
                        <div>
                            <a href="{{ route('customers.edit', $customer) }}"
                               class="text-indigo-600 dark:text-indigo-400 hover:underline">
                                Edit
                            </a>
                        </div>
                    </div>
                </div>

                @if($customer->notes)
                    <div class="mt-3 text-sm text-gray-700 dark:text-gray-300">
                        <div class="font-medium text-gray-600 dark:text-gray-400 mb-1">Notes</div>
                        <div class="whitespace-pre-line">{{ $customer->notes }}</div>
                    </div>
                @endif
            </div>

            {{-- Vehicles & AC units --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Vehicles --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6 space-y-3">
                    <div class="flex justify-between items-center">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                            Vehicles (Bikes)
                        </h3>
                    </div>

                    @if($customer->vehicles->isEmpty())
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            No vehicles yet.
                        </p>
                    @else
                        <ul class="space-y-2 text-sm">
                            @foreach($customer->vehicles as $vehicle)
                                @php
                                    $rwStatus = $vehicle->roadWorthinessStatus();
                                    $rwExpired = $vehicle->isRoadWorthinessExpired();
                                    $rwExpiringSoon = $rwStatus === 'expiring_soon';
                                @endphp
                                <li class="border rounded px-2 py-1 {{ $rwExpired ? 'border-red-300 dark:border-red-700 bg-red-50 dark:bg-red-900/20' : ($rwExpiringSoon ? 'border-yellow-300 dark:border-yellow-700 bg-yellow-50 dark:bg-yellow-900/20' : 'border-gray-200 dark:border-gray-700') }}">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <div class="font-medium text-gray-900 dark:text-gray-100">
                                                {{ $vehicle->brand }} {{ $vehicle->model }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                Reg: {{ $vehicle->registration_number ?? '—' }}
                                                @if($vehicle->year)
                                                    • {{ $vehicle->year }}
                                                @endif
                                                @if($vehicle->mileage)
                                                    • {{ $vehicle->mileage }} km
                                                @endif
                                            </div>
                                            @if($vehicle->road_worthiness_expires_at)
                                                <div class="text-xs mt-1">
                                                    <span class="font-medium">Road Worthiness:</span>
                                                    <span class="{{ $rwExpired ? 'text-red-600 dark:text-red-400' : ($rwExpiringSoon ? 'text-yellow-600 dark:text-yellow-400' : 'text-gray-600 dark:text-gray-400') }}">
                                                        Expires {{ $vehicle->road_worthiness_expires_at->format('Y-m-d') }}
                                                        @if($rwExpired)
                                                            <span class="font-bold">(EXPIRED)</span>
                                                        @elseif($rwExpiringSoon)
                                                            <span>({{ $vehicle->daysUntilExpiry() }} days left)</span>
                                                        @endif
                                                    </span>
                                                </div>
                                            @else
                                                <div class="text-xs mt-1 text-gray-400 dark:text-gray-500">
                                                    No road worthiness certificate
                                                </div>
                                            @endif
                                        </div>
                                        @if($vehicle->road_worthiness_expires_at)
                                            <span class="ml-2 px-2 py-0.5 rounded text-[10px] font-medium
                                                {{ $rwExpired 
                                                    ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' 
                                                    : ($rwExpiringSoon 
                                                        ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' 
                                                        : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200') }}">
                                                {{ $rwStatus === 'expired' ? 'EXPIRED' : ($rwStatus === 'expiring_soon' ? 'EXPIRING' : 'VALID') }}
                                            </span>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    <form method="POST" action="{{ route('customers.vehicles.store', $customer) }}" class="space-y-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                        @csrf
                        <div class="text-xs font-medium text-gray-700 dark:text-gray-300">
                            Add Vehicle
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="text" name="brand" placeholder="Brand"
                                   class="rounded-md border-gray-300 text-xs focus:border-indigo-500 focus:ring-indigo-500">
                            <input type="text" name="model" placeholder="Model"
                                   class="rounded-md border-gray-300 text-xs focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <input type="text" name="registration_number" placeholder="Reg No."
                                   class="rounded-md border-gray-300 text-xs focus:border-indigo-500 focus:ring-indigo-500">
                            <input type="number" name="year" placeholder="Year"
                                   class="rounded-md border-gray-300 text-xs focus:border-indigo-500 focus:ring-indigo-500">
                            <input type="number" name="mileage" placeholder="Mileage"
                                   class="rounded-md border-gray-300 text-xs focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="flex justify-end">
                            <button type="submit"
                                    class="inline-flex items-center px-3 py-1.5 bg-indigo-600 border border-transparent rounded-md
                                           font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700
                                           focus:outline-none">
                                Save
                            </button>
                        </div>
                    </form>
                </div>

                {{-- AC Units --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6 space-y-3">
                    <div class="flex justify-between items-center">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                            AC Units
                        </h3>
                    </div>

                    @if($customer->acUnits->isEmpty())
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            No AC units yet.
                        </p>
                    @else
                        <ul class="space-y-2 text-sm">
                            @foreach($customer->acUnits as $ac)
                                <li class="border border-gray-200 dark:border-gray-700 rounded px-2 py-1">
                                    <div class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ $ac->brand ?? 'AC' }} - {{ $ac->btu ? $ac->btu . ' BTU' : '' }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        Gas: {{ $ac->gas_type ?? 'N/A' }}
                                        @if($ac->location_description)
                                            • {{ $ac->location_description }}
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    <form method="POST" action="{{ route('customers.ac-units.store', $customer) }}" class="space-y-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                        @csrf
                        <div class="text-xs font-medium text-gray-700 dark:text-gray-300">
                            Add AC Unit
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="text" name="brand" placeholder="Brand"
                                   class="rounded-md border-gray-300 text-xs focus:border-indigo-500 focus:ring-indigo-500">
                            <input type="number" name="btu" placeholder="BTU"
                                   class="rounded-md border-gray-300 text-xs focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="text" name="gas_type" placeholder="Gas (R32/R410)"
                                   class="rounded-md border-gray-300 text-xs focus:border-indigo-500 focus:ring-indigo-500">
                            <input type="text" name="location_description" placeholder="Location (Living, BR...)"
                                   class="rounded-md border-gray-300 text-xs focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="number" name="indoor_units" placeholder="Indoor units"
                                   class="rounded-md border-gray-300 text-xs focus:border-indigo-500 focus:ring-indigo-500">
                            <input type="number" name="outdoor_units" placeholder="Outdoor units"
                                   class="rounded-md border-gray-300 text-xs focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="flex justify-end">
                            <button type="submit"
                                    class="inline-flex items-center px-3 py-1.5 bg-indigo-600 border border-transparent rounded-md
                                           font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700
                                           focus:outline-none">
                                Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Recent jobs for this customer (optional preview) --}}
            @if($customer->jobs->isNotEmpty())
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">
                        Recent Jobs
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-xs divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-2 py-1 text-left font-medium text-gray-500 dark:text-gray-400">ID</th>
                                <th class="px-2 py-1 text-left font-medium text-gray-500 dark:text-gray-400">Type</th>
                                <th class="px-2 py-1 text-left font-medium text-gray-500 dark:text-gray-400">Category</th>
                                <th class="px-2 py-1 text-left font-medium text-gray-500 dark:text-gray-400">Status</th>
                                <th class="px-2 py-1 text-left font-medium text-gray-500 dark:text-gray-400">Total</th>
                                <th class="px-2 py-1"></th>
                            </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($customer->jobs->take(10) as $job)
                                <tr>
                                    <td class="px-2 py-1">#{{ $job->id }}</td>
                                    <td class="px-2 py-1">{{ strtoupper($job->job_type) }}</td>
                                    <td class="px-2 py-1">{{ $job->job_category }}</td>
                                    <td class="px-2 py-1">{{ ucfirst($job->status) }}</td>
                                    <td class="px-2 py-1">{{ number_format($job->total_amount, 2) }} MVR</td>
                                    <td class="px-2 py-1 text-right">
                                        <a href="{{ route('jobs.show', $job) }}"
                                           class="text-indigo-600 dark:text-indigo-400 hover:underline">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>

