<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class HrAttendanceController extends Controller
{
    /**
     * GET /api/hr/attendance
     * - by date:   ?date=YYYY-MM-DD
     * - by month:  ?year=YYYY&month=MM
     */
    public function index(Request $request): JsonResponse
    {
        $date = $request->query('date');
        $year = $request->query('year');
        $month = $request->query('month');

        $query = EmployeeAttendance::with('employee:id,name,employee_number,company')
            ->orderBy('date')
            ->orderBy('employee_id');

        if ($date) {
            $query->whereDate('date', $date);
        } elseif ($year && $month) {
            $query->whereYear('date', (int) $year)->whereMonth('date', (int) $month);
        } else {
            return response()->json([
                'error' => 'Provide either ?date=YYYY-MM-DD or ?year=YYYY&month=MM',
            ], 422);
        }

        $records = $query->get();

        return response()->json([
            'total' => $records->count(),
            'data' => $records->map(fn (EmployeeAttendance $a) => [
                'id' => $a->id,
                'employee_id' => $a->employee_id,
                'employee' => [
                    'name' => $a->employee?->name,
                    'employee_number' => $a->employee?->employee_number,
                    'company' => $a->employee?->company,
                ],
                'date' => $a->date?->format('Y-m-d'),
                'status' => $a->status,
                'absence_reason' => $a->absence_reason,
                'notes' => $a->notes,
            ]),
        ]);
    }

    /**
     * POST /api/hr/attendance
     * Mark single employee attendance for a day.
     */
    public function mark(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'employee_id' => ['required', 'exists:employees,id'],
                'date' => ['required', 'date'],
                'status' => ['required', Rule::in(['present', 'absent', 'holiday', 'leave'])],
                'absence_reason' => ['nullable', 'string', 'max:255'],
                'notes' => ['nullable', 'string', 'max:1000'],
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed.', 'details' => $e->errors()], 422);
        }

        $employee = Employee::find($validated['employee_id']);
        if (!$employee) {
            return response()->json(['error' => 'Employee not found.'], 404);
        }

        $date = Carbon::parse($validated['date'])->format('Y-m-d');

        $attendance = EmployeeAttendance::updateOrCreate(
            ['employee_id' => $employee->id, 'date' => $date],
            [
                'status' => $validated['status'],
                'absence_reason' => $validated['status'] === 'absent' || $validated['status'] === 'leave'
                    ? ($validated['absence_reason'] ?? null)
                    : null,
                'notes' => $validated['notes'] ?? null,
                'marked_by' => null,
            ]
        );

        ActivityLog::record(
            'hr.attendance_marked',
            "API: Attendance {$attendance->status} for employee #{$employee->id} on {$date}",
            $attendance,
            [],
            null,
            'api'
        );

        return response()->json([
            'message' => 'Attendance saved.',
            'data' => [
                'id' => $attendance->id,
                'employee_id' => $attendance->employee_id,
                'date' => $attendance->date->format('Y-m-d'),
                'status' => $attendance->status,
                'absence_reason' => $attendance->absence_reason,
                'notes' => $attendance->notes,
            ],
        ], 201);
    }

    /**
     * POST /api/hr/attendance/mark-all-present
     * Mark all active employees as present for a specific date (optionally filtered by company).
     *
     * Body:
     * {
     *   "date": "2026-04-23",
     *   "company": "Micronet",         // optional
     *   "overwrite": false,            // optional; if true overwrites existing records
     *   "notes": "Marked by bot"       // optional
     * }
     */
    public function markAllPresent(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'date' => ['required', 'date'],
                'company' => ['nullable', 'string', 'max:255'],
                'overwrite' => ['nullable', 'boolean'],
                'notes' => ['nullable', 'string', 'max:1000'],
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed.', 'details' => $e->errors()], 422);
        }

        $date = Carbon::parse($validated['date'])->format('Y-m-d');
        $overwrite = (bool) ($validated['overwrite'] ?? false);

        $employeesQuery = Employee::query()
            ->where('status', 'active')
            ->orderBy('id');

        if (!empty($validated['company'])) {
            $employeesQuery->where('company', $validated['company']);
        }

        // Only employees hired on/before the date (or hire date not set)
        $employeesQuery->where(function ($q) use ($date) {
            $q->whereNull('hire_date')
                ->orWhereDate('hire_date', '<=', $date);
        });

        $employees = $employeesQuery->get(['id', 'name']);

        $created = 0;
        $updated = 0;
        foreach ($employees as $employee) {
            if (!$overwrite) {
                $exists = EmployeeAttendance::where('employee_id', $employee->id)
                    ->whereDate('date', $date)
                    ->exists();
                if ($exists) {
                    continue;
                }
            }

            $attendance = EmployeeAttendance::updateOrCreate(
                ['employee_id' => $employee->id, 'date' => $date],
                [
                    'status' => 'present',
                    'absence_reason' => null,
                    'notes' => $validated['notes'] ?? null,
                    'marked_by' => null,
                ]
            );

            if ($attendance->wasRecentlyCreated) {
                $created++;
            } else {
                $updated++;
            }
        }

        ActivityLog::record(
            'hr.attendance_mark_all_present',
            'API: Marked all present for ' . $date
                . (!empty($validated['company']) ? ' (' . $validated['company'] . ')' : '')
                . ($overwrite ? ' (overwrite)' : ''),
            null,
            [],
            null,
            'api'
        );

        return response()->json([
            'message' => 'Attendance updated.',
            'date' => $date,
            'company' => $validated['company'] ?? null,
            'employees_total' => $employees->count(),
            'created' => $created,
            'updated' => $updated,
            'skipped' => max(0, $employees->count() - $created - $updated),
        ]);
    }
}
