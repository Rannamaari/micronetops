<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Loans & Salary Advances
            </h2>
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

            {{-- Summary Cards --}}
            <div class="mb-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4 border-l-4 border-red-500">
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Total Outstanding</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{ number_format($totalOutstanding, 2) }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">MVR</div>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4 border-l-4 border-yellow-500">
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Monthly Deductions</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{ number_format($totalMonthlyDeduction, 2) }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">MVR</div>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4 border-l-4 border-blue-500">
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Active Loans</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{ $totalLoansCount }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Employees</div>
                </div>
            </div>

            {{-- Filters --}}
            <div class="mb-4 bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4">
                <form method="GET" action="{{ route('loans.index') }}" class="flex flex-wrap gap-4 items-end">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                        <select name="status" class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                            <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Status</option>
                            <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
                        <select name="loan_type" class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                            <option value="all" {{ $loanType === 'all' ? 'selected' : '' }}>All Types</option>
                            <option value="loan" {{ $loanType === 'loan' ? 'selected' : '' }}>Loan</option>
                            <option value="salary_advance" {{ $loanType === 'salary_advance' ? 'selected' : '' }}>Salary Advance</option>
                        </select>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded text-sm">
                        Apply Filters
                    </button>
                    @if($status !== 'all' || $loanType !== 'all')
                        <a href="{{ route('loans.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-bold rounded text-sm">
                            Clear Filters
                        </a>
                    @endif
                </form>
            </div>

            {{-- Loans List --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Employee</th>
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
                                        <div class="flex items-center">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    <a href="{{ route('employees.show', $loan->employee) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">
                                                        {{ $loan->employee->name }}
                                                    </a>
                                                </div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $loan->employee->employee_number }}</div>
                                            </div>
                                        </div>
                                    </td>
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
                                        @if($loan->status === 'active' && $loan->monthly_deduction > 0)
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                ~{{ ceil($loan->remaining_balance / $loan->monthly_deduction) }} months left
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            {{ $loan->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ ucfirst($loan->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('employees.loans.index', $loan->employee) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 mr-3">Manage</a>
                                        @if($loan->status === 'active')
                                            <form action="{{ route('employees.loans.mark-paid', [$loan->employee, $loan]) }}" method="POST" class="inline" onsubmit="return confirm('Mark this loan as fully paid?');">
                                                @csrf
                                                <button type="submit" class="text-green-600 hover:text-green-900 dark:text-green-400">Mark Paid</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                        @if($status !== 'all' || $loanType !== 'all')
                                            No loans found matching the selected filters.
                                        @else
                                            No loans or advances found.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Info Box --}}
            <div class="mt-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-md p-4">
                <div class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="text-sm text-blue-800 dark:text-blue-200">
                        <p class="font-medium mb-1">About Loans & Advances</p>
                        <ul class="list-disc list-inside space-y-1 text-xs">
                            <li><strong>Loans</strong> are typically larger amounts with longer repayment periods</li>
                            <li><strong>Salary Advances</strong> are smaller amounts deducted from upcoming salary</li>
                            <li>Monthly deductions are automatically applied during payroll processing</li>
                            <li>To add a new loan, go to the employee's page and manage their loans</li>
                            <li>Loans are marked as "Completed" automatically when fully paid</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
