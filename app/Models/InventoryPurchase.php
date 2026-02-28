<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryPurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_item_id',
        'expense_id',
        'business_unit',
        'quantity',
        'unit_cost',
        'total_cost',
        'purchased_at',
        'vendor',
        'reference',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'purchased_at' => 'date',
    ];

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
