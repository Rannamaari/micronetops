<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('payroll.index', ['year' => $year, 'month' => $month]) }}" class="inline-flex items-center justify-center w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-full hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Run Payroll</div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }}
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-4 lg:px-8">
            @if ($errors->any())
                <div class="mb-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md p-3">
                    <ul class="list-disc list-inside text-sm text-red-600 dark:text-red-400">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($existingPayroll)
                <div class="mb-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-md p-4">
                    <div class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <div class="text-sm text-yellow-800 dark:text-yellow-200">
                            <p class="font-medium">Payroll has already been run for {{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }}</p>
                            <p class="mt-1">Only employees without existing payroll records for this month will be processed.</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Month/Year Selector --}}
            <div class="mb-4 bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4">
                <form method="GET" action="{{ route('payroll.create') }}" class="flex flex-wrap gap-4 items-end">
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
                        Change Month
                    </button>
                </form>
            </div>

            @if($employees->isEmpty())
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-8 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No active employees</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">There are no active employees to process payroll for.</p>
                </div>
            @else
                <form method="POST" action="{{ route('payroll.store') }}" x-data="payrollForm()">
                    @csrf
                    <input type="hidden" name="year" value="{{ $year }}">
                    <input type="hidden" name="month" value="{{ $month }}">

                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                        {{-- Select All Header --}}
                        <div class="bg-gray-50 dark:bg-gray-900 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                            <label class="flex items-center">
                                <input type="checkbox" @change="toggleAll($event.target.checked)" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Select All Employees</span>
                            </label>
                        </div>

                        {{-- Employee List --}}
                        <div class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($employees as $employee)
                                @php
                                    $basicSalary = $employee->basic_salary;
                                    $allowances = $employee->allowances()->where('is_active', true)->where('frequency', 'monthly')->sum('amount');
                                    $loanDeduction = $employee->activeLoans()->sum('monthly_deduction');

                                    // Calculate automatic bonuses for this month
                                    $automaticBonuses = 0;
                                    $activeBonuses = $employee->bonuses()->where('status', 'active')->get();
                                    foreach ($activeBonuses as $empBonus) {
                                        if ($empBonus->isApplicableFor($year, $month)) {
                                            $automaticBonuses += $empBonus->amount;
                                        }
                                    }
                                @endphp
                                <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50" x-data="{ selected: false, bonus: 0, otherDeduction: 0, autoBonuses: {{ $automaticBonuses }} }">
                                    <div class="flex items-start gap-4">
                                        {{-- Checkbox --}}
                                        <div class="flex items-center h-10">
                                            <input
                                                type="checkbox"
                                                name="employee_ids[]"
                                                value="{{ $employee->id }}"
                                                x-model="selected"
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                            >
                                        </div>

                                        {{-- Employee Info --}}
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between mb-2">
                                                <div>
                                                    <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $employee->name }}</h4>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $employee->employee_number }} • {{ $employee->position }}</p>
                                                </div>
                                                <div class="text-right">
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">Net Salary</div>
                                                    <div class="text-lg font-bold text-gray-900 dark:text-gray-100" x-text="calculateNet({{ $basicSalary }}, {{ $allowances }}, {{ $automaticBonuses }}, {{ $loanDeduction }})"></div>
                                                </div>
                                            </div>

                                            {{-- Salary Breakdown --}}
                                            <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-3">
                                                <div class="bg-gray-50 dark:bg-gray-900 rounded p-2">
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">Basic Salary</div>
                                                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ number_format($basicSalary, 2) }}</div>
                                                </div>
                                                <div class="bg-green-50 dark:bg-green-900/20 rounded p-2">
                                                    <div class="text-xs text-green-600 dark:text-green-400">Allowances</div>
                                                    <div class="text-sm font-semibold text-green-700 dark:text-green-300">+{{ number_format($allowances, 2) }}</div>
                                                </div>
                                                <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded p-2">
                                                    <div class="text-xs text-yellow-600 dark:text-yellow-400">Auto Bonuses</div>
                                                    <div class="text-sm font-semibold text-yellow-700 dark:text-yellow-300">+{{ number_format($automaticBonuses, 2) }}</div>
                                                </div>
                                                <div class="bg-red-50 dark:bg-red-900/20 rounded p-2">
                                                    <div class="text-xs text-red-600 dark:text-red-400">Loans</div>
                                                    <div class="text-sm font-semibold text-red-700 dark:text-red-300">-{{ number_format($loanDeduction, 2) }}</div>
                                                </div>
                                                <div class="bg-blue-50 dark:bg-blue-900/20 rounded p-2">
                                                    <div class="text-xs text-blue-600 dark:text-blue-400">Gross</div>
                                                    <div class="text-sm font-semibold text-blue-700 dark:text-blue-300" x-text="({{ $basicSalary }} + {{ $allowances }} + autoBonuses + parseFloat(bonus || 0)).toFixed(2)"></div>
                                                </div>
                                            </div>

                                            {{-- Additional Fields --}}
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Bonus (MVR)</label>
                                                    <input
                                                        type="number"
                                                        step="0.01"
                                                        name="bonuses[{{ $employee->id }}]"
                                                        x-model="bonus"
                                                        :disabled="!selected"
                                                        placeholder="0.00"
                                                        class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm disabled:bg-gray-100 dark:disabled:bg-gray-800 disabled:cursor-not-allowed"
                                                    >
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Other Deductions (MVR)</label>
                                                    <input
                                                        type="number"
                                                        step="0.01"
                                                        name="other_deductions[{{ $employee->id }}]"
                                                        x-model="otherDeduction"
                                                        :disabled="!selected"
                                                        placeholder="0.00"
                                                        class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm disabled:bg-gray-100 dark:disabled:bg-gray-800 disabled:cursor-not-allowed"
                                                    >
                                                </div>
                                            </div>

                                            {{-- Active Loans Info --}}
                                            @if($employee->activeLoans()->count() > 0)
                                                <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                                    <span class="font-medium">Active Loans:</span>
                                                    @foreach($employee->activeLoans as $loan)
                                                        <span class="inline-block mr-2">
                                                            {{ ucfirst(str_replace('_', ' ', $loan->loan_type)) }}: {{ number_format($loan->remaining_balance, 2) }} remaining
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Submit Button --}}
                        <div class="bg-gray-50 dark:bg-gray-900 px-4 py-3 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                            <a href="{{ route('payroll.index', ['year' => $year, 'month' => $month]) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-600">
                                Cancel
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Process Payroll
                            </button>
                        </div>
                    </div>
                </form>

                <script>
                    function payrollForm() {
                        return {
                            toggleAll(checked) {
                                document.querySelectorAll('input[name="employee_ids[]"]').forEach(checkbox => {
                                    checkbox.checked = checked;
                                    checkbox.dispatchEvent(new Event('change'));
                                });
                            },
                            calculateNet(basic, allowances, autoBonuses, loans) {
                                const bonus = parseFloat(this.bonus || 0);
                                const otherDeduction = parseFloat(this.otherDeduction || 0);
                                const gross = basic + allowances + autoBonuses + bonus;
                                const totalDeductions = loans + otherDeduction;
                                const net = gross - totalDeductions;
                                return net.toFixed(2) + ' MVR';
                            }
                        }
                    }
                </script>
            @endif
        </div>
    </div>
</x-app-layout>
