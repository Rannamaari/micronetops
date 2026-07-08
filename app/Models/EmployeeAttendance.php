<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class EmployeeAttendance extends Model
{
    protected $table = 'employee_attendance';

    protected $fillable = [
        'employee_id',
        'date',
        'status',
        'absence_reason',
        'leave_id',
        'notes',
        'marked_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function markedBy()
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    public function leave()
    {
        return $this->belongsTo(EmployeeLeave::class, 'leave_id');
    }

    /**
     * Check if attendance is marked for a specific month
     */
    public static function isMarkedForMonth(int $employeeId, int $year, int $month): bool
    {
        $employee = \App\Models\Employee::find($employeeId);
        if (!$employee) {
            return false;
        }

        $period = $employee->getEmploymentPeriodForMonth($year, $month);
        if (!$period) {
            return true;
        }

        // Count how many days have attendance records
        $markedDays = self::where('employee_id', $employeeId)
            ->whereBetween('date', [$period['pay_period_start'], $period['pay_period_end']])
            ->count();

        // Calculate expected working days (excluding Fridays) within the employment period
        $expectedDays = self::getWorkingDaysBetween($period['pay_period_start'], $period['pay_period_end']);

        return $markedDays >= $expectedDays;
    }

    /**
     * Get working days in a month (excluding Fridays)
     */
    public static function getWorkingDaysInMonth(int $year, int $month): int
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        return self::getWorkingDaysBetween($startDate, $endDate);
    }

    /**
     * Get working days between two dates (excluding Fridays)
     */
    public static function getWorkingDaysBetween(Carbon $startDate, Carbon $endDate): int
    {
        $workingDays = 0;
        $current = $startDate->copy();

        while ($current <= $endDate) {
            // Friday is day 5 in Carbon (0=Sunday, 5=Friday)
            if ($current->dayOfWeek !== Carbon::FRIDAY) {
                $workingDays++;
            }
            $current->addDay();
        }

        return $workingDays;
    }

    /**
     * Calculate absent days for an employee in a month (UNPAID only)
     */
    public static function getAbsentDays(int $employeeId, int $year, int $month): int
    {
        $employee = \App\Models\Employee::find($employeeId);
        if (!$employee) {
            return 0;
        }

        $period = $employee->getEmploymentPeriodForMonth($year, $month);
        if (!$period) {
            return 0;
        }

        // Count UNPAID absent days only (exclude sick leave which is paid)
        // Only count inside the actual employment period for the payroll month.
        return self::where('employee_id', $employeeId)
            ->whereBetween('date', [$period['pay_period_start'], $period['pay_period_end']])
            ->where('status', 'absent')
            ->where(function($query) {
                $query->whereNull('absence_reason')
                      ->orWhere('absence_reason', 'unpaid_leave');
            })
            ->count();
    }
}
