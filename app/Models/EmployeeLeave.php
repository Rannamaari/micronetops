<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeLeave extends Model
{
    protected $fillable = [
        'employee_id',
        'leave_type',
        'start_date',
        'end_date',
        'days',
        'reason',
        'attachment_path',
        'status',
        'approved_by',
        'approved_date',
        'rejection_reason',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
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
}
