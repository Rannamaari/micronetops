<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Road Worthiness Report
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-4 lg:px-8 space-y-6">
            {{-- Month selector --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4">
                <form method="GET" action="{{ route('reports.road-worthiness') }}" class="flex items-center gap-3">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Month:</label>
                    <input type="month" name="month" value="{{ $month }}"
                           class="rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md
                                   font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700
                                   focus:outline-none">
                        View Report
                    </button>
                </form>
            </div>

            {{-- Expired --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3 text-red-600 dark:text-red-400">
                    Expired Road Worthiness ({{ $expired->count() }})
                </h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 dark:text-gray-400">Customer</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 dark:text-gray-400">Vehicle</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 dark:text-gray-400">Reg No.</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 dark:text-gray-400">Expired Date</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 dark:text-gray-400">Days Overdue</th>
                        </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($expired as $vehicle)
                            <tr class="bg-red-50 dark:bg-red-900/10">
                                <td class="px-2 py-1">{{ $vehicle->customer?->name }}</td>
                                <td class="px-2 py-1">{{ $vehicle->brand }} {{ $vehicle->model }}</td>
                                <td class="px-2 py-1">{{ $vehicle->registration_number ?? '—' }}</td>
                                <td class="px-2 py-1">{{ $vehicle->road_worthiness_expires_at?->format('Y-m-d') }}</td>
                                <td class="px-2 py-1 font-semibold text-red-600 dark:text-red-400">
                                    {{ abs($vehicle->daysUntilExpiry()) }} days
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-2 py-2 text-center text-gray-500 dark:text-gray-400">
                                    No expired road worthiness certificates.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Expiring Soon --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3 text-yellow-600 dark:text-yellow-400">
                    Expiring Soon (Next 30 Days) ({{ $expiringSoon->count() }})
                </h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 dark:text-gray-400">Customer</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 dark:text-gray-400">Vehicle</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 dark:text-gray-400">Reg No.</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 dark:text-gray-400">Expires</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 dark:text-gray-400">Days Left</th>
                        </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($expiringSoon as $vehicle)
                            <tr class="bg-yellow-50 dark:bg-yellow-900/10">
                                <td class="px-2 py-1">{{ $vehicle->customer?->name }}</td>
                                <td class="px-2 py-1">{{ $vehicle->brand }} {{ $vehicle->model }}</td>
                                <td class="px-2 py-1">{{ $vehicle->registration_number ?? '—' }}</td>
                                <td class="px-2 py-1">{{ $vehicle->road_worthiness_expires_at?->format('Y-m-d') }}</td>
                                <td class="px-2 py-1 font-semibold text-yellow-600 dark:text-yellow-400">
                                    {{ $vehicle->daysUntilExpiry() }} days
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-2 py-2 text-center text-gray-500 dark:text-gray-400">
                                    No vehicles expiring soon.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Expiring This Month --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">
                    Expiring This Month ({{ $expiringThisMonth->count() }})
                </h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 dark:text-gray-400">Customer</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 dark:text-gray-400">Vehicle</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 dark:text-gray-400">Reg No.</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 dark:text-gray-400">Expires</th>
                        </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($expiringThisMonth as $vehicle)
                            <tr>
                                <td class="px-2 py-1">{{ $vehicle->customer?->name }}</td>
                                <td class="px-2 py-1">{{ $vehicle->brand }} {{ $vehicle->model }}</td>
                                <td class="px-2 py-1">{{ $vehicle->registration_number ?? '—' }}</td>
                                <td class="px-2 py-1">{{ $vehicle->road_worthiness_expires_at?->format('Y-m-d') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-2 py-2 text-center text-gray-500 dark:text-gray-400">
                                    No vehicles expiring this month.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Recently Issued This Month --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">
                    Recently Issued This Month ({{ $recentlyIssued->count() }})
                </h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 dark:text-gray-400">Customer</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 dark:text-gray-400">Vehicle</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 dark:text-gray-400">Reg No.</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 dark:text-gray-400">Issued</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 dark:text-gray-400">Expires</th>
                        </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($recentlyIssued as $vehicle)
                            <tr>
                                <td class="px-2 py-1">{{ $vehicle->customer?->name }}</td>
                                <td class="px-2 py-1">{{ $vehicle->brand }} {{ $vehicle->model }}</td>
                                <td class="px-2 py-1">{{ $vehicle->registration_number ?? '—' }}</td>
                                <td class="px-2 py-1">{{ $vehicle->road_worthiness_created_at?->format('Y-m-d') }}</td>
                                <td class="px-2 py-1">{{ $vehicle->road_worthiness_expires_at?->format('Y-m-d') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-2 py-2 text-center text-gray-500 dark:text-gray-400">
                                    No road worthiness certificates issued this month.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

