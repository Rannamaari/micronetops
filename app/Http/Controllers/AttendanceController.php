<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeLeave;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * Show attendance marking page
     */
    public function index(Request $request)
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

        // Generate calendar days for the month
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        $daysInMonth = $startDate->daysInMonth;

        // Build days array with day info
        $days = [];
        $current = $startDate->copy();
        while ($current <= $endDate) {
            $days[] = [
                'date' => $current->format('Y-m-d'),
                'day' => $current->day,
                'dayName' => $current->format('D'),
                'isFriday' => $current->dayOfWeek === Carbon::FRIDAY,
                'isToday' => $current->isToday(),
            ];
            $current->addDay();
        }

        // Get existing attendance records for all employees this month
        $attendanceRecords = EmployeeAttendance::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get()
            ->groupBy('employee_id');

        // Get approved leaves for this month
        $approvedLeaves = EmployeeLeave::where('status', 'approved')
            ->where(function($q) use ($year, $month) {
                $q->whereYear('start_date', $year)->whereMonth('start_date', $month)
                  ->orWhereYear('end_date', $year)->whereMonth('end_date', $month);
            })
            ->get()
            ->groupBy('employee_id');

        // Check if attendance is fully marked
        $allMarked = true;
        foreach ($employees as $employee) {
            if (!EmployeeAttendance::isMarkedForMonth($employee->id, $year, $month)) {
                $allMarked = false;
                break;
            }
        }

        return view('attendance.index', compact(
            'employees',
            'days',
            'year',
            'month',
            'attendanceRecords',
            'approvedLeaves',
            'allMarked'
        ));
    }

    /**
     * Save attendance for a month
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'year' => ['required', 'integer'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'attendance' => ['nullable', 'array'],
            'attendance.*' => ['array'],
            'absence_reason' => ['nullable', 'array'],
            'absence_reason.*' => ['array'],
        ]);

        $year = $validated['year'];
        $month = $validated['month'];
        $attendanceData = $validated['attendance'] ?? [];
        $absenceReasons = $validated['absence_reason'] ?? [];

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        // Get all active employees for this month
        $monthEnd = Carbon::create($year, $month, 1)->endOfMonth();
        $employees = Employee::where('status', 'active')
            ->where(function($q) use ($monthEnd) {
                $q->whereNull('hire_date')
                  ->orWhere('hire_date', '<=', $monthEnd);
            })
            ->get();

        // Get approved leaves for this month
        $approvedLeaves = EmployeeLeave::where('status', 'approved')
            ->where(function($q) use ($year, $month) {
                $q->whereYear('start_date', $year)->whereMonth('start_date', $month)
                  ->orWhereYear('end_date', $year)->whereMonth('end_date', $month);
            })
            ->get();

        $processedCount = 0;

        foreach ($employees as $employee) {
            $employeeId = $employee->id;
            $dates = $attendanceData[$employeeId] ?? [];

            // Skip dates before employee hire date
            $hireDate = $employee->hire_date ? Carbon::parse($employee->hire_date) : null;

            // Loop through all days in the month
            $current = $startDate->copy();
            while ($current <= $endDate) {
                $dateStr = $current->format('Y-m-d');

                // Skip if before hire date
                if ($hireDate && $current->lt($hireDate)) {
                    $current->addDay();
                    continue;
                }

                // Check if this day is in an approved leave period
                $leaveForDay = $approvedLeaves->first(function($leave) use ($employeeId, $current) {
                    return $leave->employee_id == $employeeId &&
                           $current->between(Carbon::parse($leave->start_date), Carbon::parse($leave->end_date));
                });

                // Determine status and absence reason
                if ($current->dayOfWeek === Carbon::FRIDAY) {
                    $status = 'holiday';
                    $absenceReason = null;
                    $leaveId = null;
                } elseif ($leaveForDay) {
                    // Auto-mark as leave from approved leave records
                    $status = 'leave';
                    $absenceReason = 'sick_leave'; // Approved leaves are paid
                    $leaveId = $leaveForDay->id;
                } elseif (isset($dates[$dateStr]) && $dates[$dateStr] === 'present') {
                    $status = 'present';
                    $absenceReason = null;
                    $leaveId = null;
                } else {
                    // Absent - check reason
                    $status = 'absent';
                    $absenceReason = $absenceReasons[$employeeId][$dateStr] ?? 'unpaid_leave';
                    $leaveId = null;
                }

                // Create or update attendance record
                EmployeeAttendance::updateOrCreate(
                    [
                        'employee_id' => $employeeId,
                        'date' => $dateStr,
                    ],
                    [
                        'status' => $status,
                        'absence_reason' => $absenceReason,
                        'leave_id' => $leaveId,
                        'marked_by' => auth()->id(),
                    ]
                );

                $current->addDay();
            }

            $processedCount++;
        }

        return redirect()
            ->route('attendance.index', ['year' => $year, 'month' => $month])
            ->with('success', "Attendance marked successfully for {$processedCount} employee(s).");
    }

    /**
     * Auto-mark all present (helper for testing)
     */
    public function markAllPresent(Request $request)
    {
        $year = $request->get('year', Carbon::now()->year);
        $month = $request->get('month', Carbon::now()->month);

        $employees = Employee::where('status', 'active')->get();
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        foreach ($employees as $employee) {
            $current = $startDate->copy();
            while ($current <= $endDate) {
                $status = $current->dayOfWeek === Carbon::FRIDAY ? 'holiday' : 'present';

                EmployeeAttendance::updateOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'date' => $current->format('Y-m-d'),
                    ],
                    [
                        'status' => $status,
                        'marked_by' => auth()->id(),
                    ]
                );

                $current->addDay();
            }
        }

        return redirect()
            ->route('attendance.index', ['year' => $year, 'month' => $month])
            ->with('success', 'All employees marked as present for ' . Carbon::create($year, $month)->format('F Y'));
    }
}
