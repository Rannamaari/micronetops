<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'amount',
        'method',
        'reference',
        'status',
        'attachment_path',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }
}
