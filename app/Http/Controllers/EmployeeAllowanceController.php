<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeAllowance;
use Illuminate\Http\Request;

class EmployeeAllowanceController extends Controller
{
    /**
     * Display allowances for an employee
     */
    public function index(Employee $employee)
    {
        $allowances = $employee->allowances()
            ->orderBy('created_at', 'desc')
            ->get();

        return view('employees.allowances.index', compact('employee', 'allowances'));
    }

    /**
     * Show form to create a new allowance
     */
    public function create(Employee $employee)
    {
        return view('employees.allowances.create', compact('employee'));
    }

    /**
     * Store a new allowance
     */
    public function store(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'allowance_type' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:0'],
            'frequency' => ['required', 'in:monthly,quarterly,yearly,one-time'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'description' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['is_active'] = true;

        $allowance = $employee->allowances()->create($validated);

        return redirect()
            ->route('employees.allowances.index', $employee)
            ->with('success', ucfirst($validated['allowance_type']) . " allowance of MVR " . number_format($validated['amount'], 2) . " has been added successfully.");
    }

    /**
     * Show form to edit an allowance
     */
    public function edit(Employee $employee, EmployeeAllowance $allowance)
    {
        return view('employees.allowances.edit', compact('employee', 'allowance'));
    }

    /**
     * Update an allowance
     */
    public function update(Request $request, Employee $employee, EmployeeAllowance $allowance)
    {
        $validated = $request->validate([
            'allowance_type' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:0'],
            'frequency' => ['required', 'in:monthly,quarterly,yearly,one-time'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'description' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ]);

        $allowance->update($validated);

        return redirect()
            ->route('employees.allowances.index', $employee)
            ->with('success', 'Allowance has been updated successfully.');
    }

    /**
     * Delete an allowance
     */
    public function destroy(Employee $employee, EmployeeAllowance $allowance)
    {
        $allowance->delete();

        return redirect()
            ->route('employees.allowances.index', $employee)
            ->with('success', 'Allowance has been deleted successfully.');
    }
}
