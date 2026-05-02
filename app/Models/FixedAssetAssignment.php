<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FixedAssetAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'fixed_asset_id',
        'staff_id',
        'assigned_by',
        'assigned_at',
        'returned_at',
        'condition_on_assign',
        'condition_on_return',
        'notes',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'returned_at' => 'datetime',
    ];

    public function asset()
    {
        return $this->belongsTo(FixedAsset::class, 'fixed_asset_id');
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function scopeOpen($query)
    {
        return $query->whereNull('returned_at');
    }
}
