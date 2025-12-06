<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Payroll Management
            </h2>
            <a href="{{ route('payroll.create', ['year' => $year, 'month' => $month]) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Run Payroll
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

            {{-- Last Processed Payroll Badge --}}
            @if($lastProcessedPayroll)
            <div class="mb-4 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border-l-4 border-green-500 rounded-lg p-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-green-500 rounded-lg">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Last Processed Payroll</div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">
                            {{ date('F Y', mktime(0, 0, 0, $lastProcessedPayroll->month, 1, $lastProcessedPayroll->year)) }}
                            • Processed on {{ $lastProcessedPayroll->created_at->format('M d, Y \a\t h:i A') }}
                        </div>
                    </div>
                    @if($year != $lastProcessedPayroll->year || $month != $lastProcessedPayroll->month)
                    <a href="{{ route('payroll.index', ['year' => $lastProcessedPayroll->year, 'month' => $lastProcessedPayroll->month]) }}"
                       class="text-xs px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white rounded-md font-medium">
                        View
                    </a>
                    @else
                    <span class="text-xs px-3 py-1.5 bg-green-600 text-white rounded-md font-medium">
                        Currently Viewing
                    </span>
                    @endif
                </div>
            </div>
            @endif

            {{-- Month/Year Selector --}}
            <div class="mb-4 bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4">
                <form method="GET" action="{{ route('payroll.index') }}" class="flex flex-wrap gap-4 items-end">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Select Month</label>
                        <select name="month" class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="flex-1 min-w-[150px]">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Year</label>
                        <select name="year" class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                            @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded text-sm">
                        View
                    </button>
                </form>
            </div>

            {{-- Summary Cards --}}
            <div class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4 border-l-4 border-blue-500">
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Total Employees</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $payrolls->count() }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4 border-l-4 border-green-500">
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Total Gross</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{ number_format($payrolls->sum('gross_salary'), 2) }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">MVR</div>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4 border-l-4 border-red-500">
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Total Deductions</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{ number_format($payrolls->sum('total_deductions'), 2) }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">MVR</div>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4 border-l-4 border-purple-500">
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Total Net Pay</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{ number_format($payrolls->sum('net_salary'), 2) }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">MVR</div>
                </div>
            </div>

            {{-- Payroll List --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Employee</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Basic Salary</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Allowances</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Deductions</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Net Salary</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($payrolls as $payroll)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $payroll->employee->name }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $payroll->employee->employee_number }}</div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-gray-100">{{ number_format($payroll->basic_salary, 2) }}</div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="text-sm text-green-600 dark:text-green-400">+{{ number_format($payroll->allowances + $payroll->bonuses, 2) }}</div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="text-sm text-red-600 dark:text-red-400">-{{ number_format($payroll->total_deductions, 2) }}</div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-gray-900 dark:text-gray-100">{{ number_format($payroll->net_salary, 2) }}</div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('payroll.show', $payroll) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 mr-3">View</a>
                                        <form action="{{ route('payroll.destroy', $payroll) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this payroll record?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                        No payroll records for {{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }}.
                                        <a href="{{ route('payroll.create', ['year' => $year, 'month' => $month]) }}" class="text-indigo-600 hover:underline">Run payroll now</a>
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
