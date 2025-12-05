<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_number',
        'company',
        'name',
        'email',
        'phone',
        'secondary_phone',
        'contact_number_home',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'type',
        'position',
        'department',
        'hire_date',
        'status',
        'address',
        'permanent_address',
        'nationality',
        'date_of_birth',
        'id_number',
        'basic_salary',
        'basic_salary_usd',
        'job_description',
        'work_site',
        'work_status',
        'photo_path',
        'notes',
        // Compliance fields
        'work_permit_number',
        'date_of_arrival',
        'work_permit_fee_paid_until',
        'quota_slot_fee_paid_until',
        'passport_number',
        'passport_expiry_date',
        'visa_number',
        'visa_expiry_date',
        'quota_slot_number',
        'medical_checkup_expiry_date',
        'insurance_expiry_date',
        'insurance_number',
        'insurance_provider',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'date_of_birth' => 'date',
        'date_of_arrival' => 'date',
        'work_permit_fee_paid_until' => 'date',
        'quota_slot_fee_paid_until' => 'date',
        'passport_expiry_date' => 'date',
        'visa_expiry_date' => 'date',
        'medical_checkup_expiry_date' => 'date',
        'insurance_expiry_date' => 'date',
        'basic_salary' => 'decimal:2',
        'basic_salary_usd' => 'decimal:2',
    ];

    // Relationships
    public function documents()
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function salaries()
    {
        return $this->hasMany(EmployeeSalary::class);
    }

    public function loans()
    {
        return $this->hasMany(EmployeeLoan::class);
    }

    public function bonuses()
    {
        return $this->hasMany(EmployeeBonus::class);
    }

    public function activeBonuses()
    {
        return $this->hasMany(EmployeeBonus::class)->active();
    }

    public function leaves()
    {
        return $this->hasMany(EmployeeLeave::class);
    }

    public function allowances()
    {
        return $this->hasMany(EmployeeAllowance::class);
    }

    // Status Calculation Methods
    private function calculateStatus($expiryDate)
    {
        if (!$expiryDate) {
            return [
                'status' => 'Not Set',
                'days' => null,
                'class' => 'text-gray-500',
                'badge_class' => 'bg-gray-100 text-gray-800',
            ];
        }

        $now = Carbon::now()->startOfDay();
        $expiry = Carbon::parse($expiryDate)->startOfDay();
        $daysRemaining = $now->diffInDays($expiry, false);

        if ($daysRemaining < 0) {
            return [
                'status' => 'Expired',
                'days' => abs($daysRemaining),
                'class' => 'text-red-600 font-bold',
                'badge_class' => 'bg-red-100 text-red-800',
            ];
        } elseif ($daysRemaining <= 30) {
            return [
                'status' => $daysRemaining . ' days to renew',
                'days' => $daysRemaining,
                'class' => 'text-orange-600 font-semibold',
                'badge_class' => 'bg-orange-100 text-orange-800',
            ];
        } elseif ($daysRemaining <= 90) {
            return [
                'status' => $daysRemaining . ' days to renew',
                'days' => $daysRemaining,
                'class' => 'text-yellow-600',
                'badge_class' => 'bg-yellow-100 text-yellow-800',
            ];
        } else {
            return [
                'status' => $daysRemaining . ' days to renew',
                'days' => $daysRemaining,
                'class' => 'text-green-600',
                'badge_class' => 'bg-green-100 text-green-800',
            ];
        }
    }

    // Passport Status
    public function getPassportStatusAttribute()
    {
        return $this->calculateStatus($this->passport_expiry_date);
    }

    // Work Permit Status
    public function getWorkPermitStatusAttribute()
    {
        return $this->calculateStatus($this->work_permit_fee_paid_until);
    }

    // Visa Status
    public function getVisaStatusAttribute()
    {
        return $this->calculateStatus($this->visa_expiry_date);
    }

    // Quota Slot Status
    public function getQuotaSlotStatusAttribute()
    {
        return $this->calculateStatus($this->quota_slot_fee_paid_until);
    }

    // Medical Status
    public function getMedicalStatusAttribute()
    {
        return $this->calculateStatus($this->medical_checkup_expiry_date);
    }

    // Insurance Status
    public function getInsuranceStatusAttribute()
    {
        return $this->calculateStatus($this->insurance_expiry_date);
    }

    // Get all compliance statuses at once
    public function getComplianceStatusAttribute()
    {
        return [
            'passport' => $this->passport_status,
            'work_permit' => $this->work_permit_status,
            'visa' => $this->visa_status,
            'quota_slot' => $this->quota_slot_status,
            'medical' => $this->medical_status,
            'insurance' => $this->insurance_status,
        ];
    }

    // Check if any compliance item is expired or expiring soon
    public function hasExpiringDocumentsAttribute()
    {
        $statuses = $this->compliance_status;
        foreach ($statuses as $status) {
            if ($status['status'] === 'Expired' || ($status['days'] !== null && $status['days'] <= 30)) {
                return true;
            }
        }
        return false;
    }

    // Years of Service Calculation
    public function getYearsOfServiceAttribute()
    {
        if (!$this->hire_date) {
            return 0;
        }
        return Carbon::parse($this->hire_date)->diffInYears(Carbon::now());
    }

    public function getMonthsOfServiceAttribute()
    {
        if (!$this->hire_date) {
            return 0;
        }
        return Carbon::parse($this->hire_date)->diffInMonths(Carbon::now());
    }

    public function getServiceDurationAttribute()
    {
        if (!$this->hire_date) {
            return 'Not set';
        }

        $years = $this->years_of_service;
        $months = $this->months_of_service % 12;

        if ($years === 0 && $months === 0) {
            return 'Less than 1 month';
        } elseif ($years === 0) {
            return $months . ' month' . ($months > 1 ? 's' : '');
        } elseif ($months === 0) {
            return $years . ' year' . ($years > 1 ? 's' : '');
        } else {
            return $years . ' year' . ($years > 1 ? 's' : '') . ', ' . $months . ' month' . ($months > 1 ? 's' : '');
        }
    }

    // Leave Accrual System
    // 2.5 days per month worked + 10 sick days per year
    // Annual leaves: forwarded for 1 year, voided after 2 years
    // Sick leaves: NOT forwarded, reset each year

    public function getAnnualLeaveAccruedThisYearAttribute()
    {
        // Calculate leave accrual based on months worked in current year
        $startOfYear = Carbon::now()->startOfYear();
        $hireDate = Carbon::parse($this->hire_date);

        // If hired this year, calculate from hire date
        if ($hireDate->year === Carbon::now()->year) {
            $monthsThisYear = $hireDate->diffInMonths(Carbon::now());
        } else {
            // If hired before this year, full year accrual
            $monthsThisYear = Carbon::now()->month;
        }

        return round($monthsThisYear * 2.5, 1);
    }

    public function getAnnualLeaveUsedThisYearAttribute()
    {
        return $this->leaves()
            ->where('leave_type', 'annual')
            ->where('status', 'approved')
            ->whereYear('start_date', Carbon::now()->year)
            ->sum('days') ?? 0;
    }

    public function getAnnualLeaveAccruedLastYearAttribute()
    {
        // Calculate what was accrued last year
        $lastYear = Carbon::now()->subYear()->year;
        $hireDate = Carbon::parse($this->hire_date);

        // If hired last year, calculate from hire date to end of year
        if ($hireDate->year === $lastYear) {
            $monthsLastYear = $hireDate->diffInMonths(Carbon::create($lastYear, 12, 31));
        } elseif ($hireDate->year < $lastYear) {
            // If hired before last year, full year accrual
            $monthsLastYear = 12;
        } else {
            // Hired this year, no accrual from last year
            return 0;
        }

        return round($monthsLastYear * 2.5, 1);
    }

    public function getAnnualLeaveUsedLastYearAttribute()
    {
        return $this->leaves()
            ->where('leave_type', 'annual')
            ->where('status', 'approved')
            ->whereYear('start_date', Carbon::now()->subYear()->year)
            ->sum('days') ?? 0;
    }

    public function getAnnualLeaveForwardedFromLastYearAttribute()
    {
        // Forwarded = Accrued last year - Used last year
        // But can only be used within 1 year (voided after 2 years)
        return max(0, $this->annual_leave_accrued_last_year - $this->annual_leave_used_last_year);
    }

    public function getTotalAnnualLeaveAvailableAttribute()
    {
        // Total = This year's accrual + Forwarded from last year
        return $this->annual_leave_accrued_this_year + $this->annual_leave_forwarded_from_last_year;
    }

    public function getRemainingAnnualLeaveAttribute()
    {
        // Remaining = Total available - Used this year
        return max(0, $this->total_annual_leave_available - $this->annual_leave_used_this_year);
    }

    public function getAnnualSickLeaveAttribute()
    {
        // 10 sick days per year for all employees (NOT forwarded)
        return 10;
    }

    public function getUsedSickLeaveAttribute()
    {
        return $this->leaves()
            ->where('leave_type', 'sick')
            ->where('status', 'approved')
            ->whereYear('start_date', Carbon::now()->year)
            ->sum('days') ?? 0;
    }

    public function getRemainingSickLeaveAttribute()
    {
        return max(0, $this->annual_sick_leave - $this->used_sick_leave);
    }

    public function getLeaveBalanceAttribute()
    {
        return [
            'annual' => [
                'accrued_this_year' => $this->annual_leave_accrued_this_year,
                'forwarded_from_last_year' => $this->annual_leave_forwarded_from_last_year,
                'total_available' => $this->total_annual_leave_available,
                'used' => $this->annual_leave_used_this_year,
                'remaining' => $this->remaining_annual_leave,
            ],
            'sick' => [
                'total' => $this->annual_sick_leave,
                'used' => $this->used_sick_leave,
                'remaining' => $this->remaining_sick_leave,
            ],
        ];
    }

    // Active loans
    public function activeLoans()
    {
        return $this->loans()->where('status', 'active');
    }

    // Total active loan balance
    public function getTotalLoanBalanceAttribute()
    {
        return $this->activeLoans()->sum('remaining_balance');
    }

    // Active allowances
    public function activeAllowances()
    {
        return $this->allowances()->where('is_active', true);
    }

    // Total monthly allowances
    public function getTotalMonthlyAllowancesAttribute()
    {
        return $this->activeAllowances()
            ->where('frequency', 'monthly')
            ->sum('amount');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeFullTime($query)
    {
        return $query->where('type', 'full-time');
    }

    public function scopePartTime($query)
    {
        return $query->where('type', 'part-time');
    }

    public function scopeContract($query)
    {
        return $query->where('type', 'contract');
    }

    public function scopeExpiringDocuments($query, $days = 30)
    {
        $date = Carbon::now()->addDays($days);
        return $query->where(function ($q) use ($date) {
            $q->where('passport_expiry_date', '<=', $date)
                ->orWhere('work_permit_fee_paid_until', '<=', $date)
                ->orWhere('visa_expiry_date', '<=', $date)
                ->orWhere('quota_slot_fee_paid_until', '<=', $date)
                ->orWhere('medical_checkup_expiry_date', '<=', $date)
                ->orWhere('insurance_expiry_date', '<=', $date);
        });
    }
}
