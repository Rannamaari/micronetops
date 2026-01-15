<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Petty Cash - Admin Dashboard
            </h2>
            <a href="{{ route('petty-cash.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Petty Cash
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-4 lg:px-8 space-y-6">
            {{-- Success/Error Messages --}}
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

            {{-- Total Allocated Card --}}
            <div class="bg-gradient-to-br from-indigo-500 to-purple-600 shadow-sm sm:rounded-lg p-6 text-white">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-sm font-medium opacity-90">Total Allocated to Staff</h3>
                        <p class="text-4xl font-bold mt-2">
                            {{ number_format($totalAllocated, 2) }} MVR
                        </p>
                        <p class="text-xs opacity-75 mt-1">Across {{ $userBalances->count() }} staff members</p>
                    </div>
                    <div class="p-4 bg-white/10 rounded-full">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- User Balances Table/Cards --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                <div class="p-4 sm:p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        Staff Petty Cash Balances
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        View and manage individual staff petty cash allocations
                    </p>
                </div>

                {{-- Desktop Table View --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Staff Member
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Current Balance
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Total Topups
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Total Expenses
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Pending
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($userBalances as $userBalance)
                                @php
                                    $user = $userBalance['user'];
                                    $balance = $userBalance['balance'];
                                    $totalTopups = $userBalance['total_topups'];
                                    $totalExpenses = $userBalance['total_expenses'];
                                    $pendingExpenses = $userBalance['pending_expenses'];
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-semibold text-sm">
                                                {{ strtoupper(substr($user->name, 0, 2)) }}
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ $user->name }}
                                                </div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ ucfirst($user->role) }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <span class="text-base font-bold {{ $balance >= 0 ? 'text-gray-900 dark:text-gray-100' : 'text-red-600 dark:text-red-400' }}">
                                            {{ number_format($balance, 2) }} MVR
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600 dark:text-gray-400">
                                        {{ number_format($totalTopups, 2) }} MVR
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600 dark:text-gray-400">
                                        {{ number_format($totalExpenses, 2) }} MVR
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                        @if($pendingExpenses > 0)
                                            <span class="text-amber-600 dark:text-amber-400 font-medium">
                                                {{ number_format($pendingExpenses, 2) }} MVR
                                            </span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('petty-cash.show-top-up-form', $user) }}"
                                               class="inline-flex items-center gap-1 px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white rounded-lg transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                </svg>
                                                Top Up
                                            </a>
                                            <a href="{{ route('petty-cash.user-history', $user) }}"
                                               class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                                History
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No staff members found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Mobile Card View --}}
                <div class="block md:hidden p-4 space-y-3">
                    @forelse($userBalances as $userBalance)
                        @php
                            $user = $userBalance['user'];
                            $balance = $userBalance['balance'];
                            $totalTopups = $userBalance['total_topups'];
                            $totalExpenses = $userBalance['total_expenses'];
                            $pendingExpenses = $userBalance['pending_expenses'];
                        @endphp
                        <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4 border-l-4 {{ $balance >= 0 ? 'border-green-500' : 'border-red-500' }}">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-semibold">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $user->name }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ ucfirst($user->role) }}</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Balance</div>
                                    <div class="text-lg font-bold {{ $balance >= 0 ? 'text-gray-900 dark:text-gray-100' : 'text-red-600 dark:text-red-400' }}">
                                        {{ number_format($balance, 2) }}
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-2 mb-3 text-xs">
                                <div class="bg-white dark:bg-gray-800 rounded p-2 text-center">
                                    <div class="text-gray-500 dark:text-gray-400 mb-1">Topups</div>
                                    <div class="font-semibold text-gray-900 dark:text-gray-100">{{ number_format($totalTopups, 2) }}</div>
                                </div>
                                <div class="bg-white dark:bg-gray-800 rounded p-2 text-center">
                                    <div class="text-gray-500 dark:text-gray-400 mb-1">Expenses</div>
                                    <div class="font-semibold text-gray-900 dark:text-gray-100">{{ number_format($totalExpenses, 2) }}</div>
                                </div>
                                <div class="bg-white dark:bg-gray-800 rounded p-2 text-center">
                                    <div class="text-gray-500 dark:text-gray-400 mb-1">Pending</div>
                                    <div class="font-semibold {{ $pendingExpenses > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-400' }}">
                                        {{ $pendingExpenses > 0 ? number_format($pendingExpenses, 2) : '-' }}
                                    </div>
                                </div>
                            </div>

                            <div class="flex gap-2">
                                <a href="{{ route('petty-cash.show-top-up-form', $user) }}"
                                   class="flex-1 inline-flex items-center justify-center gap-1 px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Top Up
                                </a>
                                <a href="{{ route('petty-cash.user-history', $user) }}"
                                   class="flex-1 inline-flex items-center justify-center gap-1 px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    History
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-sm text-gray-500 dark:text-gray-400">
                            No staff members found.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
