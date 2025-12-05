<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeBonus;
use Illuminate\Http\Request;

class EmployeeBonusController extends Controller
{
    /**
     * Display bonuses for an employee
     */
    public function index(Employee $employee)
    {
        $bonuses = $employee->bonuses()
            ->orderBy('awarded_date', 'desc')
            ->get();

        return view('employees.bonuses.index', compact('employee', 'bonuses'));
    }

    /**
     * Show form to create a new bonus
     */
    public function create(Employee $employee)
    {
        return view('employees.bonuses.create', compact('employee'));
    }

    /**
     * Store a new bonus
     */
    public function store(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'bonus_type' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:0'],
            'frequency' => ['required', 'in:one_time,monthly,quarterly,annual'],
            'awarded_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:awarded_date'],
            'reason' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['status'] = 'active';

        $bonus = $employee->bonuses()->create($validated);

        return redirect()
            ->route('employees.bonuses.index', $employee)
            ->with('success', "Bonus of MVR " . number_format($validated['amount'], 2) . " has been added successfully.");
    }

    /**
     * Show form to edit a bonus
     */
    public function edit(Employee $employee, EmployeeBonus $bonus)
    {
        return view('employees.bonuses.edit', compact('employee', 'bonus'));
    }

    /**
     * Update a bonus
     */
    public function update(Request $request, Employee $employee, EmployeeBonus $bonus)
    {
        $validated = $request->validate([
            'bonus_type' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:0'],
            'frequency' => ['required', 'in:one_time,monthly,quarterly,annual'],
            'awarded_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:awarded_date'],
            'reason' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', 'in:active,expired,cancelled'],
        ]);

        $bonus->update($validated);

        return redirect()
            ->route('employees.bonuses.index', $employee)
            ->with('success', 'Bonus has been updated successfully.');
    }

    /**
     * Delete a bonus
     */
    public function destroy(Employee $employee, EmployeeBonus $bonus)
    {
        $bonus->delete();

        return redirect()
            ->route('employees.bonuses.index', $employee)
            ->with('success', 'Bonus has been deleted successfully.');
    }
}
