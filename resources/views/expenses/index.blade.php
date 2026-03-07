<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Expenses') }}
            </h2>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('expenses.create-cogs') }}" class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                    Add COGS
                </a>
                <a href="{{ route('expenses.create-operating') }}" class="px-3 py-2 border border-blue-600 text-blue-600 rounded-lg hover:bg-blue-50 text-sm">
                    Add Operating
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-4 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6 text-gray-900 dark:text-gray-100">
                    {{-- Recurring expenses banner --}}
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
                    <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 mb-4">
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Period:</span>
                        @foreach (['all' => 'All', 'today' => 'Today', 'yesterday' => 'Yest.', 'week' => 'Week', 'month' => 'Month'] as $key => $label)
                            <a href="{{ route('expenses.index', array_merge(request()->except('period', 'page'), $key !== 'all' ? ['period' => $key] : [])) }}"
                                class="px-2.5 py-1.5 text-xs font-medium rounded-full border transition
                                    {{ $period === $key ? 'bg-blue-600 text-white border-blue-600' : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600' }}">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>

                    {{-- Filters --}}
                    <form method="GET" class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
                        @if ($period !== 'all')
                            <input type="hidden" name="period" value="{{ $period }}">
                        @endif
                        <div>
                            <label class="block text-xs font-medium mb-1">Business Unit</label>
                            <select name="business_unit" class="w-full rounded border-gray-300 text-sm h-10">
                                <option value="all">All</option>
                                @foreach ($businessUnits as $key => $label)
                                    <option value="{{ $key }}" @selected($businessUnit === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Type</label>
                            <select name="type" class="w-full rounded border-gray-300 text-sm h-10">
                                <option value="all">All</option>
                                @foreach ($types as $key => $label)
                                    <option value="{{ $key }}" @selected($type === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Search</label>
                            <input name="search" value="{{ $search }}" class="w-full rounded border-gray-300 text-sm h-10" placeholder="Vendor, ref...">
                        </div>
                        <div class="flex items-end">
                            <button class="w-full h-10 bg-gray-900 text-white rounded-lg text-sm">Filter</button>
                        </div>
                    </form>

                    {{-- Desktop Table (hidden on mobile) --}}
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Category</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Unit</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Vendor</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase hidden md:table-cell">Ref</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase hidden md:table-cell">Account</th>
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
                                        <td class="px-4 py-3 text-sm text-gray-500 hidden md:table-cell">{{ $expense->reference ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500 hidden md:table-cell">{{ $expense->account?->name ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-right font-medium tabular-nums">{{ number_format($expense->amount, 2) }}</td>
                                        <td class="px-4 py-3 text-right whitespace-nowrap">
                                            <a href="{{ route('expenses.show', $expense) }}" class="text-blue-600 hover:underline text-sm" onclick="event.stopPropagation()">View</a>
                                            <a href="{{ route('expenses.edit', $expense) }}" class="text-gray-500 hover:underline text-sm ml-2" onclick="event.stopPropagation()">Edit</a>
                                            @if(Auth::user()->isAdmin())
                                                <form method="POST" action="{{ route('expenses.destroy', $expense) }}" class="inline ml-2" onclick="event.stopPropagation()"
                                                      onsubmit="return confirm('Delete this expense? This will reverse the account debit and any inventory stock changes.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:underline text-sm">Delete</button>
                                                </form>
                                            @endif
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

                    {{-- Mobile Card View --}}
                    <div class="sm:hidden space-y-3">
                        @forelse ($expenses as $expense)
                            <a href="{{ route('expenses.show', $expense) }}" class="block bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4 active:bg-gray-100 dark:active:bg-gray-700/50 transition">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $expense->category?->name ?? 'Uncategorized' }}</span>
                                            <span class="inline-block px-1.5 py-0.5 text-[10px] font-medium rounded
                                                {{ $expense->category?->type === 'cogs' ? 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400' : ($expense->category?->type === 'operating' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-600 dark:text-gray-300') }}">
                                                {{ strtoupper($expense->category?->type) }}
                                            </span>
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            {{ $expense->vendorEntity?->name ?? $expense->vendor ?? 'No vendor' }}
                                            <span class="mx-1">&middot;</span>
                                            {{ $businessUnits[$expense->business_unit] ?? $expense->business_unit }}
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100 tabular-nums">{{ number_format($expense->amount, 2) }}</div>
                                        <div class="text-xs text-gray-400 mt-0.5">{{ $expense->incurred_at->format('d M') }}</div>
                                    </div>
                                </div>
                                @if ($expense->reference)
                                    <div class="text-xs text-gray-400 mt-1.5">Ref: {{ $expense->reference }}</div>
                                @endif
                                @if(Auth::user()->isAdmin())
                                    <form method="POST" action="{{ route('expenses.destroy', $expense) }}" class="mt-2"
                                          onsubmit="return confirm('Delete this expense? This will reverse the account debit and any inventory stock changes.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-red-600 hover:underline">Delete</button>
                                    </form>
                                @endif
                            </a>
                        @empty
                            <div class="py-8 text-center text-gray-500 text-sm">No expenses found.</div>
                        @endforelse
                    </div>

                    <div class="mt-4">
                        {{ $expenses->links() }}
                    </div>

                    <div class="mt-5 text-sm text-gray-500">
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
