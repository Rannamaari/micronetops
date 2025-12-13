<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillItem extends Model
{
    protected $fillable = [
        'bill_id',
        'name',
        'price',
        'assigned_to',
        'is_from_ocr',
        'ocr_confidence',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'assigned_to' => 'array',
        'is_from_ocr' => 'boolean',
        'ocr_confidence' => 'decimal:2',
    ];

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }
}
