<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Account Logs</h2>
            <a href="{{ route('accounts.index') }}"
               class="inline-flex items-center gap-1 px-3 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                Accounts
            </a>
        </div>
    </x-slot>

    <div class="py-4 sm:py-6">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4 sm:space-y-6">
            {{-- Filters --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6">
                <form method="GET" action="{{ route('accounts.logs') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Account</label>
                        <select name="account_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm text-sm">
                            <option value="">All Accounts</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}" {{ request('account_id') == $account->id ? 'selected' : '' }}>{{ $account->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
                        <select name="type" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm text-sm">
                            <option value="">All Types</option>
                            @foreach(['adjustment', 'expense', 'expense_reversal', 'sale_transfer', 'transfer_in', 'transfer_out', 'eod_cash_in'] as $type)
                                <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>{{ str_replace('_', ' ', ucfirst($type)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">From</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">To</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm text-sm">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 px-4 py-2 bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium rounded-lg transition">Filter</button>
                        <a href="{{ route('accounts.logs') }}" class="px-3 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm rounded-lg transition">Clear</a>
                    </div>
                </form>
            </div>

            {{-- Transactions Table --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-4 sm:px-6 py-3 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">
                        Transactions ({{ $transactions->total() }})
                    </h3>
                </div>

                @if($transactions->isEmpty())
                    <div class="p-6 text-center text-gray-500 dark:text-gray-400 text-sm">No transactions found.</div>
                @else
                    {{-- Desktop Table --}}
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Account</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Description</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($transactions as $tx)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap">{{ $tx->occurred_at?->format('d M Y') }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            <a href="{{ route('accounts.show', $tx->account_id) }}" class="font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
                                                {{ $tx->account?->name ?? 'Deleted' }}
                                            </a>
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            @php
                                                $typeColors = [
                                                    'sale_transfer' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                                    'expense' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                                    'expense_reversal' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
                                                    'adjustment' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
                                                    'transfer_in' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                                    'transfer_out' => 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200',
                                                    'eod_cash_in' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200',
                                                ];
                                                $color = $typeColors[$tx->type] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
                                            @endphp
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $color }}">
                                                {{ str_replace('_', ' ', ucfirst($tx->type)) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $tx->description ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-right font-semibold whitespace-nowrap {{ $tx->amount < 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                            {{ $tx->amount >= 0 ? '+' : '' }}{{ number_format($tx->amount, 2) }} MVR
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Mobile Cards --}}
                    <div class="sm:hidden divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($transactions as $tx)
                            <div class="p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $tx->account?->name ?? 'Deleted' }}</span>
                                            @php
                                                $color = $typeColors[$tx->type] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
                                            @endphp
                                            <span class="text-[10px] px-1.5 py-0.5 rounded font-medium {{ $color }}">{{ str_replace('_', ' ', ucfirst($tx->type)) }}</span>
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $tx->occurred_at?->format('d M Y') }}</div>
                                        @if($tx->description)
                                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $tx->description }}</div>
                                        @endif
                                    </div>
                                    <span class="text-sm font-semibold shrink-0 {{ $tx->amount < 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                        {{ $tx->amount >= 0 ? '+' : '' }}{{ number_format($tx->amount, 2) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if($transactions->hasPages())
                    <div class="px-4 sm:px-6 py-3 border-t border-gray-200 dark:border-gray-700">
                        {{ $transactions->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
