<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Purchase Orders
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Create and manage supplier purchase orders for all business units.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('sales.daily.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 text-sm font-medium rounded-lg transition dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-100">
                    Back to Sales
                </a>
                <a href="{{ route('sales.purchase-orders.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                    New Purchase Order
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-4 sm:py-6">
        <div class="max-w-6xl mx-auto px-4 sm:px-4 lg:px-8 space-y-4">
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm dark:bg-green-900/30 dark:border-green-800 dark:text-green-300">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm dark:bg-red-900/30 dark:border-red-800 dark:text-red-300">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6">
                <form method="GET" action="{{ route('sales.purchase-orders.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Business Unit</label>
                        <select name="business_unit" class="w-full h-10 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm">
                            <option value="">All units</option>
                            @foreach($businessUnits as $key => $label)
                                <option value="{{ $key }}" @selected($filters['business_unit'] === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                        <select name="status" class="w-full h-10 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm">
                            <option value="">All statuses</option>
                            @foreach($statuses as $key => $label)
                                <option value="{{ $key }}" @selected($filters['status'] === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Supplier</label>
                        <input type="text" name="vendor" value="{{ $filters['vendor'] }}" placeholder="Search supplier name"
                               class="w-full h-10 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm">
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="h-10 px-4 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                            Filter
                        </button>
                        <a href="{{ route('sales.purchase-orders.index') }}" class="h-10 px-4 inline-flex items-center justify-center bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200">
                            Clear
                        </a>
                    </div>
                </form>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                @if($purchaseOrders->isEmpty())
                    <div class="p-6 text-center text-sm text-gray-500 dark:text-gray-400">
                        No purchase orders found yet.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">PO #</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Unit</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Supplier</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($purchaseOrders as $purchaseOrder)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $purchaseOrder->po_number ?? 'Pending' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $purchaseOrder->order_date?->format('d M Y') }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $businessUnits[$purchaseOrder->business_unit] ?? $purchaseOrder->business_unit }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $purchaseOrder->vendor_name }}</td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium
                                                {{ $purchaseOrder->status === \App\Models\PurchaseOrder::STATUS_DRAFT ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '' }}
                                                {{ $purchaseOrder->status === \App\Models\PurchaseOrder::STATUS_ISSUED ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                                                {{ $purchaseOrder->status === \App\Models\PurchaseOrder::STATUS_CANCELLED ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : '' }}">
                                                {{ $purchaseOrder->status_label }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right font-medium text-gray-900 dark:text-gray-100">{{ number_format($purchaseOrder->total_amount, 2) }} MVR</td>
                                        <td class="px-4 py-3">
                                            <div class="flex flex-wrap items-center justify-center gap-2">
                                                <a href="{{ route('sales.purchase-orders.show', $purchaseOrder) }}" class="inline-flex px-3 py-1.5 text-xs font-medium rounded-lg bg-indigo-100 text-indigo-700 hover:bg-indigo-200 dark:bg-indigo-900/40 dark:text-indigo-300">Open</a>
                                                <a href="{{ route('sales.purchase-orders.print', $purchaseOrder) }}" target="_blank" class="inline-flex px-3 py-1.5 text-xs font-medium rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200">Print</a>
                                                @if($purchaseOrder->canEdit())
                                                    <a href="{{ route('sales.purchase-orders.edit', $purchaseOrder) }}" class="inline-flex px-3 py-1.5 text-xs font-medium rounded-lg bg-amber-100 text-amber-700 hover:bg-amber-200 dark:bg-amber-900/40 dark:text-amber-300">Edit</a>
                                                @endif
                                                @if($purchaseOrder->status === \App\Models\PurchaseOrder::STATUS_CANCELLED)
                                                    <form method="POST" action="{{ route('sales.purchase-orders.resubmit', $purchaseOrder) }}" onsubmit="return confirm('Create a new purchase order from this cancelled PO with a new number?')">
                                                        @csrf
                                                        <button type="submit" class="inline-flex px-3 py-1.5 text-xs font-medium rounded-lg bg-indigo-100 text-indigo-700 hover:bg-indigo-200 dark:bg-indigo-900/40 dark:text-indigo-300">Resubmit</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                        {{ $purchaseOrders->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
