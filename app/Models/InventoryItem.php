<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'category', // moto / ac / both (kept for backward compatibility)
        'inventory_category_id', // FK to inventory_categories
        'name',
        'sku',
        'brand',
        'unit',
        'quantity',
        'cost_price',
        'sell_price',
        'low_stock_limit',
        'is_active',
        'is_service',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'is_service' => 'boolean',
    ];

    public function inventoryCategory()
    {
        return $this->belongsTo(InventoryCategory::class);
    }

    public function jobItems()
    {
        return $this->hasMany(JobItem::class);
    }

    public function logs()
    {
        return $this->hasMany(InventoryLog::class);
    }

    /**
     * Scope for active items
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for service items
     */
    public function scopeServices($query)
    {
        return $query->where('is_service', true);
    }

    /**
     * Scope for parts (non-service)
     */
    public function scopeParts($query)
    {
        return $query->where('is_service', false);
    }

    /**
     * Scope for filtering by category type (moto/ac/both)
     */
    public function scopeOfCategory($query, $category)
    {
        if ($category === 'both') {
            return $query->whereIn('category', ['moto', 'ac', 'both']);
        }
        return $query->where('category', $category);
    }

    /**
     * Check if item is low on stock
     */
    public function isLowStock(): bool
    {
        if ($this->is_service) {
            return false;
        }
        return $this->quantity <= $this->low_stock_limit;
    }
}
