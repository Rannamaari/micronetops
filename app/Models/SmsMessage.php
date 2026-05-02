<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SmsMessage extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_SENDING = 'sending';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'user_id',
        'audience',
        'status',
        'content',
        'scheduled_for',
        'source',
        'destinations',
        'destinations_count',
        'invalid_destinations',
        'invalid_count',
        'responses',
        'sent_count',
        'failed_count',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'destinations' => 'array',
        'invalid_destinations' => 'array',
        'responses' => 'array',
        'scheduled_for' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function isScheduled(): bool
    {
        return $this->status === self::STATUS_SCHEDULED;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
