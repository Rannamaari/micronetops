<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Petty Cash
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-4 lg:px-8 space-y-6">
            {{-- Balance card with ledger history --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Current Balance</h3>
                        <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-1">
                            {{ number_format($balance, 2) }} MVR
                        </p>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium
                            {{ $balance >= 0 ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                            {{ $balance >= 0 ? 'Positive' : 'Negative' }}
                        </span>
                    </div>
                </div>

                {{-- Ledger History --}}
                @if(count($ledgerWithBalance) > 0)
                    <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-4">
                        <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-3 uppercase tracking-wide">
                            Transaction History (Calculator Style)
                        </h4>
                        <div class="space-y-1.5 max-h-80 overflow-y-auto bg-gray-50 dark:bg-gray-900/50 rounded-lg p-2">
                            @foreach($ledgerWithBalance as $ledgerItem)
                                @php
                                    $entry = $ledgerItem['entry'];
                                    $balanceAfter = $ledgerItem['balance_after'];
                                    $isTopup = $entry->type === 'topup';
                                @endphp
                                <div class="flex items-start justify-between py-2.5 px-3 rounded-md bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-700 transition-colors">
                                    <div class="flex-1 min-w-0 pr-3">
                                        <div class="flex items-baseline gap-2 flex-wrap">
                                            <span class="text-lg font-bold {{ $isTopup ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                                {{ $isTopup ? '+' : '−' }}{{ number_format($entry->amount, 2) }}
                                            </span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $entry->paid_at?->format('M d, Y H:i') ?? $entry->created_at->format('M d, Y H:i') }}
                                            </span>
                                        </div>
                                        <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                            {{ $entry->purpose }}
                                            @if($entry->category)
                                                <span class="text-gray-400"> • {{ ucfirst($entry->category) }}</span>
                                            @endif
                                        </div>
                                        @if($entry->user)
                                            <div class="text-[10px] text-gray-400 mt-0.5">
                                                by {{ $entry->user->name }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="text-right flex-shrink-0 border-l border-gray-200 dark:border-gray-700 pl-3 ml-3">
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Balance</div>
                                        <div class="text-sm font-bold {{ $balanceAfter >= 0 ? 'text-gray-900 dark:text-gray-100' : 'text-red-600 dark:text-red-400' }}">
                                            {{ number_format($balanceAfter, 2) }} MVR
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-4">
                        <p class="text-xs text-gray-500 dark:text-gray-400 text-center py-4">
                            No transaction history yet. Add a top-up or expense to see the ledger.
                        </p>
                    </div>
                @endif
            </div>

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

            {{-- Filters --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4">
                <div class="flex flex-wrap items-center gap-2 mb-4">
                    @php
                        $statusTabs = [
                            'all'      => 'All',
                            'pending'  => 'Pending',
                            'approved' => 'Approved',
                            'rejected' => 'Rejected',
                        ];
                        $typeTabs = [
                            'all'     => 'All',
                            'topup'   => 'Top-up',
                            'expense' => 'Expense',
                        ];
                    @endphp

                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Status:</span>
                    @foreach($statusTabs as $key => $label)
                        <a href="{{ route('petty-cash.index', array_merge(['status' => $key], request('type') !== 'all' ? ['type' => request('type')] : [])) }}"
                           class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                  {{ $status === $key
                                      ? 'bg-indigo-600 text-white'
                                      : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600' }}">
                            {{ $label }}
                        </a>
                    @endforeach

                    <span class="ml-4 text-xs font-medium text-gray-700 dark:text-gray-300">Type:</span>
                    @foreach($typeTabs as $key => $label)
                        <a href="{{ route('petty-cash.index', array_merge(['type' => $key], request('status') !== 'all' ? ['status' => request('status')] : [])) }}"
                           class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                  {{ $type === $key
                                      ? 'bg-green-600 text-white'
                                      : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Top-up + Expense forms --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Add Top-up form --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">
                        Add Top-up
                    </h3>
                    <form method="POST" action="{{ route('petty-cash.store') }}" class="space-y-3">
                        @csrf
                        <input type="hidden" name="type" value="topup">

                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Amount (MVR)
                            </label>
                            <input type="number" step="0.01" name="amount" required
                                   class="block w-full rounded-md border-gray-300 text-sm
                                          focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Category (optional)
                            </label>
                            <input type="text" name="category" placeholder="e.g. Bank withdrawal"
                                   class="block w-full rounded-md border-gray-300 text-sm
                                          focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Purpose
                            </label>
                            <textarea name="purpose" rows="2" required
                                      class="block w-full rounded-md border-gray-300 text-sm
                                             focus:border-indigo-500 focus:ring-indigo-500"
                                      placeholder="Reason for top-up"></textarea>
                        </div>

                        <button type="submit"
                                class="w-full inline-flex justify-center items-center px-4 py-2 bg-green-600 border border-transparent rounded-md
                                       font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700
                                       focus:outline-none">
                            Add Top-up
                        </button>
                    </form>
                </div>

                {{-- Record Expense form --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">
                        Record Expense
                    </h3>
                    <form method="POST" action="{{ route('petty-cash.store') }}" class="space-y-3">
                        @csrf
                        <input type="hidden" name="type" value="expense">

                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Amount (MVR)
                            </label>
                            <input type="number" step="0.01" name="amount" required
                                   class="block w-full rounded-md border-gray-300 text-sm
                                          focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Category
                            </label>
                            <select name="category"
                                    class="block w-full rounded-md border-gray-300 text-sm
                                           focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select category</option>
                                <option value="fuel">Fuel</option>
                                <option value="parts">Parts</option>
                                <option value="food">Food</option>
                                <option value="misc">Misc</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Purpose
                            </label>
                            <textarea name="purpose" rows="2" required
                                      class="block w-full rounded-md border-gray-300 text-sm
                                             focus:border-indigo-500 focus:ring-indigo-500"
                                      placeholder="What was this expense for?"></textarea>
                        </div>

                        <button type="submit"
                                class="w-full inline-flex justify-center items-center px-4 py-2 bg-red-600 border border-transparent rounded-md
                                       font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700
                                       focus:outline-none">
                            Record Expense
                        </button>
                    </form>
                </div>
            </div>

            {{-- Table of entries --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400">Date</th>
                        <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400">Type</th>
                        <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400">User</th>
                        <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400">Category</th>
                        <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400">Purpose</th>
                        <th class="px-3 py-2 text-right font-medium text-gray-500 dark:text-gray-400">Amount</th>
                        <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-3 py-2 text-left font-medium text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($entries as $entry)
                        <tr>
                            <td class="px-3 py-2 text-xs text-gray-500 dark:text-gray-400">
                                {{ $entry->created_at?->format('Y-m-d H:i') }}
                            </td>
                            <td class="px-3 py-2">
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                                    {{ $entry->type === 'topup'
                                        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                        : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                    {{ ucfirst($entry->type) }}
                                </span>
                            </td>
                            <td class="px-3 py-2 text-xs text-gray-700 dark:text-gray-300">
                                {{ $entry->user?->name }}
                            </td>
                            <td class="px-3 py-2 text-xs text-gray-500 dark:text-gray-400">
                                {{ $entry->category ?? '—' }}
                            </td>
                            <td class="px-3 py-2 text-xs text-gray-700 dark:text-gray-300">
                                {{ $entry->purpose }}
                                @if($entry->source_payment_id)
                                    <span class="ml-1 text-[10px] text-indigo-600 dark:text-indigo-400">(from payment)</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-right font-medium
                                {{ $entry->type === 'topup'
                                    ? 'text-green-600 dark:text-green-400'
                                    : 'text-red-600 dark:text-red-400' }}">
                                {{ $entry->type === 'topup' ? '+' : '-' }}{{ number_format($entry->amount, 2) }} MVR
                            </td>
                            <td class="px-3 py-2">
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                                    {{ $entry->status === 'approved'
                                        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                        : ($entry->status === 'rejected'
                                            ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                                            : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200') }}">
                                    {{ ucfirst($entry->status) }}
                                </span>
                            </td>
                            <td class="px-3 py-2">
                                @if($entry->status === 'pending')
                                    <div class="flex gap-2">
                                        <form method="POST" action="{{ route('petty-cash.approve', $entry) }}"
                                              onsubmit="return confirm('Approve this entry?');">
                                            @csrf
                                            <button type="submit"
                                                    class="text-xs text-green-600 dark:text-green-400 hover:underline">
                                                Approve
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('petty-cash.reject', $entry) }}"
                                              onsubmit="return confirm('Reject this entry?');">
                                            @csrf
                                            <button type="submit"
                                                    class="text-xs text-red-600 dark:text-red-400 hover:underline">
                                                Reject
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-3 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                No petty cash entries found.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $entries->links() }}
            </div>
        </div>
    </div>
</x-app-layout>

