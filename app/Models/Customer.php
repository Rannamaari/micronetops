<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory;

    /**
     * Name used when a job is logged before we know the customer's name.
     */
    public static function placeholderName(?string $phone): string
    {
        return 'Customer ' . ($phone ?: 'unknown');
    }

    /**
     * True for "Unknown", empty, or a "Customer <number>" placeholder.
     */
    public static function isPlaceholderName(?string $name): bool
    {
        $name = trim((string) $name);

        return $name === '' || $name === 'Unknown' || (bool) preg_match('/^Customer [0-9]+$|^Customer unknown$/', $name);
    }

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'gst_number',
        'category',
        'notes',
        'easyfix_user_id',
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

    public function addresses()
    {
        return $this->hasMany(CustomerAddress::class)->orderByDesc('is_default')->orderBy('label');
    }

    public function defaultAddress()
    {
        return $this->hasOne(CustomerAddress::class)->where('is_default', true);
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
