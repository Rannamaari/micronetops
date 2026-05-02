<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FixedAssetCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
    ];

    public function assets()
    {
        return $this->hasMany(FixedAsset::class, 'fixed_asset_category_id');
    }
}
