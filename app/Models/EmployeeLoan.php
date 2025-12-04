<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeLoan extends Model
{
    protected $fillable = [
        'employee_id',
        'loan_type',
        'amount',
        'remaining_balance',
        'monthly_deduction',
        'loan_date',
        'start_deduction_date',
        'status',
        'approved_by',
        'approved_date',
        'reason',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
        'monthly_deduction' => 'decimal:2',
        'loan_date' => 'date',
        'start_deduction_date' => 'date',
        'approved_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Deduct from loan
    public function deduct($amount)
    {
        $this->remaining_balance -= $amount;
        if ($this->remaining_balance <= 0) {
            $this->remaining_balance = 0;
            $this->status = 'completed';
        }
        $this->save();
    }
}
