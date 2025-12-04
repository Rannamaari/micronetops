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
        'name',
        'email',
        'phone',
        'secondary_phone',
        'emergency_contact_name',
        'emergency_contact_phone',
        'type',
        'position',
        'department',
        'hire_date',
        'status',
        'address',
        'nationality',
        'date_of_birth',
        'id_number',
        'basic_salary',
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
