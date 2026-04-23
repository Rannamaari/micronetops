<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SmsMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'audience',
        'content',
        'source',
        'destinations',
        'destinations_count',
        'invalid_destinations',
        'invalid_count',
        'responses',
        'sent_count',
        'failed_count',
        'sent_at',
    ];

    protected $casts = [
        'destinations' => 'array',
        'invalid_destinations' => 'array',
        'responses' => 'array',
        'sent_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

