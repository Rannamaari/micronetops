<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RoadWorthinessHistory extends Model
{
    use HasFactory;

    protected $table = 'road_worthiness_history';

    protected $fillable = [
        'vehicle_id',
        'job_id',
        'issued_at',
        'expires_at',
    ];

    protected $casts = [
        'issued_at'  => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function job()
    {
        return $this->belongsTo(Job::class);
    }
}
