<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
            <div class="flex items-center gap-3">
                <a href="{{ route('customers.index') }}"
                   class="inline-flex items-center justify-center w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-full hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Customer</div>
                    <h2 class="font-bold text-xl text-gray-800 dark:text-gray-200">
                        {{ $customer->name }}
                    </h2>
                </div>
            </div>

            {{-- Desktop Quick Action in Header --}}
            @if(Auth::user()->canCreateJobs())
                <div class="hidden sm:block">
                    <a href="{{ route('jobs.create', ['customer_id' => $customer->id]) }}"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 rounded-lg font-semibold text-sm text-white shadow-sm transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>Create New Job</span>
                    </a>
                </div>
            @endif
        </div>
    </x-slot>

    <div class="py-4 sm:py-6">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-3 sm:space-y-4">

            {{-- Alerts --}}
            @if (session('success'))
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                    <p class="text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                    <p class="text-sm text-red-800 dark:text-red-200">{{ session('error') }}</p>
                </div>
            @endif

            {{-- Quick Actions - Mobile First --}}
            @if(Auth::user()->canCreateJobs())
                <div class="block sm:hidden">
                    <a href="{{ route('jobs.create', ['customer_id' => $customer->id]) }}"
                       class="flex items-center justify-center gap-2 w-full py-4 bg-indigo-600 hover:bg-indigo-700 rounded-xl font-semibold text-white shadow-lg transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>Create New Job</span>
                    </a>
                </div>
            @endif

            {{-- Customer Info Card --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden">
                <div class="p-5 sm:p-4">
                    <div class="flex justify-between items-start mb-4 sm:mb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-14 h-14 sm:w-12 sm:h-12 bg-indigo-100 dark:bg-indigo-900 rounded-full flex items-center justify-center">
                                <svg class="w-7 h-7 sm:w-6 sm:h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Full Name</div>
                                <div class="text-lg sm:text-base font-bold text-gray-900 dark:text-gray-100">
                                    {{ $customer->name }}
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('customers.edit', $customer) }}"
                           class="inline-flex items-center gap-1 px-3 py-2 sm:px-2.5 sm:py-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            <span class="hidden sm:inline">Edit</span>
                        </a>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="flex items-start gap-3 p-3 sm:p-2.5 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <svg class="w-5 h-5 sm:w-4 sm:h-4 text-green-600 dark:text-green-400 mt-0.5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                            </svg>
                            <div class="flex-1">
                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">WhatsApp</div>
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $customer->phone) }}" target="_blank" class="text-base sm:text-sm font-semibold text-green-600 dark:text-green-400 hover:text-green-700 dark:hover:text-green-300">
                                    {{ $customer->phone }}
                                </a>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 p-3 sm:p-2.5 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <svg class="w-5 h-5 sm:w-4 sm:h-4 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                            <div class="flex-1">
                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Category</div>
                                <span class="inline-flex items-center px-2.5 py-1 sm:px-2 sm:py-0.5 rounded-full text-sm font-medium bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200">
                                    {{ ucfirst($customer->category) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    @if($customer->address)
                        <div class="mt-3 flex items-start gap-3 p-3 sm:p-2.5 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <svg class="w-5 h-5 sm:w-4 sm:h-4 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <div class="flex-1">
                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Address</div>
                                <div class="text-sm text-gray-700 dark:text-gray-300">{{ $customer->address }}</div>
                            </div>
                        </div>
                    @endif

                    @if($customer->notes)
                        <div class="mt-3 p-3 sm:p-2.5 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 sm:w-4 sm:h-4 text-amber-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                <div class="flex-1">
                                    <div class="text-xs text-amber-700 dark:text-amber-400 font-medium mb-1">Notes</div>
                                    <div class="text-sm text-amber-900 dark:text-amber-200 whitespace-pre-line">{{ $customer->notes }}</div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Vehicles & AC units --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-4">
                {{-- Vehicles --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden">
                    <div class="p-4 sm:p-3 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-2.5">
                            <div class="w-10 h-10 sm:w-9 sm:h-9 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 sm:w-5 sm:h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                Vehicles
                            </h3>
                        </div>
                    </div>

                    <div class="p-5">
                        @if($customer->vehicles->isEmpty())
                            <div class="text-center py-8">
                                <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                <p class="text-sm text-gray-500 dark:text-gray-400">No vehicles registered yet</p>
                            </div>
                        @else
                            <div class="space-y-3">
                                @foreach($customer->vehicles as $vehicle)
                                    @php
                                        $rwStatus = $vehicle->roadWorthinessStatus();
                                        $rwExpired = $vehicle->isRoadWorthinessExpired();
                                        $rwExpiringSoon = $rwStatus === 'expiring_soon';

                                        $insStatus = $vehicle->insuranceStatus();
                                        $insExpired = $vehicle->isInsuranceExpired();
                                        $insExpiringSoon = $insStatus === 'expiring_soon';
                                    @endphp
                                    <div class="p-4 rounded-lg border-2 {{ $rwExpired ? 'border-red-300 dark:border-red-700 bg-red-50 dark:bg-red-900/20' : ($rwExpiringSoon ? 'border-yellow-300 dark:border-yellow-700 bg-yellow-50 dark:bg-yellow-900/20' : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30') }}">
                                        <div class="flex justify-between items-start gap-3">
                                            <div class="flex-1">
                                                <div class="font-bold text-base text-gray-900 dark:text-gray-100 mb-1">
                                                    {{ $vehicle->brand }} {{ $vehicle->model }}
                                                </div>
                                                <div class="flex flex-wrap gap-2 text-sm text-gray-600 dark:text-gray-400">
                                                    <span class="inline-flex items-center gap-1">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                                        </svg>
                                                        {{ $vehicle->registration_number ?? 'No Reg' }}
                                                    </span>
                                                    @if($vehicle->year)
                                                        <span>• {{ $vehicle->year }}</span>
                                                    @endif
                                                    @if($vehicle->mileage)
                                                        <span>• {{ number_format($vehicle->mileage) }} km</span>
                                                    @endif
                                                </div>
                                                @if($vehicle->road_worthiness_expires_at)
                                                    <div class="mt-2 text-sm">
                                                        <div class="flex items-center gap-2">
                                                            <svg class="w-4 h-4 {{ $rwExpired ? 'text-red-500' : ($rwExpiringSoon ? 'text-yellow-500' : 'text-green-500') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                            </svg>
                                                            <span class="{{ $rwExpired ? 'text-red-700 dark:text-red-300' : ($rwExpiringSoon ? 'text-yellow-700 dark:text-yellow-300' : 'text-gray-700 dark:text-gray-300') }}">
                                                                <span class="font-medium">RW:</span> {{ $vehicle->road_worthiness_expires_at->format('M d, Y') }}
                                                                @if($rwExpired)
                                                                    <span class="font-bold">(EXPIRED)</span>
                                                                @elseif($rwExpiringSoon)
                                                                    <span>({{ $vehicle->daysUntilExpiry() }} days)</span>
                                                                @endif
                                                            </span>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="mt-2 text-sm text-gray-400 dark:text-gray-500 italic">
                                                        No road worthiness
                                                    </div>
                                                @endif

                                                @if($vehicle->insurance_expires_at)
                                                    <div class="mt-2 text-sm">
                                                        <div class="flex items-center gap-2">
                                                            <svg class="w-4 h-4 {{ $insExpired ? 'text-red-500' : ($insExpiringSoon ? 'text-yellow-500' : 'text-green-500') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                                            </svg>
                                                            <span class="{{ $insExpired ? 'text-red-700 dark:text-red-300' : ($insExpiringSoon ? 'text-yellow-700 dark:text-yellow-300' : 'text-gray-700 dark:text-gray-300') }}">
                                                                <span class="font-medium">Insurance:</span> {{ $vehicle->insurance_expires_at->format('M d, Y') }}
                                                                @if($insExpired)
                                                                    <span class="font-bold">(EXPIRED)</span>
                                                                @elseif($insExpiringSoon)
                                                                    <span>({{ $vehicle->daysUntilInsuranceExpiry() }} days)</span>
                                                                @endif
                                                            </span>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="mt-2 text-sm text-gray-400 dark:text-gray-500 italic">
                                                        No insurance
                                                    </div>
                                                @endif
                                            </div>
                                            @if($vehicle->road_worthiness_expires_at)
                                                <span class="px-3 py-1.5 rounded-lg text-xs font-bold uppercase
                                                    {{ $rwExpired
                                                        ? 'bg-red-600 text-white'
                                                        : ($rwExpiringSoon
                                                            ? 'bg-yellow-500 text-white'
                                                            : 'bg-green-500 text-white') }}">
                                                    {{ $rwStatus === 'expired' ? 'Expired' : ($rwStatus === 'expiring_soon' ? 'Soon' : 'Valid') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- Add Vehicle Form --}}
                        <details class="mt-5 group">
                            <summary class="cursor-pointer list-none">
                                <div class="flex items-center justify-center gap-2 w-full py-3 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg font-medium text-gray-700 dark:text-gray-300 transition">
                                    <svg class="w-5 h-5 group-open:rotate-45 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    <span>Add Vehicle</span>
                                </div>
                            </summary>
                            <form method="POST" action="{{ route('customers.vehicles.store', $customer) }}" class="mt-4 space-y-3 p-4 bg-gray-50 dark:bg-gray-700/30 rounded-lg">
                                @csrf
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <input type="text" name="brand" placeholder="Brand (e.g., Honda)" required
                                           class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500">
                                    <input type="text" name="model" placeholder="Model (e.g., CBR)" required
                                           class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <input type="text" name="registration_number" placeholder="Reg Number"
                                           class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500">
                                    <input type="number" name="year" placeholder="Year"
                                           class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500">
                                    <input type="number" name="mileage" placeholder="Mileage (km)"
                                           class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <button type="submit"
                                        class="w-full sm:w-auto px-6 py-3 bg-indigo-600 hover:bg-indigo-700 border border-transparent rounded-lg
                                               font-semibold text-white transition">
                                    Save Vehicle
                                </button>
                            </form>
                        </details>
                    </div>
                </div>

                {{-- AC Units --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden">
                    <div class="p-5 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-cyan-100 dark:bg-cyan-900 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-cyan-600 dark:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                AC Units
                            </h3>
                        </div>
                    </div>

                    <div class="p-5">
                        @if($customer->acUnits->isEmpty())
                            <div class="text-center py-8">
                                <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path>
                                </svg>
                                <p class="text-sm text-gray-500 dark:text-gray-400">No AC units registered yet</p>
                            </div>
                        @else
                            <div class="space-y-3">
                                @foreach($customer->acUnits as $ac)
                                    <div class="p-4 rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30">
                                        <div class="font-bold text-base text-gray-900 dark:text-gray-100 mb-2">
                                            {{ $ac->brand ?? 'AC Unit' }}
                                            @if($ac->btu)
                                                <span class="ml-2 px-2 py-1 bg-cyan-100 dark:bg-cyan-900 text-cyan-800 dark:text-cyan-200 text-sm font-semibold rounded">
                                                    {{ $ac->btu }} BTU
                                                </span>
                                            @endif
                                        </div>
                                        <div class="space-y-1 text-sm">
                                            @if($ac->gas_type)
                                                <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                                                    </svg>
                                                    <span><span class="font-medium">Gas:</span> {{ $ac->gas_type }}</span>
                                                </div>
                                            @endif
                                            @if($ac->location_description)
                                                <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    </svg>
                                                    <span><span class="font-medium">Location:</span> {{ $ac->location_description }}</span>
                                                </div>
                                            @endif
                                            @if($ac->indoor_units || $ac->outdoor_units)
                                                <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                                    </svg>
                                                    <span>
                                                        {{ $ac->indoor_units ?? 0 }} Indoor / {{ $ac->outdoor_units ?? 0 }} Outdoor
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- Add AC Unit Form --}}
                        <details class="mt-5 group">
                            <summary class="cursor-pointer list-none">
                                <div class="flex items-center justify-center gap-2 w-full py-3 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg font-medium text-gray-700 dark:text-gray-300 transition">
                                    <svg class="w-5 h-5 group-open:rotate-45 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    <span>Add AC Unit</span>
                                </div>
                            </summary>
                            <form method="POST" action="{{ route('customers.ac-units.store', $customer) }}" class="mt-4 space-y-3 p-4 bg-gray-50 dark:bg-gray-700/30 rounded-lg">
                                @csrf
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <input type="text" name="brand" placeholder="Brand (e.g., Daikin)" required
                                           class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500">
                                    <input type="number" name="btu" placeholder="BTU (e.g., 12000)"
                                           class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <input type="text" name="gas_type" placeholder="Gas Type (R32/R410)"
                                           class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500">
                                    <input type="text" name="location_description" placeholder="Location (Living Room)"
                                           class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <input type="number" name="indoor_units" placeholder="Indoor Units"
                                           class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500">
                                    <input type="number" name="outdoor_units" placeholder="Outdoor Units"
                                           class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <button type="submit"
                                        class="w-full sm:w-auto px-6 py-3 bg-indigo-600 hover:bg-indigo-700 border border-transparent rounded-lg
                                               font-semibold text-white transition">
                                    Save AC Unit
                                </button>
                            </form>
                        </details>
                    </div>
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

