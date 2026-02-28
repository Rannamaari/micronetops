<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Expenses') }}
            </h2>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('expenses.create-cogs') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Add COGS
                </a>
                <a href="{{ route('expenses.create-operating') }}" class="px-4 py-2 border border-blue-600 text-blue-600 rounded-lg hover:bg-blue-50">
                    Add Operating
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                        <div class="text-sm text-gray-600 dark:text-gray-300">
                            Recurring expenses can be auto-generated for due dates.
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('recurring-expenses.index') }}" class="px-3 py-2 text-sm rounded-lg border border-gray-300 hover:bg-gray-50">
                                Manage Recurring
                            </a>
                            <form method="POST" action="{{ route('recurring-expenses.generate') }}">
                                @csrf
                                <button class="px-3 py-2 text-sm rounded-lg bg-gray-900 text-white hover:bg-gray-800">
                                    Generate Due
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Date preset chips --}}
                    <div class="flex flex-wrap items-center gap-2 mb-4">
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Period:</span>
                        @foreach (['all' => 'All Time', 'today' => 'Today', 'yesterday' => 'Yesterday', 'week' => 'This Week', 'month' => 'This Month'] as $key => $label)
                            <a href="{{ route('expenses.index', array_merge(request()->except('period', 'page'), $key !== 'all' ? ['period' => $key] : [])) }}"
                                class="px-3 py-1.5 text-xs font-medium rounded-full border transition
                                    {{ $period === $key ? 'bg-blue-600 text-white border-blue-600' : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600' }}">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>

                    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        {{-- Preserve period selection --}}
                        @if ($period !== 'all')
                            <input type="hidden" name="period" value="{{ $period }}">
                        @endif
                        <div>
                            <label class="block text-sm font-medium">Business Unit</label>
                            <select name="business_unit" class="mt-1 w-full rounded border-gray-300">
                                <option value="all">All</option>
                                @foreach ($businessUnits as $key => $label)
                                    <option value="{{ $key }}" @selected($businessUnit === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Type</label>
                            <select name="type" class="mt-1 w-full rounded border-gray-300">
                                <option value="all">All</option>
                                @foreach ($types as $key => $label)
                                    <option value="{{ $key }}" @selected($type === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Search</label>
                            <input name="search" value="{{ $search }}" class="mt-1 w-full rounded border-gray-300" placeholder="Vendor, ref, notes">
                        </div>
                        <div class="flex items-end">
                            <button class="w-full px-4 py-2 bg-gray-900 text-white rounded-lg">Filter</button>
                        </div>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Category</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Unit</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Vendor</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase hidden sm:table-cell">Ref</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase hidden sm:table-cell">Account</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Amount</th>
                                    <th class="px-4 py-2"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($expenses as $expense)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer" onclick="window.location='{{ route('expenses.show', $expense) }}'">
                                        <td class="px-4 py-3 text-sm whitespace-nowrap">{{ $expense->incurred_at->format('d M Y') }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            {{ $expense->category?->name }}
                                            <span class="inline-block ml-1 px-1.5 py-0.5 text-[10px] font-medium rounded
                                                {{ $expense->category?->type === 'cogs' ? 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400' : ($expense->category?->type === 'operating' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-600 dark:text-gray-300') }}">
                                                {{ strtoupper($expense->category?->type) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm">{{ $businessUnits[$expense->business_unit] ?? $expense->business_unit }}</td>
                                        <td class="px-4 py-3 text-sm">{{ $expense->vendorEntity?->name ?? $expense->vendor ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500 hidden sm:table-cell">{{ $expense->reference ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500 hidden sm:table-cell">{{ $expense->account?->name ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-right font-medium tabular-nums">{{ number_format($expense->amount, 2) }}</td>
                                        <td class="px-4 py-3 text-right whitespace-nowrap">
                                            <a href="{{ route('expenses.show', $expense) }}" class="text-blue-600 hover:underline text-sm" onclick="event.stopPropagation()">View</a>
                                            <a href="{{ route('expenses.edit', $expense) }}" class="text-gray-500 hover:underline text-sm ml-2" onclick="event.stopPropagation()">Edit</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-4 py-6 text-center text-gray-500">No expenses found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $expenses->links() }}
                    </div>

                    <div class="mt-6 text-sm text-gray-500">
                        Manage
                        <a href="{{ route('expense-categories.index') }}" class="text-blue-600 hover:underline">Expense Categories</a>,
                        <a href="{{ route('vendors.index') }}" class="text-blue-600 hover:underline">Vendors</a>,
                        <a href="{{ route('accounts.index') }}" class="text-blue-600 hover:underline">Accounts</a>,
                        and
                        <a href="{{ route('recurring-expenses.index') }}" class="text-blue-600 hover:underline">Recurring Expenses</a>.
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
