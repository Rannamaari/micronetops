<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AcUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'brand',
        'btu',
        'gas_type',
        'indoor_units',
        'outdoor_units',
        'last_service_at',
        'location_description',
    ];

    protected $casts = [
        'last_service_at' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function jobs()
    {
        return $this->hasMany(Job::class);
    }
}
