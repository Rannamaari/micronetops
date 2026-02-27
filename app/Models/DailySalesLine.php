<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DailySalesLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'daily_sales_log_id',
        'inventory_item_id',
        'description',
        'qty',
        'unit_price',
        'payment_method',
        'line_total',
        'is_stock_item',
        'note',
        'is_gst_applicable',
        'gst_amount',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'line_total' => 'decimal:2',
        'is_stock_item' => 'boolean',
        'is_gst_applicable' => 'boolean',
        'gst_amount' => 'decimal:2',
    ];

    public function log()
    {
        return $this->belongsTo(DailySalesLog::class, 'daily_sales_log_id');
    }

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }
}
