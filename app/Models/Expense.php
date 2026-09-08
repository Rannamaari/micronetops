<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Expense extends Model
{
    use HasFactory;

    public const UNIT_MOTO = 'moto';
    public const UNIT_AC = 'ac';
    public const UNIT_IT = 'it';
    public const UNIT_EASYFIX = 'easyfix';
    public const UNIT_SHARED = 'shared';

    protected $fillable = [
        'expense_category_id',
        'vendor_id',
        'account_id',
        'business_unit',
        'amount',
        'subtotal_amount',
        'is_gst_applicable',
        'gst_rate',
        'gst_amount',
        'gst_expenditure_type',
        'is_paid',
        'incurred_at',
        'due_date',
        'paid_at',
        'vendor',
        'reference',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'subtotal_amount' => 'decimal:2',
        'is_gst_applicable' => 'boolean',
        'gst_rate' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'is_paid' => 'boolean',
        'incurred_at' => 'date',
        'due_date' => 'date',
        'paid_at' => 'date',
    ];

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function vendorEntity()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function inventoryPurchases()
    {
        return $this->hasMany(InventoryPurchase::class);
    }

    public function pettyCashEntry()
    {
        return $this->hasOne(PettyCash::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function getBusinessUnits(): array
    {
        return [
            self::UNIT_MOTO => 'Micro Moto',
            self::UNIT_AC => 'Micro Cool',
            self::UNIT_IT => 'Micronet',
            self::UNIT_EASYFIX => 'Micronet - Easy Fix',
            self::UNIT_SHARED => 'Shared',
        ];
    }
}
