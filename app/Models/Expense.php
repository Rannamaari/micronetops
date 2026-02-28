<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Expense extends Model
{
    use HasFactory;

    public const UNIT_MOTO = 'moto';
    public const UNIT_AC = 'ac';
    public const UNIT_SHARED = 'shared';

    protected $fillable = [
        'expense_category_id',
        'vendor_id',
        'account_id',
        'business_unit',
        'amount',
        'incurred_at',
        'vendor',
        'reference',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'incurred_at' => 'date',
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
            self::UNIT_MOTO => 'Moto',
            self::UNIT_AC => 'AC',
            self::UNIT_SHARED => 'Shared',
        ];
    }
}
