<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeSalary;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    /**
     * Show payroll dashboard
     */
    public function index(Request $request)
    {
        // If no year/month specified, default to the most recently processed payroll
        if (!$request->has('year') || !$request->has('month')) {
            $latestPayroll = EmployeeSalary::orderByDesc('year')
                ->orderByDesc('month')
                ->orderByDesc('created_at')
                ->first();

            if ($latestPayroll) {
                $year = $latestPayroll->year;
                $month = $latestPayroll->month;
            } else {
                // No payroll exists, default to current month
                $year = Carbon::now()->year;
                $month = Carbon::now()->month;
            }
        } else {
            $year = $request->get('year');
            $month = $request->get('month');
        }

        // Get all payroll records for selected month
        $payrolls = EmployeeSalary::with('employee')
            ->where('year', $year)
            ->where('month', $month)
            ->orderBy('created_at', 'desc')
            ->get();

        // Get info about the last processed payroll for display
        $lastProcessedPayroll = EmployeeSalary::orderByDesc('created_at')->first();

        return view('payroll.index', compact('payrolls', 'year', 'month', 'lastProcessedPayroll'));
    }

    /**
     * Show form to run payroll for a specific month
     */
    public function create(Request $request)
    {
        $year = $request->get('year', Carbon::now()->year);
        $month = $request->get('month', Carbon::now()->month);

        // Get employees who were employed for any part of this payroll month.
        $monthStart = Carbon::create($year, $month, 1)->startOfMonth();
        $monthEnd = Carbon::create($year, $month, 1)->endOfMonth();
        $employees = Employee::where(function($q) {
                $q->where('status', 'active')
                  ->orWhere('status', 'terminated');
            })
            ->where(function($q) use ($monthEnd) {
                $q->whereNull('hire_date')
                  ->orWhere('hire_date', '<=', $monthEnd);
            })
            ->where(function($q) use ($monthStart) {
                $q->whereNull('termination_date')
                  ->orWhere('termination_date', '>=', $monthStart);
            })
            ->orderBy('name')
            ->get();

        // Check if payroll already run for this month
        $existingPayroll = EmployeeSalary::where('year', $year)
            ->where('month', $month)
            ->exists();

        // Check if attendance is marked for all employees
        $attendanceNotMarked = [];
        foreach ($employees as $employee) {
            if (!EmployeeAttendance::isMarkedForMonth($employee->id, $year, $month)) {
                $attendanceNotMarked[] = $employee->name;
            }
        }

        return view('payroll.create', compact('employees', 'year', 'month', 'existingPayroll', 'attendanceNotMarked'));
    }

    /**
     * Run payroll for selected employees
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'year' => ['required', 'integer'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'employee_ids' => ['required', 'array'],
            'employee_ids.*' => ['exists:employees,id'],
            'bonuses' => ['nullable', 'array'],
            'bonuses.*' => ['nullable', 'numeric', 'min:0'],
            'absent_days' => ['nullable', 'array'],
            'absent_days.*' => ['nullable', 'integer', 'min:0', 'max:31'],
            'other_deductions' => ['nullable', 'array'],
            'other_deductions.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        $year = $validated['year'];
        $month = $validated['month'];
        $bonuses = $validated['bonuses'] ?? [];
        $absentDays = $validated['absent_days'] ?? [];
        $otherDeductions = $validated['other_deductions'] ?? [];

        $createdCount = 0;

        foreach ($validated['employee_ids'] as $employeeId) {
            // Check if payroll already exists
            $existing = EmployeeSalary::where('employee_id', $employeeId)
                ->where('year', $year)
                ->where('month', $month)
                ->exists();

            if ($existing) {
                continue; // Skip if already exists
            }

            $employee = Employee::find($employeeId);

            // Get absent days from form (override default calculation)
            $manualAbsentDays = $absentDays[$employeeId] ?? null;

            // Calculate payroll
            $payrollData = $this->calculatePayroll($employee, $year, $month, $manualAbsentDays);

            // Add manual bonus and other deductions from form (add to automatic bonuses, don't replace)
            $payrollData['bonuses'] += $bonuses[$employeeId] ?? 0;
            $payrollData['other_deductions'] += $otherDeductions[$employeeId] ?? 0;

            // Recalculate totals
            $payrollData['gross_salary'] = $payrollData['basic_salary'] +
                                          $payrollData['allowances'] +
                                          $payrollData['bonuses'] +
                                          $payrollData['overtime'];

            $payrollData['total_deductions'] = $payrollData['loan_deduction'] +
                                               $payrollData['absent_deduction'] +
                                               $payrollData['other_deductions'];

            $payrollData['net_salary'] = $payrollData['gross_salary'] - $payrollData['total_deductions'];

            // Create salary record
            EmployeeSalary::create($payrollData);

            // Deduct from active loans
            $this->deductFromLoans($employee, $payrollData['loan_deduction']);

            $createdCount++;
        }

        return redirect()
            ->route('payroll.index', ['year' => $year, 'month' => $month])
            ->with('success', "Payroll processed successfully for {$createdCount} employee(s).");
    }

    /**
     * Calculate payroll for an employee for a specific month
     */
    private function calculatePayroll(Employee $employee, int $year, int $month, ?int $manualAbsentDays = null)
    {
        $period = $employee->getEmploymentPeriodForMonth($year, $month);
        if (!$period) {
            return [
                'employee_id' => $employee->id,
                'year' => $year,
                'month' => $month,
                'basic_salary' => 0,
                'allowances' => 0,
                'bonuses' => 0,
                'overtime' => 0,
                'loan_deduction' => 0,
                'absent_deduction' => 0,
                'absent_days' => 0,
                'working_days' => 0,
                'prorated_deduction' => 0,
                'other_deductions' => 0,
                'gross_salary' => 0,
                'total_deductions' => 0,
                'net_salary' => 0,
            ];
        }

        $basicSalary = $employee->basic_salary;

        // Monthly fixed allowances are prorated by calendar days within the employment period.
        $payableAllowances = $employee->getProratedMonthlyAllowancesForMonth($year, $month);

        // Calculate automatic bonuses for this month
        $automaticBonuses = 0;
        $activeBonuses = $employee->bonuses()->where('status', 'active')->get();

        foreach ($activeBonuses as $bonus) {
            if ($bonus->isApplicableFor($year, $month)) {
                $automaticBonuses += $bonus->amount;
            }
        }

        // Working-day logic remains only for attendance-based adjustments like unpaid leave
        // and the current recurring bonus behavior.
        $fullMonthWorkingDays = EmployeeAttendance::getWorkingDaysInMonth($year, $month);
        $expectedWorkingDays = EmployeeAttendance::getWorkingDaysBetween(
            $period['pay_period_start'],
            $period['pay_period_end']
        );

        // Prevent division by zero
        if ($fullMonthWorkingDays <= 0) {
            $fullMonthWorkingDays = 1;
        }

        // Get absent days from attendance records
        $attendanceAbsentDays = EmployeeAttendance::getAbsentDays($employee->id, $year, $month);
        $absentDays = $attendanceAbsentDays > 0 ? $attendanceAbsentDays : ($manualAbsentDays ?? 0);
        $dailyBonusRate = $automaticBonuses / $fullMonthWorkingDays;
        $calendarDailyBasicRate = $period['days_in_month'] > 0
            ? ((float) $basicSalary / $period['days_in_month'])
            : 0;

        // Basic salary is prorated by payable calendar days, inclusive of join date,
        // rest days, and public holidays while employed.
        $payableBasicSalary = $employee->getProratedBasicSalaryForMonth($year, $month);

        // Bonuses: Pay for EXPECTED days from hire date (NOT reduced for absences)
        $payableBonuses = round($dailyBonusRate * $expectedWorkingDays, 2);

        // Unpaid leave / absence stays as a separate deduction from the gross amount.
        $absentDeduction = round($calendarDailyBasicRate * $absentDays, 2);

        // Get total loan deductions for this month
        $loanDeduction = $employee->activeLoans()->sum('monthly_deduction');

        // Calculate totals
        $grossSalary = $payableBasicSalary + $payableAllowances + $payableBonuses;
        $totalDeductions = $loanDeduction + $absentDeduction;
        $netSalary = $grossSalary - $totalDeductions;

        return [
            'employee_id' => $employee->id,
            'year' => $year,
            'month' => $month,
            'basic_salary' => $payableBasicSalary,
            'allowances' => $payableAllowances,
            'bonuses' => $payableBonuses,
            'overtime' => 0, // Can be added later
            'loan_deduction' => $loanDeduction,
            'absent_deduction' => $absentDeduction,
            'absent_days' => $absentDays,
            'working_days' => $period['payable_calendar_days'],
            'prorated_deduction' => 0,
            'other_deductions' => 0, // Will be added from form
            'gross_salary' => $grossSalary,
            'total_deductions' => $totalDeductions,
            'net_salary' => $netSalary,
        ];
    }

    /**
     * Deduct amount from active loans
     */
    private function deductFromLoans(Employee $employee, float $amount)
    {
        if ($amount <= 0) {
            return;
        }

        $loans = $employee->activeLoans()
            ->orderBy('loan_date')
            ->get();

        $remainingAmount = $amount;

        foreach ($loans as $loan) {
            if ($remainingAmount <= 0) {
                break;
            }

            $deductAmount = min($remainingAmount, $loan->remaining_balance, $loan->monthly_deduction);

            $loan->remaining_balance -= $deductAmount;

            if ($loan->remaining_balance <= 0) {
                $loan->status = 'completed';
            }

            $loan->save();

            $remainingAmount -= $deductAmount;
        }
    }

    /**
     * Show a single payroll record
     */
    public function show(EmployeeSalary $payroll)
    {
        $payroll->load('employee');
        return view('payroll.show', compact('payroll'));
    }

    /**
     * Delete a payroll record
     */
    public function destroy(EmployeeSalary $payroll)
    {
        $payroll->delete();

        return redirect()
            ->route('payroll.index')
            ->with('success', 'Payroll record has been deleted.');
    }
}
