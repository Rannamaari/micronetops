<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JobItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'inventory_item_id',
        'item_name',
        'item_description',
        'is_service',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    protected $casts = [
        'is_service' => 'boolean',
        'unit_price' => 'decimal:2',
        'subtotal'   => 'decimal:2',
    ];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }
}
