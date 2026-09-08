<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GstSetting extends Model
{
    protected $fillable = [
        'taxpayer_tin',
        'activity_number_moto',
        'activity_number_cool',
        'activity_number_it',
        'activity_number_easyfix',
        'activity_number_shared',
        'updated_by',
    ];

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
