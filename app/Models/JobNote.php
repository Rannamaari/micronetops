<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JobNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'user_id',
        'type',
        'content',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    // Note types
    public const TYPE_NOTE = 'note';
    public const TYPE_STATUS_CHANGE = 'status_change';
    public const TYPE_ASSIGNMENT = 'assignment';
    public const TYPE_SYSTEM = 'system';
    public const TYPE_CUSTOMER_CONTACT = 'customer_contact';
    public const TYPE_ACCEPTED = 'accepted';
    public const TYPE_INVOICED = 'invoiced';
    public const TYPE_FAULT = 'fault';
    public const TYPE_SIGNED_OFF = 'signed_off';

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get icon for note type.
     */
    public function getIconAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_STATUS_CHANGE => 'arrow-path',
            self::TYPE_ASSIGNMENT => 'user-plus',
            self::TYPE_SYSTEM => 'cog',
            self::TYPE_CUSTOMER_CONTACT => 'phone',
            self::TYPE_ACCEPTED => 'check',
            self::TYPE_INVOICED => 'document-text',
            self::TYPE_FAULT => 'exclamation-triangle',
            self::TYPE_SIGNED_OFF => 'check-badge',
            default => 'chat-bubble-left',
        };
    }

    /**
     * Get color for note type.
     */
    public function getColorAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_STATUS_CHANGE => 'blue',
            self::TYPE_ASSIGNMENT => 'green',
            self::TYPE_SYSTEM => 'gray',
            self::TYPE_CUSTOMER_CONTACT => 'amber',
            self::TYPE_ACCEPTED => 'green',
            self::TYPE_INVOICED => 'blue',
            self::TYPE_FAULT => 'red',
            self::TYPE_SIGNED_OFF => 'green',
            default => 'indigo',
        };
    }
}
