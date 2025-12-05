<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeLeave;
use Illuminate\Http\Request;

class EmployeeLeaveController extends Controller
{
    /**
     * Display leaves for an employee
     */
    public function index(Employee $employee)
    {
        $leaves = $employee->leaves()
            ->orderBy('start_date', 'desc')
            ->paginate(20);

        return view('employees.leaves.index', compact('employee', 'leaves'));
    }

    /**
     * Show form to create a new leave
     */
    public function create(Employee $employee)
    {
        return view('employees.leaves.create', compact('employee'));
    }

    /**
     * Store a new leave record
     */
    public function store(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'leave_type' => ['required', 'in:annual,sick,unpaid,emergency,maternity,paternity'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        // Calculate days between start and end date
        $startDate = \Carbon\Carbon::parse($validated['start_date']);
        $endDate = \Carbon\Carbon::parse($validated['end_date']);
        $days = $startDate->diffInDays($endDate) + 1; // +1 to include both start and end date

        // Create leave record
        $leave = $employee->leaves()->create([
            'leave_type' => $validated['leave_type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'days' => $days,
            'reason' => $validated['reason'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => 'approved', // HR/Admin enters directly as approved
            'approved_by' => auth()->id(),
            'approved_date' => now(),
        ]);

        return redirect()
            ->route('employees.leaves.index', $employee)
            ->with('success', "Leave record for {$days} day(s) has been added successfully.");
    }

    /**
     * Show form to edit a leave
     */
    public function edit(Employee $employee, EmployeeLeave $leave)
    {
        return view('employees.leaves.edit', compact('employee', 'leave'));
    }

    /**
     * Update a leave record
     */
    public function update(Request $request, Employee $employee, EmployeeLeave $leave)
    {
        $validated = $request->validate([
            'leave_type' => ['required', 'in:annual,sick,unpaid,emergency,maternity,paternity'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        // Recalculate days
        $startDate = \Carbon\Carbon::parse($validated['start_date']);
        $endDate = \Carbon\Carbon::parse($validated['end_date']);
        $days = $startDate->diffInDays($endDate) + 1;

        $leave->update([
            'leave_type' => $validated['leave_type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'days' => $days,
            'reason' => $validated['reason'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()
            ->route('employees.leaves.index', $employee)
            ->with('success', 'Leave record has been updated successfully.');
    }

    /**
     * Delete a leave record
     */
    public function destroy(Employee $employee, EmployeeLeave $leave)
    {
        $leave->delete();

        return redirect()
            ->route('employees.leaves.index', $employee)
            ->with('success', 'Leave record has been deleted successfully.');
    }
}
