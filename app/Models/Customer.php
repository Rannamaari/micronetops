<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'category',
        'notes',
    ];

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

    public function acUnits()
    {
        return $this->hasMany(AcUnit::class);
    }

    public function jobs()
    {
        return $this->hasMany(Job::class);
    }

    public function hasExpiredRoadWorthiness(): bool
    {
        return $this->vehicles()
            ->whereNotNull('road_worthiness_expires_at')
            ->where('road_worthiness_expires_at', '<', now())
            ->exists();
    }

    public function vehiclesWithExpiredRoadWorthiness()
    {
        return $this->vehicles()
            ->whereNotNull('road_worthiness_expires_at')
            ->where('road_worthiness_expires_at', '<', now())
            ->get();
    }
}
