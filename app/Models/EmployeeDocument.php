<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeDocument extends Model
{
    protected $fillable = [
        'employee_id',
        'document_type',
        'document_number',
        'issue_date',
        'expiry_date',
        'attachment_path',
        'send_alert',
        'alert_days_before',
        'notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'send_alert' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
