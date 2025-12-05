<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="{{ route('employees.show', $employee) }}" class="inline-flex items-center justify-center w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-full hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Loans & Advances</div>
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                        {{ $employee->name }}
                    </h2>
                </div>
            </div>
            <a href="{{ route('employees.loans.create', $employee) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add Loan/Advance
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-4 lg:px-8">
            {{-- Success Message --}}
            @if (session('success'))
                <div class="mb-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-md p-3">
                    <p class="text-sm text-green-600 dark:text-green-400">{{ session('success') }}</p>
                </div>
            @endif

            {{-- Loan Summary --}}
            <div class="mb-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4 border-l-4 border-red-500">
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Total Outstanding</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{ number_format($loans->where('status', 'active')->sum('remaining_balance'), 2) }} MVR
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4 border-l-4 border-yellow-500">
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Monthly Deduction</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{ number_format($loans->where('status', 'active')->sum('monthly_deduction'), 2) }} MVR
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4 border-l-4 border-green-500">
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Active Loans</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{ $loans->where('status', 'active')->count() }}
                    </div>
                </div>
            </div>

            {{-- Loan List --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Amount</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Remaining</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Monthly Deduction</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($loans as $loan)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            {{ $loan->loan_type === 'loan' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                            {{ $loan->loan_type === 'loan' ? 'Loan' : 'Salary Advance' }}
                                        </span>
                                        @if($loan->reason)
                                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $loan->reason }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ number_format($loan->amount, 2) }} MVR</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $loan->loan_date->format('M d, Y') }}</div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="text-sm font-bold text-red-600 dark:text-red-400">{{ number_format($loan->remaining_balance, 2) }} MVR</div>
                                        <div class="text-xs text-green-600 dark:text-green-400 font-medium">
                                            {{ number_format(($loan->amount - $loan->remaining_balance), 2) }} MVR paid
                                        </div>
                                        @php
                                            $paidPercentage = $loan->amount > 0 ? (($loan->amount - $loan->remaining_balance) / $loan->amount) * 100 : 0;
                                        @endphp
                                        <div class="mt-2">
                                            <div class="flex items-center gap-2">
                                                <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                                    <div class="bg-green-500 h-2 rounded-full transition-all" style="width: {{ $paidPercentage }}%"></div>
                                                </div>
                                                <span class="text-xs text-gray-600 dark:text-gray-400 font-medium">{{ number_format($paidPercentage, 0) }}%</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-gray-100">{{ number_format($loan->monthly_deduction, 2) }} MVR</div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            {{ $loan->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ ucfirst($loan->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                                        @if($loan->status === 'active')
                                            <form action="{{ route('employees.loans.mark-paid', [$employee, $loan]) }}" method="POST" class="inline mr-3" onsubmit="return confirm('Mark this loan as fully paid?');">
                                                @csrf
                                                <button type="submit" class="text-green-600 hover:text-green-900 dark:text-green-400">Mark Paid</button>
                                            </form>
                                        @endif
                                        <a href="{{ route('employees.loans.edit', [$employee, $loan]) }}" class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 mr-3">Edit</a>
                                        <form action="{{ route('employees.loans.destroy', [$employee, $loan]) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this loan?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                        No loans or advances found. <a href="{{ route('employees.loans.create', $employee) }}" class="text-indigo-600 hover:underline">Add the first loan</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
