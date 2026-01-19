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
            default => 'indigo',
        };
    }
}
