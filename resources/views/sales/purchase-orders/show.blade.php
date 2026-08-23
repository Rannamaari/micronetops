<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ $purchaseOrder->po_number }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $brand['name'] }} supplier purchase order</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('sales.purchase-orders.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 text-sm font-medium rounded-lg transition dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-100">Back</a>
                <a href="{{ route('sales.purchase-orders.print', $purchaseOrder) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-700 hover:bg-slate-800 text-white text-sm font-medium rounded-lg transition">Print PO</a>
                @if($purchaseOrder->canEdit())
                    <a href="{{ route('sales.purchase-orders.edit', $purchaseOrder) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium rounded-lg transition">Edit</a>
                    @if($purchaseOrder->status === \App\Models\PurchaseOrder::STATUS_DRAFT)
                        <form method="POST" action="{{ route('sales.purchase-orders.issue', $purchaseOrder) }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition">Mark Issued</button>
                        </form>
                    @endif
                @endif
                @if($purchaseOrder->status === \App\Models\PurchaseOrder::STATUS_CANCELLED)
                    <form method="POST" action="{{ route('sales.purchase-orders.resubmit', $purchaseOrder) }}" onsubmit="return confirm('Create a new purchase order from this cancelled PO with a new number?')">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">Resubmit As New PO</button>
                    </form>
                @endif
                @if($purchaseOrder->status !== \App\Models\PurchaseOrder::STATUS_CANCELLED)
                    <form method="POST" action="{{ route('sales.purchase-orders.cancel', $purchaseOrder) }}" onsubmit="return confirm('Cancel this purchase order?')">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition">Cancel PO</button>
                    </form>
                @endif
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

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 shadow-sm rounded-2xl p-5">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $brand['name'] }}</h3>
                            <p class="mt-3 text-sm text-gray-600 dark:text-gray-300">{{ $brand['address'] }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-300">{{ $brand['phone'] }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-300">{{ $brand['email'] }}</p>
                        </div>
                        <div class="text-left sm:text-right">
                            <div class="text-sm text-gray-500 dark:text-gray-400">Status</div>
                            <div class="mt-1">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $purchaseOrder->status === \App\Models\PurchaseOrder::STATUS_DRAFT ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '' }}
                                    {{ $purchaseOrder->status === \App\Models\PurchaseOrder::STATUS_ISSUED ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                                    {{ $purchaseOrder->status === \App\Models\PurchaseOrder::STATUS_CANCELLED ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : '' }}">
                                    {{ $purchaseOrder->status_label }}
                                </span>
                            </div>
                            <div class="mt-3 text-sm text-gray-700 dark:text-gray-300">Order Date: {{ $purchaseOrder->order_date?->format('d M Y') }}</div>
                            <div class="text-sm text-gray-700 dark:text-gray-300">Expected: {{ $purchaseOrder->expected_date?->format('d M Y') ?? 'Not set' }}</div>
                            @if($purchaseOrder->issued_at)
                                <div class="text-sm text-gray-700 dark:text-gray-300">Issued: {{ $purchaseOrder->issued_at->format('d M Y g:i A') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="mt-5 border-t border-gray-200 dark:border-gray-700 pt-4">
                        <form method="POST" action="{{ route('sales.purchase-orders.update-number', $purchaseOrder) }}" class="flex flex-col sm:flex-row sm:items-end gap-3">
                            @csrf
                            @method('PATCH')
                            <div class="w-full sm:max-w-xs">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Amend PO Number</label>
                                <input type="text" name="po_number" value="{{ old('po_number', $purchaseOrder->po_number) }}"
                                       class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" required>
                            </div>
                            <button type="submit" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-slate-700 hover:bg-slate-800 text-white text-sm font-medium transition">
                                Update Number
                            </button>
                        </form>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-2xl p-5">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wide">Supplier</h3>
                    <div class="mt-3 space-y-2 text-sm text-gray-700 dark:text-gray-300">
                        <div class="font-semibold text-base text-gray-900 dark:text-gray-100">{{ $purchaseOrder->vendor_name }}</div>
                        @if($purchaseOrder->vendor_contact_name)
                            <div>Contact: {{ $purchaseOrder->vendor_contact_name }}</div>
                        @endif
                        @if($purchaseOrder->vendor_phone)
                            <div>Phone: {{ $purchaseOrder->vendor_phone }}</div>
                        @endif
                        @if($purchaseOrder->vendor_address)
                            <div>{{ $purchaseOrder->vendor_address }}</div>
                        @endif
                        @if($purchaseOrder->reference)
                            <div>Reference: {{ $purchaseOrder->reference }}</div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-2xl p-5 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                        <tr class="text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            <th class="px-3 py-2">Description</th>
                            <th class="px-3 py-2 text-right">Qty</th>
                            <th class="px-3 py-2">Unit</th>
                            <th class="px-3 py-2 text-right">Unit Cost</th>
                            <th class="px-3 py-2 text-right">Line Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($purchaseOrder->lines as $line)
                            <tr>
                                <td class="px-3 py-3 text-sm text-gray-900 dark:text-gray-100">
                                    <div>{{ $line->description }}</div>
                                    @if($line->notes)
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $line->notes }}</div>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-sm text-right text-gray-700 dark:text-gray-300">{{ number_format($line->quantity, 2) }}</td>
                                <td class="px-3 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $line->unit ?: '—' }}</td>
                                <td class="px-3 py-3 text-sm text-right text-gray-700 dark:text-gray-300">{{ number_format($line->unit_cost, 2) }}</td>
                                <td class="px-3 py-3 text-sm text-right font-medium text-gray-900 dark:text-gray-100">{{ number_format($line->line_total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 shadow-sm rounded-2xl p-5 space-y-4">
                    @if($purchaseOrder->notes)
                        <div>
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-900 dark:text-gray-100">Notes</h3>
                            <p class="mt-2 text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $purchaseOrder->notes }}</p>
                        </div>
                    @endif
                    @if($purchaseOrder->terms)
                        <div>
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-900 dark:text-gray-100">Terms</h3>
                            <p class="mt-2 text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $purchaseOrder->terms }}</p>
                        </div>
                    @endif
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-2xl p-5">
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm text-gray-700 dark:text-gray-300">
                            <span>Subtotal</span>
                            <span>{{ number_format($purchaseOrder->subtotal, 2) }} MVR</span>
                        </div>
                        <div class="flex justify-between text-base font-bold text-gray-900 dark:text-gray-100 border-t border-gray-200 dark:border-gray-700 pt-3">
                            <span>Total</span>
                            <span>{{ number_format($purchaseOrder->total_amount, 2) }} MVR</span>
                        </div>
                        <div class="pt-3 text-xs text-gray-500 dark:text-gray-400">
                            Prepared by {{ $purchaseOrder->creator?->name ?? 'System' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
