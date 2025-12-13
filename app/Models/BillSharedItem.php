<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillSharedItem extends Model
{
    protected $fillable = [
        'bill_id',
        'name',
        'price',
        'is_from_ocr',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_from_ocr' => 'boolean',
    ];

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }
}
