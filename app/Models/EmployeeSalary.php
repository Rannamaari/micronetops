<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeSalary extends Model
{
    protected $fillable = [
        'employee_id',
        'month',
        'year',
        'basic_salary',
        'allowances',
        'bonuses',
        'overtime',
        'loan_deduction',
        'other_deductions',
        'gross_salary',
        'total_deductions',
        'net_salary',
        'status',
        'payment_date',
        'payment_method',
        'reference',
        'notes',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'allowances' => 'decimal:2',
        'bonuses' => 'decimal:2',
        'overtime' => 'decimal:2',
        'loan_deduction' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // Calculate totals automatically
    public function calculateTotals()
    {
        $this->gross_salary = $this->basic_salary + $this->allowances + $this->bonuses + $this->overtime;
        $this->total_deductions = $this->loan_deduction + $this->other_deductions;
        $this->net_salary = $this->gross_salary - $this->total_deductions;
        return $this;
    }
}
