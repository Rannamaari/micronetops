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
                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $expense->is_paid ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-200' }}">
                    {{ $expense->is_paid ? 'Paid' : 'Due' }}
                </span>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('expenses.edit', $expense) }}" class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                    Edit
                </a>
                @if(Auth::user()->isAdmin())
                    <form method="POST" action="{{ route('expenses.destroy', $expense) }}"
                          onsubmit="return confirm('Delete Expense #{{ $expense->id }}?\n\nThis will {{ $expense->is_paid ? 'restore the account balance and ' : '' }}reverse any inventory stock changes. This cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm">
                            Delete Expense
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-4 sm:py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4 sm:space-y-6">
            @if (session('success'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-200">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-200">
                    {{ $errors->first() }}
                </div>
            @endif

            @if (!$expense->is_paid)
                <div class="overflow-hidden rounded-xl border border-amber-200 bg-amber-50 shadow-sm dark:border-amber-800 dark:bg-amber-900/20">
                    <div class="p-4 sm:p-6">
                        <h3 class="font-semibold text-amber-950 dark:text-amber-100">Payment is due</h3>
                        <p class="mt-1 text-sm text-amber-800 dark:text-amber-200">Record payment when the supplier is paid. The selected account will be deducted once.</p>
                        <form method="POST" action="{{ route('expenses.mark-paid', $expense) }}" class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 sm:items-end">
                            @csrf
                            <div>
                                <label for="payment-account" class="block text-sm font-medium text-gray-800 dark:text-gray-100">Paid From Account <span class="text-red-500">*</span></label>
                                <select id="payment-account" name="account_id" required class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900">
                                    <x-expense-account-options :accounts="$accounts" :selected="old('account_id', $expense->account_id)" />
                                </select>
                            </div>
                            <div>
                                <label for="paid-at" class="block text-sm font-medium text-gray-800 dark:text-gray-100">Payment Date <span class="text-red-500">*</span></label>
                                <input id="paid-at" type="date" name="paid_at" value="{{ old('paid_at', now()->toDateString()) }}" required class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900">
                            </div>
                            <button type="submit" class="rounded-lg bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-800 sm:col-span-2" onclick="return confirm('Mark this expense as paid and deduct MVR {{ number_format($expense->amount, 2) }} from the selected account?')">
                                Mark as Paid
                            </button>
                        </form>
                    </div>
                </div>
            @endif

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
                            @if ($expense->vendorEntity?->gst_number)
                                <dd class="mt-1 text-xs text-gray-500">GST TIN: {{ $expense->vendorEntity->gst_number }}</dd>
                            @endif
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Date</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $expense->incurred_at->format('d M Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ $expense->is_gst_applicable ? 'Total Payable' : 'Amount' }}</dt>
                            <dd class="mt-1 text-base sm:text-lg font-semibold text-gray-900 dark:text-gray-100 tabular-nums">MVR {{ number_format($expense->amount, 2) }}</dd>
                        </div>
                        @if ($expense->is_gst_applicable)
                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Amount Before GST</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">MVR {{ number_format($expense->subtotal_amount, 2) }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-blue-600 dark:text-blue-300 uppercase tracking-wide">GST ({{ number_format($expense->gst_rate, 0) }}%)</dt>
                                <dd class="mt-1 text-sm font-semibold text-blue-700 dark:text-blue-200">MVR {{ number_format($expense->gst_amount, 2) }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Payment Status</dt>
                            <dd class="mt-1 text-sm font-semibold {{ $expense->is_paid ? 'text-emerald-700' : 'text-amber-700' }}">{{ $expense->is_paid ? 'Paid' : 'Due' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ $expense->is_paid ? 'Paid From' : 'Planned Account' }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $expense->account?->name ?? ($expense->is_paid ? '-' : 'Not selected') }}</dd>
                        </div>
                        @if ($expense->is_paid && $expense->paid_at)
                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Paid Date</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $expense->paid_at->format('d M Y') }}</dd>
                            </div>
                        @elseif (!$expense->is_paid && $expense->due_date)
                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Due Date</dt>
                                <dd class="mt-1 text-sm {{ $expense->due_date->isPast() ? 'font-semibold text-red-600' : 'text-gray-900 dark:text-gray-100' }}">{{ $expense->due_date->format('d M Y') }}</dd>
                            </div>
                        @endif
                        @if ($expense->reference)
                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Invoice / Bill Number</dt>
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
