<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FixedAsset extends Model
{
    use HasFactory;

    public const CONDITION_GOOD = 'Good';
    public const CONDITION_NEEDS_REPAIR = 'Needs Repair';
    public const CONDITION_DAMAGED = 'Damaged';

    public const STATUS_AVAILABLE = 'Available';
    public const STATUS_ASSIGNED = 'Assigned';
    public const STATUS_UNDER_REPAIR = 'Under Repair';
    public const STATUS_RETIRED = 'Retired';
    public const STATUS_LOST = 'Lost';
    public const STATUS_SOLD = 'Sold';

    protected $fillable = [
        'asset_code',
        'name',
        'fixed_asset_category_id',
        'fixed_asset_brand_id',
        'category',
        'brand',
        'model',
        'serial_number',
        'photo_path',
        'condition',
        'status',
        'notes',
    ];

    public static function conditionOptions(): array
    {
        return [
            self::CONDITION_GOOD,
            self::CONDITION_NEEDS_REPAIR,
            self::CONDITION_DAMAGED,
        ];
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_AVAILABLE,
            self::STATUS_ASSIGNED,
            self::STATUS_UNDER_REPAIR,
            self::STATUS_RETIRED,
            self::STATUS_LOST,
            self::STATUS_SOLD,
        ];
    }

    public function assignments()
    {
        return $this->hasMany(FixedAssetAssignment::class)->orderByDesc('assigned_at');
    }

    public function categoryEntity()
    {
        return $this->belongsTo(FixedAssetCategory::class, 'fixed_asset_category_id');
    }

    public function brandEntity()
    {
        return $this->belongsTo(FixedAssetBrand::class, 'fixed_asset_brand_id');
    }

    public function currentAssignment()
    {
        return $this->hasOne(FixedAssetAssignment::class)
            ->whereNull('returned_at')
            ->latestOfMany('assigned_at');
    }

    public function events()
    {
        return $this->hasMany(FixedAssetEvent::class)->orderByDesc('event_at');
    }
}
