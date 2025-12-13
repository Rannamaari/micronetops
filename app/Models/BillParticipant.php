<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillParticipant extends Model
{
    protected $fillable = [
        'bill_id',
        'name',
        'total_amount',
        'personal_items',
        'shared_items',
        'service_charge',
        'gst',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'personal_items' => 'decimal:2',
        'shared_items' => 'decimal:2',
        'service_charge' => 'decimal:2',
        'gst' => 'decimal:2',
    ];

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }
}
