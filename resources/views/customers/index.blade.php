<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Customers
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-4 lg:px-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    All Customers
                </h3>

                {{-- Search Bar --}}
                <div class="flex flex-1 max-w-md gap-2">
                    <form method="GET" action="{{ route('customers.index') }}" class="flex-1 flex gap-2">
                        <input type="text"
                               name="search"
                               value="{{ $search ?? '' }}"
                               placeholder="Search by name, phone, email, or address..."
                               class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none">
                            Search
                        </button>
                        @if($search ?? false)
                            <a href="{{ route('customers.index') }}"
                               class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-500 focus:outline-none">
                                Clear
                            </a>
                        @endif
                    </form>
                </div>

                <a href="{{ route('customers.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none whitespace-nowrap">
                    + New Customer
                </a>
            </div>

            @if (session('success'))
                <div class="mb-4 text-sm text-green-600 dark:text-green-400">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 text-sm text-red-600 dark:text-red-400">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Last Customer Notice --}}
            @if($lastCustomer)
                <div class="mb-4 bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded-lg p-4">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div class="flex-1">
                            <div class="text-sm font-semibold text-indigo-900 dark:text-indigo-100">
                                Last Customer Added
                            </div>
                            <div class="text-sm text-indigo-800 dark:text-indigo-200 mt-1">
                                <strong>{{ $lastCustomer->name }}</strong> - {{ $lastCustomer->phone }}
                                <span class="text-xs text-indigo-600 dark:text-indigo-400 ml-2">
                                    ({{ $lastCustomer->created_at?->diffForHumans() }})
                                </span>
                            </div>
                        </div>
                        <a href="{{ route('customers.show', $lastCustomer) }}"
                           class="inline-flex items-center px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 rounded-md text-xs font-medium text-white transition">
                            View
                        </a>
                    </div>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Name</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Phone</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Category</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Created</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($customers as $customer)
                        @php
                            $hasExpiredRW = $customer->hasExpiredRoadWorthiness();
                        @endphp
                        <tr onclick="window.location.href='{{ route('customers.show', $customer) }}'"
                            class="touch-row cursor-pointer transition-colors duration-150 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 active:bg-indigo-100 dark:active:bg-indigo-900/30 touch-manipulation {{ $hasExpiredRW ? 'bg-red-50 dark:bg-red-900/10' : '' }}">
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-gray-900 dark:text-gray-100 font-medium">{{ $customer->name }}</span>
                                    @if($hasExpiredRW)
                                        <span class="px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                            Expired RW
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-300">
                                {{ $customer->phone }}
                            </td>
                            <td class="px-4 py-4">
                                <span class="inline-flex px-2 py-1 rounded text-xs font-medium
                                    {{ $customer->category === 'moto' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 
                                       ($customer->category === 'ac' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                                       'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200') }}">
                                    {{ ucfirst($customer->category) }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-xs text-gray-500 dark:text-gray-400">
                                <div>{{ $customer->created_at?->format('Y-m-d') }}</div>
                                <div class="text-gray-400 dark:text-gray-500">{{ $customer->created_at?->format('H:i') }}</div>
                            </td>
                            <td class="px-4 py-4" onclick="event.stopPropagation()">
                                @if(Auth::user()->canDelete())
                                    <form action="{{ route('customers.destroy', $customer) }}" method="POST"
                                          onsubmit="return confirm('Are you sure you want to delete this customer? All associated vehicles and AC units will also be deleted.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 font-medium text-xs">
                                            Delete
                                        </button>
                                    </form>
                                @else
                                    <span class="text-gray-400 dark:text-gray-600 text-xs">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                No customers yet.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $customers->links() }}
            </div>
        </div>
    </div>
</x-app-layout>

