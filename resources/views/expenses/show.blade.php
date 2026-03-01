<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                <a href="{{ route('expenses.index') }}" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <h2 class="font-semibold text-lg sm:text-xl text-gray-800 dark:text-gray-200 leading-tight truncate">
                    Expense #{{ $expense->id }}
                </h2>
                <span class="inline-block px-2 py-0.5 text-xs font-medium rounded shrink-0
                    {{ $expense->category?->type === 'cogs' ? 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400' : ($expense->category?->type === 'operating' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-600 dark:text-gray-300') }}">
                    {{ strtoupper($expense->category?->type) }}
                </span>
            </div>
            <a href="{{ route('expenses.edit', $expense) }}" class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm shrink-0">
                Edit
            </a>
        </div>
    </x-slot>

    <div class="py-4 sm:py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4 sm:space-y-6">
            {{-- Expense Summary --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                <div class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/80 px-4 sm:px-6 py-3">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wide">Expense Details</h3>
                </div>
                <div class="p-4 sm:p-6">
                    <dl class="grid grid-cols-2 gap-x-6 gap-y-4 sm:gap-x-8 sm:gap-y-5">
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Category</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $expense->category?->name ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Business Unit</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $businessUnits[$expense->business_unit] ?? $expense->business_unit }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Vendor</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $expense->vendorEntity?->name ?? $expense->vendor ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Date</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $expense->incurred_at->format('d M Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Amount</dt>
                            <dd class="mt-1 text-base sm:text-lg font-semibold text-gray-900 dark:text-gray-100 tabular-nums">MVR {{ number_format($expense->amount, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Paid From</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $expense->account?->name ?? '-' }}</dd>
                        </div>
                        @if ($expense->reference)
                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Reference</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $expense->reference }}</dd>
                            </div>
                        @endif
                        @if ($expense->notes)
                            <div class="col-span-2">
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Notes</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 whitespace-pre-line">{{ $expense->notes }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
                <div class="border-t border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-3 bg-gray-50 dark:bg-gray-800/80">
                    <div class="flex flex-wrap gap-x-6 gap-y-1 text-xs text-gray-500 dark:text-gray-400">
                        @if ($expense->creator)
                            <span>Created by {{ $expense->creator->name }} on {{ $expense->created_at->format('d M Y H:i') }}</span>
                        @endif
                        @if ($expense->updater && $expense->updated_at->ne($expense->created_at))
                            <span>Updated by {{ $expense->updater->name }} on {{ $expense->updated_at->format('d M Y H:i') }}</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- COGS Items --}}
            @if ($expense->inventoryPurchases->isNotEmpty())
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/80 px-4 sm:px-6 py-3 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wide">Items Purchased</h3>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $expense->inventoryPurchases->count() }} item(s)</span>
                    </div>

                    {{-- Desktop Table --}}
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">#</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Item</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">SKU</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Qty</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Unit Cost</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach ($expense->inventoryPurchases as $i => $purchase)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-400">{{ $i + 1 }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                            {{ $purchase->inventoryItem?->name ?? 'Deleted Item' }}
                                            @if ($purchase->inventoryItem?->unit)
                                                <span class="text-xs text-gray-400 ml-1">({{ $purchase->inventoryItem->unit }})</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500">{{ $purchase->inventoryItem?->sku ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-right tabular-nums">{{ rtrim(rtrim(number_format($purchase->quantity, 2), '0'), '.') }}</td>
                                        <td class="px-4 py-3 text-sm text-right tabular-nums">{{ number_format($purchase->unit_cost, 2) }}</td>
                                        <td class="px-4 py-3 text-sm text-right font-medium tabular-nums">{{ number_format($purchase->total_cost, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <td colspan="5" class="px-4 py-3 text-sm font-semibold text-gray-700 dark:text-gray-200 text-right">Items Total</td>
                                    <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-gray-100 text-right tabular-nums">{{ number_format($expense->inventoryPurchases->sum('total_cost'), 2) }}</td>
                                </tr>
                                @if ((float) $expense->amount !== (float) $expense->inventoryPurchases->sum('total_cost'))
                                    <tr>
                                        <td colspan="5" class="px-4 py-2 text-xs text-gray-500 dark:text-gray-400 text-right">Expense Amount (incl. shipping/duty)</td>
                                        <td class="px-4 py-2 text-xs text-gray-500 dark:text-gray-400 text-right tabular-nums">{{ number_format($expense->amount, 2) }}</td>
                                    </tr>
                                @endif
                            </tfoot>
                        </table>
                    </div>

                    {{-- Mobile Cards --}}
                    <div class="sm:hidden divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach ($expense->inventoryPurchases as $i => $purchase)
                            <div class="p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $purchase->inventoryItem?->name ?? 'Deleted Item' }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                            @if ($purchase->inventoryItem?->sku)
                                                SKU: {{ $purchase->inventoryItem->sku }}
                                                <span class="mx-1">&middot;</span>
                                            @endif
                                            {{ rtrim(rtrim(number_format($purchase->quantity, 2), '0'), '.') }} {{ $purchase->inventoryItem?->unit ?? '' }}
                                            x {{ number_format($purchase->unit_cost, 2) }}
                                        </div>
                                    </div>
                                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100 tabular-nums shrink-0">
                                        {{ number_format($purchase->total_cost, 2) }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        <div class="p-4 bg-gray-50 dark:bg-gray-700/50">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Items Total</span>
                                <span class="text-sm font-semibold text-gray-900 dark:text-gray-100 tabular-nums">{{ number_format($expense->inventoryPurchases->sum('total_cost'), 2) }}</span>
                            </div>
                            @if ((float) $expense->amount !== (float) $expense->inventoryPurchases->sum('total_cost'))
                                <div class="flex justify-between items-center mt-1">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Expense Amount (incl. shipping/duty)</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 tabular-nums">{{ number_format($expense->amount, 2) }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
