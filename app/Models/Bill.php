<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bill extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'restaurant_name',
        'service_charge_percentage',
        'gst_enabled',
        'gst_on_service',
        'subtotal',
        'service_charge_amount',
        'gst_amount',
        'grand_total',
        'currency',
        'ocr_extracted_text',
        'calculation_metadata',
    ];

    protected $casts = [
        'gst_enabled' => 'boolean',
        'gst_on_service' => 'boolean',
        'service_charge_percentage' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'service_charge_amount' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'calculation_metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(BillParticipant::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BillItem::class);
    }

    public function sharedItems(): HasMany
    {
        return $this->hasMany(BillSharedItem::class);
    }
}
