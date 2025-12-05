<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeLoan;
use Illuminate\Http\Request;

class EmployeeLoanController extends Controller
{
    /**
     * Display all loans across all employees
     */
    public function allLoans(Request $request)
    {
        $status = $request->get('status', 'all');
        $loanType = $request->get('loan_type', 'all');

        $query = EmployeeLoan::with('employee')
            ->orderBy('created_at', 'desc');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($loanType !== 'all') {
            $query->where('loan_type', $loanType);
        }

        $loans = $query->get();

        // Calculate totals
        $activeLoans = EmployeeLoan::where('status', 'active')->get();
        $totalOutstanding = $activeLoans->sum('remaining_balance');
        $totalMonthlyDeduction = $activeLoans->sum('monthly_deduction');
        $totalLoansCount = EmployeeLoan::where('status', 'active')->count();

        return view('loans.index', compact('loans', 'status', 'loanType', 'totalOutstanding', 'totalMonthlyDeduction', 'totalLoansCount'));
    }

    /**
     * Display loans for an employee
     */
    public function index(Employee $employee)
    {
        $loans = $employee->loans()
            ->orderBy('created_at', 'desc')
            ->get();

        return view('employees.loans.index', compact('employee', 'loans'));
    }

    /**
     * Show form to create a new loan
     */
    public function create(Employee $employee)
    {
        return view('employees.loans.create', compact('employee'));
    }

    /**
     * Store a new loan
     */
    public function store(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'loan_type' => ['required', 'in:loan,salary_advance'],
            'amount' => ['required', 'numeric', 'min:0'],
            'monthly_deduction' => ['required', 'numeric', 'min:0'],
            'loan_date' => ['required', 'date'],
            'reason' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $loan = $employee->loans()->create([
            'loan_type' => $validated['loan_type'],
            'amount' => $validated['amount'],
            'remaining_balance' => $validated['amount'],
            'monthly_deduction' => $validated['monthly_deduction'],
            'loan_date' => $validated['loan_date'],
            'reason' => $validated['reason'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => 'active',
        ]);

        return redirect()
            ->route('employees.loans.index', $employee)
            ->with('success', ucfirst($validated['loan_type']) . " of MVR " . number_format($validated['amount'], 2) . " has been created.");
    }

    /**
     * Show form to edit a loan
     */
    public function edit(Employee $employee, EmployeeLoan $loan)
    {
        return view('employees.loans.edit', compact('employee', 'loan'));
    }

    /**
     * Update a loan
     */
    public function update(Request $request, Employee $employee, EmployeeLoan $loan)
    {
        $validated = $request->validate([
            'loan_type' => ['required', 'in:loan,salary_advance'],
            'amount' => ['required', 'numeric', 'min:0'],
            'monthly_deduction' => ['required', 'numeric', 'min:0'],
            'loan_date' => ['required', 'date'],
            'reason' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        // If amount changed, update remaining balance proportionally
        if ($validated['amount'] != $loan->amount) {
            $paidAmount = $loan->amount - $loan->remaining_balance;
            $validated['remaining_balance'] = $validated['amount'] - $paidAmount;
        }

        $loan->update($validated);

        return redirect()
            ->route('employees.loans.index', $employee)
            ->with('success', 'Loan has been updated successfully.');
    }

    /**
     * Mark loan as paid
     */
    public function markAsPaid(Employee $employee, EmployeeLoan $loan)
    {
        $loan->update([
            'remaining_balance' => 0,
            'status' => 'completed',
        ]);

        return redirect()
            ->route('employees.loans.index', $employee)
            ->with('success', 'Loan has been marked as fully paid.');
    }

    /**
     * Delete a loan
     */
    public function destroy(Employee $employee, EmployeeLoan $loan)
    {
        $loan->delete();

        return redirect()
            ->route('employees.loans.index', $employee)
            ->with('success', 'Loan has been deleted successfully.');
    }
}
