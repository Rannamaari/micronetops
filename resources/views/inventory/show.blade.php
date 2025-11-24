<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Inventory Item Details
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-4 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-md p-3 text-sm text-green-600 dark:text-green-400">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md p-3 text-sm text-red-600 dark:text-red-400">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Item Details --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ $inventoryItem->name }}
                        </h3>
                        <div class="mt-2 flex gap-2 flex-wrap">
                            <span class="inline-flex px-2 py-1 rounded text-xs font-medium
                                {{ $inventoryItem->category === 'moto' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : ($inventoryItem->category === 'ac' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200') }}">
                                {{ strtoupper($inventoryItem->category) }}
                            </span>
                            <span class="inline-flex px-2 py-1 rounded text-xs font-medium
                                {{ $inventoryItem->is_service ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' : 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200' }}">
                                {{ $inventoryItem->is_service ? 'Service' : 'Part' }}
                            </span>
                            <span class="inline-flex px-2 py-1 rounded text-xs font-medium
                                {{ $inventoryItem->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                {{ $inventoryItem->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                    <a href="{{ route('inventory.edit', $inventoryItem) }}"
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none">
                        Edit
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div>
                        <div class="text-gray-500 dark:text-gray-400">SKU</div>
                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ $inventoryItem->sku ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="text-gray-500 dark:text-gray-400">Brand</div>
                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ $inventoryItem->brand ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="text-gray-500 dark:text-gray-400">Unit</div>
                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ $inventoryItem->unit }}</div>
                    </div>
                    <div>
                        <div class="text-gray-500 dark:text-gray-400">Category</div>
                        <div class="font-medium text-gray-900 dark:text-gray-100">
                            {{ $inventoryItem->inventoryCategory?->name ?? '—' }}
                        </div>
                    </div>
                    <div>
                        <div class="text-gray-500 dark:text-gray-400">Cost Price</div>
                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ number_format($inventoryItem->cost_price, 2) }} MVR</div>
                    </div>
                    <div>
                        <div class="text-gray-500 dark:text-gray-400">Sell Price</div>
                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ number_format($inventoryItem->sell_price, 2) }} MVR</div>
                    </div>
                    @if(!$inventoryItem->is_service)
                        <div>
                            <div class="text-gray-500 dark:text-gray-400">Current Quantity</div>
                            <div class="font-medium text-lg {{ $inventoryItem->isLowStock() ? 'text-yellow-600 dark:text-yellow-400' : 'text-gray-900 dark:text-gray-100' }}">
                                {{ $inventoryItem->quantity }} {{ $inventoryItem->unit }}
                                @if($inventoryItem->isLowStock())
                                    <span class="ml-2 text-xs">⚠ Low Stock!</span>
                                @endif
                            </div>
                        </div>
                        <div>
                            <div class="text-gray-500 dark:text-gray-400">Low Stock Limit</div>
                            <div class="font-medium text-gray-900 dark:text-gray-100">{{ $inventoryItem->low_stock_limit }} {{ $inventoryItem->unit }}</div>
                        </div>
                    @endif
                </div>
            </div>

            @if(!$inventoryItem->is_service)
                {{-- Stock Adjustment Form --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        Adjust Stock
                    </h3>
                    <form method="POST" action="{{ route('inventory.adjust-stock', $inventoryItem) }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @csrf

                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Quantity Change
                            </label>
                            <input type="number" name="quantity_change" required
                                   placeholder="+10 or -5"
                                   class="block w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Positive to add, negative to remove
                            </p>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Notes (optional)
                            </label>
                            <input type="text" name="notes"
                                   placeholder="Reason for adjustment"
                                   class="block w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div class="flex items-end">
                            <button type="submit"
                                    class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md
                                           font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700
                                           focus:outline-none">
                                Adjust Stock
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            {{-- Stock History --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4">
                    Stock History
                </h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 dark:text-gray-400">Date</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 dark:text-gray-400">Type</th>
                            <th class="px-2 py-1 text-right font-medium text-gray-500 dark:text-gray-400">Change</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 dark:text-gray-400">User</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 dark:text-gray-400">Job</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 dark:text-gray-400">Notes</th>
                        </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($logs as $log)
                            <tr>
                                <td class="px-2 py-1 text-gray-500 dark:text-gray-400">
                                    {{ $log->created_at?->format('Y-m-d H:i') }}
                                </td>
                                <td class="px-2 py-1">
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                                        {{ $log->type === 'sale' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 
                                           ($log->type === 'adjustment' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 
                                           ($log->type === 'return' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                                           'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200')) }}">
                                        {{ ucfirst($log->type) }}
                                    </span>
                                </td>
                                <td class="px-2 py-1 text-right font-medium
                                    {{ $log->quantity_change > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ $log->quantity_change > 0 ? '+' : '' }}{{ $log->quantity_change }}
                                </td>
                                <td class="px-2 py-1 text-gray-500 dark:text-gray-400">
                                    {{ $log->user?->name ?? '—' }}
                                </td>
                                <td class="px-2 py-1">
                                    @if($log->job)
                                        <a href="{{ route('jobs.show', $log->job) }}"
                                           class="text-indigo-600 dark:text-indigo-400 hover:underline">
                                            Job #{{ $log->job->id }}
                                        </a>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-2 py-1 text-gray-500 dark:text-gray-400">
                                    {{ $log->notes ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-2 py-4 text-center text-gray-500 dark:text-gray-400">
                                    No stock history found.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $logs->links() }}
                </div>
            </div>

            <div class="flex justify-end">
                <a href="{{ route('inventory.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none">
                    ← Back to Inventory
                </a>
            </div>
        </div>
    </div>
</x-app-layout>

