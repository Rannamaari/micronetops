<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'brand',
        'model',
        'registration_number',
        'year',
        'mileage',
        'road_worthiness_created_at',
        'road_worthiness_expires_at',
    ];

    protected $casts = [
        'road_worthiness_created_at' => 'datetime',
        'road_worthiness_expires_at'  => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function jobs()
    {
        return $this->hasMany(Job::class);
    }

    public function roadWorthinessHistory()
    {
        return $this->hasMany(RoadWorthinessHistory::class);
    }

    public function isRoadWorthinessExpired(): bool
    {
        if (!$this->road_worthiness_expires_at) {
            return false;
        }

        return $this->road_worthiness_expires_at->isPast();
    }

    public function daysUntilExpiry(): ?int
    {
        if (!$this->road_worthiness_expires_at) {
            return null;
        }

        return now()->diffInDays($this->road_worthiness_expires_at, false);
    }

    public function roadWorthinessStatus(): string
    {
        if (!$this->road_worthiness_expires_at) {
            return 'none';
        }

        if ($this->isRoadWorthinessExpired()) {
            return 'expired';
        }

        $daysUntilExpiry = $this->daysUntilExpiry();

        if ($daysUntilExpiry <= 30) {
            return 'expiring_soon';
        }

        return 'valid';
    }
}
