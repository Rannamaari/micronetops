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

        // Get active employees who were hired before or during this month
        $monthEnd = Carbon::create($year, $month, 1)->endOfMonth();
        $employees = Employee::where('status', 'active')
            ->where(function($q) use ($monthEnd) {
                $q->whereNull('hire_date')
                  ->orWhere('hire_date', '<=', $monthEnd);
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
        $basicSalary = $employee->basic_salary;

        // Get all active allowances (monthly frequency)
        $allowances = $employee->allowances()
            ->where('is_active', true)
            ->where('frequency', 'monthly')
            ->sum('amount');

        // Calculate automatic bonuses for this month
        $automaticBonuses = 0;
        $activeBonuses = $employee->bonuses()->where('status', 'active')->get();

        foreach ($activeBonuses as $bonus) {
            if ($bonus->isApplicableFor($year, $month)) {
                $automaticBonuses += $bonus->amount;
            }
        }

        // Calculate actual working period for this employee
        $monthStart = Carbon::create($year, $month, 1)->startOfMonth();
        $monthEnd = Carbon::create($year, $month, 1)->endOfMonth();

        // Determine actual start date (hire date if hired mid-month)
        $actualStartDate = $monthStart->copy();
        if ($employee->hire_date) {
            $hireDate = Carbon::parse($employee->hire_date);
            if ($hireDate->gt($monthStart) && $hireDate->lte($monthEnd)) {
                $actualStartDate = $hireDate;
            }
        }

        // Calculate working days (excluding Fridays) from hire date to month end
        $expectedWorkingDays = EmployeeAttendance::getWorkingDaysBetween($actualStartDate, $monthEnd);

        // Prevent division by zero
        if ($expectedWorkingDays <= 0) {
            $expectedWorkingDays = 1;
        }

        // Get absent days from attendance records
        $attendanceAbsentDays = EmployeeAttendance::getAbsentDays($employee->id, $year, $month);
        $absentDays = $attendanceAbsentDays > 0 ? $attendanceAbsentDays : ($manualAbsentDays ?? 0);

        // Calculate actual days worked
        $actualWorkedDays = $expectedWorkingDays - $absentDays;
        if ($actualWorkedDays < 0) {
            $actualWorkedDays = 0;
        }

        // Calculate daily rate based on expected working days (from hire date)
        $dailyBasicRate = $basicSalary / $expectedWorkingDays;

        // Calculate payable basic salary ONLY for days actually worked
        $payableBasicSalary = $dailyBasicRate * $actualWorkedDays;

        // Calculate daily allowance rate
        $dailyAllowanceRate = $allowances / $expectedWorkingDays;

        // Calculate payable allowances ONLY for days actually worked
        $payableAllowances = $dailyAllowanceRate * $actualWorkedDays;

        // Calculate daily bonus rate
        $dailyBonusRate = $automaticBonuses / $expectedWorkingDays;

        // Calculate payable bonuses ONLY for days actually worked
        $payableBonuses = $dailyBonusRate * $actualWorkedDays;

        // Get total loan deductions for this month
        $loanDeduction = $employee->activeLoans()->sum('monthly_deduction');

        // Calculate totals
        $grossSalary = $payableBasicSalary + $payableAllowances + $payableBonuses;
        $totalDeductions = $loanDeduction;
        $netSalary = $grossSalary - $totalDeductions;

        return [
            'employee_id' => $employee->id,
            'year' => $year,
            'month' => $month,
            'basic_salary' => $payableBasicSalary, // Store PAYABLE amount (prorated for worked days)
            'allowances' => $payableAllowances, // Store PAYABLE amount (prorated for worked days)
            'bonuses' => $payableBonuses, // Store PAYABLE amount (prorated for worked days)
            'overtime' => 0, // Can be added later
            'loan_deduction' => $loanDeduction,
            'absent_deduction' => 0, // Not used - we pay only for worked days instead
            'absent_days' => $absentDays,
            'working_days' => $expectedWorkingDays, // Expected working days from hire date
            'prorated_deduction' => 0, // Not used - we pay only for worked days instead
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
