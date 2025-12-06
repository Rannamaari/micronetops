<?php

namespace App\Http\Controllers;

use App\Models\Employee;
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

        // Get active employees
        $employees = Employee::where('status', 'active')->orderBy('name')->get();

        // Check if payroll already run for this month
        $existingPayroll = EmployeeSalary::where('year', $year)
            ->where('month', $month)
            ->exists();

        return view('payroll.create', compact('employees', 'year', 'month', 'existingPayroll'));
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
            'other_deductions' => ['nullable', 'array'],
            'other_deductions.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        $year = $validated['year'];
        $month = $validated['month'];
        $bonuses = $validated['bonuses'] ?? [];
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

            // Calculate payroll
            $payrollData = $this->calculatePayroll($employee, $year, $month);

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
    private function calculatePayroll(Employee $employee, int $year, int $month)
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

        // Calculate working days in month
        $daysInMonth = Carbon::create($year, $month)->daysInMonth;

        // Get approved leaves for this month (sick + annual)
        $approvedLeaveDays = $employee->leaves()
            ->where('status', 'approved')
            ->whereIn('leave_type', ['sick', 'annual', 'emergency', 'maternity', 'paternity'])
            ->whereYear('start_date', $year)
            ->whereMonth('start_date', $month)
            ->sum('days');

        // Assume working days = days in month (can be adjusted for weekends if needed)
        $workingDays = $daysInMonth;

        // Absent days = working days - approved leave days
        // Note: We assume employee worked all days except approved leave
        // If you want to track actual attendance, you'll need an attendance system
        $absentDays = 0; // Default to 0 (no deduction unless specified)

        // Calculate absent day deduction (daily rate * absent days)
        $dailyRate = $basicSalary / $workingDays;
        $absentDeduction = $absentDays * $dailyRate;

        // Get total loan deductions for this month
        $loanDeduction = $employee->activeLoans()->sum('monthly_deduction');

        return [
            'employee_id' => $employee->id,
            'year' => $year,
            'month' => $month,
            'basic_salary' => $basicSalary,
            'allowances' => $allowances,
            'bonuses' => $automaticBonuses, // Automatically calculated from employee bonuses
            'overtime' => 0, // Can be added later
            'loan_deduction' => $loanDeduction,
            'absent_deduction' => $absentDeduction,
            'absent_days' => $absentDays,
            'other_deductions' => 0, // Will be added from form
            'gross_salary' => $basicSalary + $allowances + $automaticBonuses,
            'total_deductions' => $loanDeduction + $absentDeduction,
            'net_salary' => ($basicSalary + $allowances + $automaticBonuses) - ($loanDeduction + $absentDeduction),
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
