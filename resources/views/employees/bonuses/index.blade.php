<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="{{ route('employees.show', $employee) }}" class="inline-flex items-center justify-center w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-full hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Bonuses</div>
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                        {{ $employee->name }}
                    </h2>
                </div>
            </div>
            <a href="{{ route('employees.bonuses.create', $employee) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add Bonus
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-4 lg:px-8">
            {{-- Success Message --}}
            @if (session('success'))
                <div class="mb-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-md p-3">
                    <p class="text-sm text-green-600 dark:text-green-400">{{ session('success') }}</p>
                </div>
            @endif

            {{-- Bonus Summary --}}
            <div class="mb-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4 border-l-4 border-green-500">
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Active Bonuses</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{ $bonuses->where('status', 'active')->count() }}
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4 border-l-4 border-yellow-500">
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Monthly Bonuses</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{ number_format($bonuses->where('status', 'active')->where('frequency', 'monthly')->sum('amount'), 2) }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">MVR</div>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4 border-l-4 border-blue-500">
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Total Active Value</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{ number_format($bonuses->where('status', 'active')->sum('amount'), 2) }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">MVR</div>
                </div>
            </div>

            {{-- Bonus List --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Amount</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Frequency</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Period</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($bonuses as $bonus)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ ucfirst(str_replace('_', ' ', $bonus->bonus_type)) }}</div>
                                        @if($bonus->reason)
                                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $bonus->reason }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ number_format($bonus->amount, 2) }} MVR</div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            @if($bonus->frequency === 'one_time') bg-gray-100 text-gray-800
                                            @elseif($bonus->frequency === 'monthly') bg-green-100 text-green-800
                                            @elseif($bonus->frequency === 'quarterly') bg-blue-100 text-blue-800
                                            @else bg-purple-100 text-purple-800 @endif">
                                            {{ ucfirst(str_replace('_', ' ', $bonus->frequency)) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-gray-100">{{ $bonus->awarded_date->format('M d, Y') }}</div>
                                        @if($bonus->end_date)
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                Until {{ $bonus->end_date->format('M d, Y') }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            @if($bonus->status === 'active') bg-green-100 text-green-800
                                            @elseif($bonus->status === 'expired') bg-gray-100 text-gray-800
                                            @else bg-red-100 text-red-800 @endif">
                                            {{ ucfirst($bonus->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('employees.bonuses.edit', [$employee, $bonus]) }}" class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 mr-3">Edit</a>
                                        <form action="{{ route('employees.bonuses.destroy', [$employee, $bonus]) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this bonus?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                        No bonuses found. <a href="{{ route('employees.bonuses.create', $employee) }}" class="text-indigo-600 hover:underline">Add the first bonus</a>
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
