<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FixedAssetEvent extends Model
{
    use HasFactory;

    public const TYPE_CREATED = 'created';
    public const TYPE_UPDATED = 'updated';
    public const TYPE_ASSIGNED = 'assigned';
    public const TYPE_RETURNED = 'returned';
    public const TYPE_STATUS_CHANGED = 'status_changed';
    public const TYPE_CONDITION_CHANGED = 'condition_changed';

    protected $fillable = [
        'fixed_asset_id',
        'event_type',
        'old_status',
        'new_status',
        'old_condition',
        'new_condition',
        'performed_by',
        'event_at',
        'notes',
    ];

    protected $casts = [
        'event_at' => 'datetime',
    ];

    public function asset()
    {
        return $this->belongsTo(FixedAsset::class, 'fixed_asset_id');
    }

    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
