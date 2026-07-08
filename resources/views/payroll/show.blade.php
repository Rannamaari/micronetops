<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="{{ route('payroll.index', ['year' => $payroll->year, 'month' => $payroll->month]) }}" class="inline-flex items-center justify-center w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-full hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Salary Slip</div>
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                        {{ $payroll->employee->name }}
                    </h2>
                </div>
            </div>
            <button onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 print:hidden">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Print Salary Slip
            </button>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-4 lg:px-8">
            <div class="payslip-sheet bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                {{-- Screen Header Section --}}
                <div class="payslip-header print:hidden bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 py-8 text-white">
                    <div class="text-center mb-4">
                        <h1 class="text-2xl font-bold">Salary Slip</h1>
                        <p class="text-indigo-100 mt-1">{{ date('F Y', mktime(0, 0, 0, $payroll->month, 1, $payroll->year)) }}</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                        <div>
                            <div class="text-indigo-100 text-xs uppercase tracking-wide">Employee Name</div>
                            <div class="text-lg font-semibold">{{ $payroll->employee->name }}</div>
                        </div>
                        <div>
                            <div class="text-indigo-100 text-xs uppercase tracking-wide">Employee Number</div>
                            <div class="text-lg font-semibold">{{ $payroll->employee->employee_number }}</div>
                        </div>
                        <div>
                            <div class="text-indigo-100 text-xs uppercase tracking-wide">Position</div>
                            <div class="text-lg font-semibold">{{ $payroll->employee->position }}</div>
                        </div>
                        <div>
                            <div class="text-indigo-100 text-xs uppercase tracking-wide">Department</div>
                            <div class="text-lg font-semibold">{{ $payroll->employee->department }}</div>
                        </div>
                    </div>
                </div>

                @php
                    $employmentPeriod = $payroll->employee->getEmploymentPeriodForMonth($payroll->year, $payroll->month);
                    $daysInMonth = $employmentPeriod['days_in_month'] ?? \Carbon\Carbon::create($payroll->year, $payroll->month, 1)->daysInMonth;
                    $payableCalendarDays = $employmentPeriod['payable_calendar_days'] ?? 0;
                    $calendarDailyBasicRate = $daysInMonth > 0 ? ((float) $payroll->employee->basic_salary / $daysInMonth) : 0;

                    // Get bonuses applied for this payroll period
                    $appliedBonuses = $payroll->employee->bonuses()
                        ->where('status', 'active')
                        ->get()
                        ->filter(function($bonus) use ($payroll) {
                            return $bonus->isApplicableFor($payroll->year, $payroll->month);
                        });

                    // Get fixed monthly allowances applicable for this payroll period,
                    // and calculate the prorated amount shown on the payslip.
                    $appliedAllowances = $payroll->employee->allowances()
                        ->where('is_active', true)
                        ->where('frequency', 'monthly')
                        ->get()
                        ->filter(function ($allowance) use ($employmentPeriod) {
                            if (!$employmentPeriod) {
                                return false;
                            }

                            $allowanceStart = $allowance->start_date?->copy()->startOfDay() ?? $employmentPeriod['month_start']->copy();
                            $allowanceEnd = $allowance->end_date?->copy()->startOfDay() ?? $employmentPeriod['month_end']->copy();

                            return !($allowanceStart->gt($employmentPeriod['month_end']) || $allowanceEnd->lt($employmentPeriod['month_start']));
                        })
                        ->map(function ($allowance) use ($employmentPeriod, $daysInMonth) {
                            $allowanceStart = $allowance->start_date?->copy()->startOfDay() ?? $employmentPeriod['month_start']->copy();
                            $allowanceEnd = $allowance->end_date?->copy()->startOfDay() ?? $employmentPeriod['month_end']->copy();

                            $effectiveStart = $allowanceStart->gt($employmentPeriod['pay_period_start'])
                                ? $allowanceStart->copy()
                                : $employmentPeriod['pay_period_start']->copy();

                            $effectiveEnd = $allowanceEnd->lt($employmentPeriod['pay_period_end'])
                                ? $allowanceEnd->copy()
                                : $employmentPeriod['pay_period_end']->copy();

                            $allowanceDays = $effectiveEnd->lt($effectiveStart)
                                ? 0
                                : $effectiveStart->diffInDays($effectiveEnd) + 1;

                            $proratedAmount = $daysInMonth > 0
                                ? round(((float) $allowance->amount / $daysInMonth) * $allowanceDays, 2)
                                : 0;

                            return [
                                'model' => $allowance,
                                'monthly_amount' => (float) $allowance->amount,
                                'prorated_amount' => $proratedAmount,
                                'payable_days' => $allowanceDays,
                            ];
                        });
                @endphp

                {{-- Compact Print Slip --}}
                <div class="hidden print:block p-4 text-black">
                    <div class="border border-gray-300 rounded-lg overflow-hidden">
                        <div class="bg-gray-100 border-b border-gray-300 px-4 py-3 text-center">
                            <div class="text-lg font-bold">Salary Slip</div>
                            <div class="text-sm">{{ date('F Y', mktime(0, 0, 0, $payroll->month, 1, $payroll->year)) }}</div>
                        </div>

                        <div class="px-4 py-3">
                            <div class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                                <div><span class="font-semibold">Employee:</span> {{ $payroll->employee->name }}</div>
                                <div><span class="font-semibold">Employee No:</span> {{ $payroll->employee->employee_number }}</div>
                                <div><span class="font-semibold">Position:</span> {{ $payroll->employee->position }}</div>
                                <div><span class="font-semibold">Department:</span> {{ $payroll->employee->department ?: '—' }}</div>
                                <div><span class="font-semibold">Payable Days:</span> {{ $payroll->working_days ?? 'N/A' }} / {{ $daysInMonth }}</div>
                                <div><span class="font-semibold">Pay Date:</span> {{ $payroll->created_at->format('M d, Y') }}</div>
                            </div>
                        </div>

                        <div class="px-4 pb-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <div class="text-sm font-bold border-b border-gray-300 pb-1 mb-2">Earnings</div>
                                    <div class="space-y-1 text-sm">
                                        <div class="flex justify-between">
                                            <span>Basic Salary (Monthly)</span>
                                            <span>{{ number_format($payroll->employee->basic_salary, 2) }}</span>
                                        </div>
                                        <div class="flex justify-between text-xs text-gray-600">
                                            <span>Proration</span>
                                            <span>{{ number_format($payroll->employee->basic_salary, 2) }} ÷ {{ $daysInMonth }} × {{ $payableCalendarDays }}</span>
                                        </div>
                                        <div class="flex justify-between font-semibold">
                                            <span>Payable Basic</span>
                                            <span>{{ number_format($payroll->basic_salary, 2) }}</span>
                                        </div>

                                        @foreach($appliedAllowances as $allowanceData)
                                            <div class="flex justify-between">
                                                <span>{{ ucfirst(str_replace('_', ' ', $allowanceData['model']->allowance_type)) }} (Monthly)</span>
                                                <span>{{ number_format($allowanceData['monthly_amount'], 2) }}</span>
                                            </div>
                                            <div class="flex justify-between text-xs text-gray-600">
                                                <span>Proration</span>
                                                <span>{{ number_format($allowanceData['monthly_amount'], 2) }} ÷ {{ $daysInMonth }} × {{ $allowanceData['payable_days'] }}</span>
                                            </div>
                                            <div class="flex justify-between font-semibold">
                                                <span>Payable {{ ucfirst(str_replace('_', ' ', $allowanceData['model']->allowance_type)) }}</span>
                                                <span>{{ number_format($allowanceData['prorated_amount'], 2) }}</span>
                                            </div>
                                        @endforeach

                                        @if($payroll->bonuses > 0)
                                            <div class="flex justify-between">
                                                <span>Bonuses</span>
                                                <span>{{ number_format($payroll->bonuses, 2) }}</span>
                                            </div>
                                        @endif
                                        @if($payroll->overtime > 0)
                                            <div class="flex justify-between">
                                                <span>Overtime</span>
                                                <span>{{ number_format($payroll->overtime, 2) }}</span>
                                            </div>
                                        @endif

                                        <div class="flex justify-between font-bold border-t border-gray-300 pt-1 mt-2">
                                            <span>Gross Salary</span>
                                            <span>{{ number_format($payroll->gross_salary, 2) }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <div class="text-sm font-bold border-b border-gray-300 pb-1 mb-2">Deductions</div>
                                    <div class="space-y-1 text-sm">
                                        @if($payroll->loan_deduction > 0)
                                            <div class="flex justify-between">
                                                <span>Loan Deduction</span>
                                                <span>{{ number_format($payroll->loan_deduction, 2) }}</span>
                                            </div>
                                        @endif
                                        @if($payroll->absent_deduction > 0)
                                            <div class="flex justify-between">
                                                <span>Unpaid Leave / Absent</span>
                                                <span>{{ number_format($payroll->absent_deduction, 2) }}</span>
                                            </div>
                                        @endif
                                        @if($payroll->other_deductions > 0)
                                            <div class="flex justify-between">
                                                <span>Other Deductions</span>
                                                <span>{{ number_format($payroll->other_deductions, 2) }}</span>
                                            </div>
                                        @endif
                                        @if($payroll->total_deductions == 0)
                                            <div class="text-gray-500">No deductions</div>
                                        @endif
                                        <div class="flex justify-between font-bold border-t border-gray-300 pt-1 mt-2">
                                            <span>Total Deductions</span>
                                            <span>{{ number_format($payroll->total_deductions, 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 border-t-2 border-gray-800 pt-3 flex justify-between items-end">
                                <div>
                                    <div class="text-xs uppercase tracking-wide text-gray-600">Net Salary</div>
                                    <div class="text-xs text-gray-500">Amount to be paid</div>
                                </div>
                                <div class="text-2xl font-bold">{{ number_format($payroll->net_salary, 2) }} MVR</div>
                            </div>

                            <div class="mt-4 text-[11px] text-gray-600 text-center">
                                This is a computer-generated salary slip and does not require a signature.
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Salary Details --}}
                <div class="payslip-body print:hidden p-6">
                    {{-- Proration Breakdown --}}
                    <div class="mb-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
                        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Basic Salary</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $payableCalendarDays }} payable day{{ $payableCalendarDays === 1 ? '' : 's' }} out of {{ $daysInMonth }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Monthly</div>
                                    <div class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ number_format($payroll->employee->basic_salary, 2) }} MVR</div>
                                </div>
                            </div>
                            <div class="mt-3 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-3 py-2 text-sm text-gray-700 dark:text-gray-300">
                                {{ number_format($payroll->employee->basic_salary, 2) }} ÷ {{ $daysInMonth }} × {{ $payableCalendarDays }}
                            </div>
                            <div class="mt-3 flex items-center justify-between border-t border-gray-200 dark:border-gray-700 pt-3">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Payable Basic</span>
                                <span class="text-lg font-bold text-indigo-600 dark:text-indigo-400">{{ number_format($payroll->basic_salary, 2) }} MVR</span>
                            </div>
                        </div>

                        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Allowances</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Fixed monthly allowances applied to this payslip</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Monthly Total</div>
                                    <div class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ number_format($appliedAllowances->sum('monthly_amount'), 2) }} MVR</div>
                                </div>
                            </div>

                            <div class="mt-3 space-y-2">
                                @forelse($appliedAllowances as $allowanceData)
                                    <div class="rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-3 py-2 text-sm">
                                        <div class="flex items-center justify-between">
                                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ ucfirst(str_replace('_', ' ', $allowanceData['model']->allowance_type)) }}</span>
                                            <span class="text-gray-700 dark:text-gray-300">{{ number_format($allowanceData['monthly_amount'], 2) }} MVR</span>
                                        </div>
                                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                            {{ number_format($allowanceData['monthly_amount'], 2) }} ÷ {{ $daysInMonth }} × {{ $allowanceData['payable_days'] }}
                                        </div>
                                        <div class="mt-1 text-sm font-semibold text-blue-600 dark:text-blue-400">
                                            Payable: {{ number_format($allowanceData['prorated_amount'], 2) }} MVR
                                        </div>
                                    </div>
                                @empty
                                    <div class="rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-3 py-2 text-sm text-gray-500 dark:text-gray-400">
                                        No fixed monthly allowances applied.
                                    </div>
                                @endforelse
                            </div>

                            <div class="mt-3 flex items-center justify-between border-t border-gray-200 dark:border-gray-700 pt-3">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Payable Allowances</span>
                                <span class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ number_format($payroll->allowances, 2) }} MVR</span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Earnings --}}
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 pb-2 border-b-2 border-green-500">Earnings</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Basic Salary</span>
                                    <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ number_format($payroll->basic_salary, 2) }}</span>
                                </div>
                                @if($payroll->allowances > 0)
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Allowances</span>
                                        <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ number_format($payroll->allowances, 2) }}</span>
                                    </div>
                                @endif
                                @if($payroll->bonuses > 0)
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Bonuses</span>
                                        <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ number_format($payroll->bonuses, 2) }}</span>
                                    </div>
                                @endif
                                @if($payroll->overtime > 0)
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Overtime</span>
                                        <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ number_format($payroll->overtime, 2) }}</span>
                                    </div>
                                @endif
                                <div class="pt-3 mt-3 border-t border-gray-200 dark:border-gray-700">
                                    <div class="flex justify-between items-center">
                                        <span class="text-base font-semibold text-green-600 dark:text-green-400">Total Earnings</span>
                                        <span class="text-base font-bold text-green-600 dark:text-green-400">{{ number_format($payroll->gross_salary, 2) }} MVR</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Deductions --}}
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 pb-2 border-b-2 border-red-500">Deductions</h3>
                            <div class="space-y-3">
                                @if($payroll->loan_deduction > 0)
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Loan Deduction</span>
                                        <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ number_format($payroll->loan_deduction, 2) }}</span>
                                    </div>
                                @endif
                                @if($payroll->absent_deduction > 0)
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Absent Deduction ({{ $payroll->absent_days }} days)</span>
                                        <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ number_format($payroll->absent_deduction, 2) }}</span>
                                    </div>
                                @endif
                                @if(($payroll->prorated_deduction ?? 0) > 0)
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Prorated Deduction (Mid-month hire)</span>
                                        <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ number_format($payroll->prorated_deduction, 2) }}</span>
                                    </div>
                                @endif
                                @if($payroll->other_deductions > 0)
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Other Deductions</span>
                                        <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ number_format($payroll->other_deductions, 2) }}</span>
                                    </div>
                                @endif
                                @if($payroll->total_deductions == 0)
                                    <div class="text-sm text-gray-500 dark:text-gray-400 italic">No deductions</div>
                                @endif
                                <div class="pt-3 mt-3 border-t border-gray-200 dark:border-gray-700">
                                    <div class="flex justify-between items-center">
                                        <span class="text-base font-semibold text-red-600 dark:text-red-400">Total Deductions</span>
                                        <span class="text-base font-bold text-red-600 dark:text-red-400">{{ number_format($payroll->total_deductions, 2) }} MVR</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Net Salary --}}
                    <div class="mt-8 payslip-highlight bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-lg p-6 border-2 border-indigo-200 dark:border-indigo-800">
                        <div class="flex justify-between items-center">
                            <div>
                                <div class="text-sm text-gray-600 dark:text-gray-400 uppercase tracking-wide">Net Salary</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Amount to be paid</div>
                            </div>
                            <div class="text-right">
                                <div class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ number_format($payroll->net_salary, 2) }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">MVR</div>
                            </div>
                        </div>
                    </div>

                    {{-- Payment Details --}}
                    <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                            <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Pay Period</div>
                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100 mt-1">
                                {{ date('F Y', mktime(0, 0, 0, $payroll->month, 1, $payroll->year)) }}
                            </div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                            <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Payable Days</div>
                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100 mt-1">
                                {{ $payroll->working_days ?? 'N/A' }} days
                            </div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                            <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Pay Date</div>
                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100 mt-1">
                                {{ $payroll->created_at->format('M d, Y') }}
                            </div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                            <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Payment Method</div>
                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100 mt-1">
                                Bank Transfer
                            </div>
                        </div>
                    </div>

                    {{-- Bonuses Breakdown --}}
                    @if($appliedBonuses->count() > 0)
                        <div class="mt-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-green-800 dark:text-green-200 mb-2">Bonuses Applied</h4>
                            <div class="space-y-1">
                                @foreach($appliedBonuses as $bonus)
                                    <div class="flex justify-between text-xs text-green-700 dark:text-green-300">
                                        <span>{{ ucfirst(str_replace('_', ' ', $bonus->bonus_type)) }} ({{ ucfirst(str_replace('_', ' ', $bonus->frequency)) }})</span>
                                        <span class="font-semibold">{{ number_format($bonus->amount, 2) }} MVR</span>
                                    </div>
                                @endforeach
                                <div class="flex justify-between text-xs font-semibold text-green-800 dark:text-green-200 pt-1 mt-1 border-t border-green-200 dark:border-green-700">
                                    <span>Total Bonuses</span>
                                    <span>{{ number_format($appliedBonuses->sum('amount'), 2) }} MVR</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Allowances Breakdown --}}
                    @if($appliedAllowances->count() > 0)
                        <div class="mt-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-blue-800 dark:text-blue-200 mb-2">Allowances Applied</h4>
                            <div class="space-y-1">
                                @foreach($appliedAllowances as $allowanceData)
                                    <div class="flex justify-between text-xs text-blue-700 dark:text-blue-300">
                                        <span>{{ ucfirst(str_replace('_', ' ', $allowanceData['model']->allowance_type)) }} Allowance</span>
                                        <span class="font-semibold">{{ number_format($allowanceData['prorated_amount'], 2) }} MVR</span>
                                    </div>
                                @endforeach
                                <div class="flex justify-between text-xs font-semibold text-blue-800 dark:text-blue-200 pt-1 mt-1 border-t border-blue-200 dark:border-blue-700">
                                    <span>Total Payable Allowances</span>
                                    <span>{{ number_format($appliedAllowances->sum('prorated_amount'), 2) }} MVR</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($payroll->employee->activeLoans()->count() > 0)
                        <div class="mt-6 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-yellow-800 dark:text-yellow-200 mb-2">Active Loans</h4>
                            <div class="space-y-1">
                                @foreach($payroll->employee->activeLoans as $loan)
                                    <div class="flex justify-between text-xs text-yellow-700 dark:text-yellow-300">
                                        <span>{{ ucfirst(str_replace('_', ' ', $loan->loan_type)) }}</span>
                                        <span>Remaining: {{ number_format($loan->remaining_balance, 2) }} MVR</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Footer Notes --}}
                    <div class="mt-8 payslip-footer pt-6 border-t border-gray-200 dark:border-gray-700 text-xs text-gray-500 dark:text-gray-400 text-center">
                        <p>This is a computer-generated salary slip and does not require a signature.</p>
                        <p class="mt-1">For any queries, please contact the HR department.</p>
                        <p class="mt-3 text-gray-400 dark:text-gray-500">Generated on {{ now()->format('M d, Y h:i A') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @media print {
            @page {
                size: A4;
                margin: 8mm;
            }
            body {
                background: white;
                font-size: 11px;
            }
            nav, .print\:hidden, header {
                display: none !important;
            }
            .print\:block {
                display: block !important;
            }
            .py-6, .sm\:px-4, .lg\:px-8, .max-w-4xl {
                padding: 0 !important;
                margin: 0 !important;
                max-width: 100% !important;
            }
            .shadow-sm, .rounded-lg, .rounded-xl, .sm\:rounded-lg {
                box-shadow: none !important;
            }
            .payslip-sheet {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                border: none !important;
                background: white !important;
            }
            .payslip-header {
                padding: 16px 18px !important;
            }
            .payslip-body {
                padding: 14px 16px !important;
            }
            .payslip-highlight {
                margin-top: 18px !important;
                padding: 14px !important;
            }
            .payslip-footer {
                margin-top: 18px !important;
                padding-top: 12px !important;
            }
            h1 {
                font-size: 20px !important;
                line-height: 1.2 !important;
            }
            h2, h3, h4 {
                line-height: 1.2 !important;
            }
            .text-3xl {
                font-size: 24px !important;
                line-height: 1.1 !important;
            }
            .text-2xl {
                font-size: 20px !important;
                line-height: 1.1 !important;
            }
            .text-xl, .text-lg {
                line-height: 1.2 !important;
            }
            .grid {
                gap: 10px !important;
            }
            .space-y-3 > :not([hidden]) ~ :not([hidden]) {
                margin-top: 8px !important;
            }
            .space-y-2 > :not([hidden]) ~ :not([hidden]) {
                margin-top: 6px !important;
            }
            .mt-8 {
                margin-top: 16px !important;
            }
            .mt-6 {
                margin-top: 12px !important;
            }
            .mt-5, .mt-4 {
                margin-top: 10px !important;
            }
            .pt-6 {
                padding-top: 12px !important;
            }
            .p-6, .p-5, .p-4 {
                padding: 12px !important;
            }
            .px-6 {
                padding-left: 16px !important;
                padding-right: 16px !important;
            }
            .py-8 {
                padding-top: 16px !important;
                padding-bottom: 16px !important;
            }
            .break-inside-avoid,
            .rounded-xl,
            .rounded-lg {
                break-inside: avoid !important;
                page-break-inside: avoid !important;
            }
            .bg-gradient-to-r {
                background: #4338ca !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .dark\:bg-gray-800 {
                background-color: white !important;
            }
            .dark\:text-gray-100, .dark\:text-gray-200 {
                color: black !important;
            }
            .dark\:bg-gray-900 {
                background-color: #f9fafb !important;
            }
            .border, .border-t, .border-b-2 {
                border-color: #d1d5db !important;
            }
        }
    </style>

    @php
        $slipFileName = $payroll->employee->name . ' - Salary Slip - ' . date('F Y', mktime(0, 0, 0, $payroll->month, 1, $payroll->year));
    @endphp

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const slipFileName = @json($slipFileName);
                const defaultTitle = document.title;

                document.title = slipFileName;

                window.addEventListener('beforeprint', () => {
                    document.title = slipFileName;
                });

                window.addEventListener('afterprint', () => {
                    document.title = slipFileName || defaultTitle;
                });
            });
        </script>
    @endpush
</x-app-layout>
