<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RecurringExpense extends Model
{
    use HasFactory;

    public const FREQ_WEEKLY = 'weekly';
    public const FREQ_MONTHLY = 'monthly';

    protected $fillable = [
        'name',
        'expense_category_id',
        'vendor_id',
        'vendor_name',
        'vendor_phone',
        'vendor_contact_name',
        'vendor_address',
        'business_unit',
        'amount',
        'frequency',
        'day_of_week',
        'day_of_month',
        'next_due_at',
        'last_generated_at',
        'reference',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'next_due_at' => 'date',
        'last_generated_at' => 'date',
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public static function getFrequencies(): array
    {
        return [
            self::FREQ_WEEKLY => 'Weekly',
            self::FREQ_MONTHLY => 'Monthly',
        ];
    }
}
