<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Inventory Management
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-4 lg:px-8">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    Inventory Items
                </h3>
                <div class="flex gap-2">
                    <a href="{{ route('inventory-categories.index') }}"
                       class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none">
                        Categories
                    </a>
                    <a href="{{ route('inventory.create') }}"
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none">
                        + New Item
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="mb-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-md p-3 text-sm text-green-600 dark:text-green-400 flex justify-between items-center">
                    <span>{{ session('success') }}</span>
                    @if (session('item_id'))
                        <a href="{{ route('inventory.show', session('item_id')) }}"
                           class="ml-4 text-xs font-medium text-green-700 dark:text-green-300 hover:underline whitespace-nowrap">
                            View Item →
                        </a>
                    @endif
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md p-3 text-sm text-red-600 dark:text-red-400">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Filters --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 mb-4">
                <form method="GET" action="{{ route('inventory.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    {{-- Search --}}
                    <div class="md:col-span-4">
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                        <div class="flex gap-2">
                            <input type="text"
                                   name="search"
                                   value="{{ $search ?? '' }}"
                                   placeholder="Search by name, SKU, or brand..."
                                   class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @if($search ?? false)
                                <a href="{{ route('inventory.index') }}"
                                   class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-500 focus:outline-none">
                                    Clear Search
                                </a>
                            @endif
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Category Type</label>
                        <select name="category_type" class="block w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="all" {{ $categoryType === 'all' ? 'selected' : '' }}>All Types</option>
                            <option value="moto" {{ $categoryType === 'moto' ? 'selected' : '' }}>Moto</option>
                            <option value="ac" {{ $categoryType === 'ac' ? 'selected' : '' }}>AC</option>
                            <option value="both" {{ $categoryType === 'both' ? 'selected' : '' }}>Both</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Item Type</label>
                        <select name="item_type" class="block w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="all" {{ $itemType === 'all' ? 'selected' : '' }}>All</option>
                            <option value="service" {{ $itemType === 'service' ? 'selected' : '' }}>Services</option>
                            <option value="parts" {{ $itemType === 'parts' ? 'selected' : '' }}>Parts</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                        <select name="status" class="block w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All</option>
                            <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Category</label>
                        <select name="category_id" class="block w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="all" {{ $categoryId === 'all' ? 'selected' : '' }}>All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-4 flex gap-2">
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none">
                            Filter
                        </button>
                        <a href="{{ route('inventory.index') }}"
                           class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none">
                            Clear
                        </a>
                    </div>
                </form>
            </div>

            {{-- Inventory Table --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Name</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">SKU</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Brand</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Category</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Type</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-400">Qty</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-400">Cost</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-400">Sell</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($items as $item)
                        <tr onclick="if(!event.target.closest('.action-buttons')) window.location.href='{{ route('inventory.show', $item) }}'"
                            class="touch-row cursor-pointer transition-colors duration-150 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 active:bg-indigo-100 dark:active:bg-indigo-900/30 touch-manipulation {{ $item->isLowStock() && !$item->is_service ? 'bg-yellow-50 dark:bg-yellow-900/10' : '' }}">
                            <td class="px-4 py-4 font-medium text-gray-900 dark:text-gray-100">
                                {{ $item->name }}
                            </td>
                            <td class="px-4 py-4 text-xs text-gray-500 dark:text-gray-400">
                                {{ $item->sku ?? '—' }}
                            </td>
                            <td class="px-4 py-4 text-xs text-gray-500 dark:text-gray-400">
                                {{ $item->brand ?? '—' }}
                            </td>
                            <td class="px-4 py-4 text-xs">
                                <span class="inline-flex px-2 py-1 rounded text-xs font-medium
                                    {{ $item->category === 'moto' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : ($item->category === 'ac' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200') }}">
                                    {{ strtoupper($item->category) }}
                                </span>
                                @if($item->inventoryCategory)
                                    <div class="text-[10px] text-gray-400 mt-1">{{ $item->inventoryCategory->name }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <span class="inline-flex px-2 py-1 rounded text-xs font-medium
                                    {{ $item->is_service ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' : 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200' }}">
                                    {{ $item->is_service ? 'Service' : 'Part' }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-right">
                                @if($item->is_service)
                                    <span class="text-gray-400">—</span>
                                @else
                                    <span class="{{ $item->isLowStock() ? 'font-semibold text-yellow-600 dark:text-yellow-400' : '' }}">
                                        {{ $item->quantity }} {{ $item->unit }}
                                    </span>
                                    @if($item->isLowStock())
                                        <div class="text-[10px] text-yellow-600 dark:text-yellow-400">Low stock!</div>
                                    @endif
                                @endif
                            </td>
                            <td class="px-4 py-4 text-right text-xs text-gray-600 dark:text-gray-400">
                                @if($item->has_gst)
                                    {{ number_format($item->cost_price_with_gst, 2) }} MVR
                                    <div class="text-[10px] text-gray-400">(incl. 8% GST)</div>
                                @else
                                    {{ number_format($item->cost_price, 2) }} MVR
                                @endif
                            </td>
                            <td class="px-4 py-4 text-right text-xs font-medium text-gray-900 dark:text-gray-100">
                                {{ number_format($item->sell_price, 2) }} MVR
                            </td>
                            <td class="px-4 py-4">
                                <span class="inline-flex px-2 py-1 rounded text-xs font-medium
                                    {{ $item->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                    {{ $item->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-right action-buttons" onclick="event.stopPropagation()">
                                <div class="flex gap-2 justify-end flex-wrap">
                                    <a href="{{ route('inventory.edit', $item) }}"
                                       class="px-3 py-1.5 text-xs font-medium text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 rounded hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('inventory.toggle-active', $item) }}" class="inline">
                                        @csrf
                                        <button type="submit"
                                                class="px-3 py-1.5 text-xs font-medium {{ $item->is_active ? 'text-orange-600 dark:text-orange-400 bg-orange-50 dark:bg-orange-900/30 hover:bg-orange-100 dark:hover:bg-orange-900/50' : 'text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/30 hover:bg-green-100 dark:hover:bg-green-900/50' }} rounded transition-colors">
                                            {{ $item->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                    @if(Auth::user()->isAdmin())
                                        <form method="POST" action="{{ route('inventory.destroy', $item) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this item? If the item has been used in jobs, it will be deactivated instead.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="px-3 py-1.5 text-xs font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/30 rounded hover:bg-red-100 dark:hover:bg-red-900/50 transition-colors">
                                                Delete
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                No inventory items found.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $items->links() }}
            </div>
        </div>
    </div>
</x-app-layout>

