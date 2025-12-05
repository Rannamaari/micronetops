<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeBonus extends Model
{
    protected $fillable = [
        'employee_id',
        'bonus_type',
        'amount',
        'frequency',
        'awarded_date',
        'end_date',
        'reason',
        'notes',
        'status',
    ];

    protected $casts = [
        'awarded_date' => 'date',
        'end_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Scope to get active bonuses
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now());
            });
    }

    /**
     * Check if bonus is applicable for a given date
     */
    public function isApplicableFor($year, $month)
    {
        $date = \Carbon\Carbon::create($year, $month, 1);

        if ($this->status !== 'active') {
            return false;
        }

        // Check if bonus has started
        if ($this->awarded_date->isAfter($date->copy()->endOfMonth())) {
            return false;
        }

        // Check if bonus has ended
        if ($this->end_date && $this->end_date->isBefore($date->copy()->startOfMonth())) {
            return false;
        }

        // For one-time bonuses, only apply in the month they were awarded
        if ($this->frequency === 'one_time') {
            return $this->awarded_date->year === $year && $this->awarded_date->month === $month;
        }

        return true;
    }
}
