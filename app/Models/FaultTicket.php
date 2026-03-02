<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FaultTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'business_unit',
        'priority',
        'status',
        'customer_id',
        'customer_name',
        'customer_phone',
        'job_id',
        'title',
        'description',
        'resolution_notes',
        'resolved_at',
        'resolved_by',
        'deadline_at',
        'created_by',
        'assigned_to',
    ];

    protected $casts = [
        'deadline_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::created(function (FaultTicket $ticket) {
            $ticket->update([
                'ticket_number' => 'FT-' . str_pad($ticket->id, 5, '0', STR_PAD_LEFT),
            ]);
        });
    }

    // Relationships

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    // Scopes

    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['open', 'in_progress']);
    }

    public function scopeOverdue($query)
    {
        return $query->whereNotIn('status', ['resolved', 'closed'])
            ->where('deadline_at', '<', Carbon::now());
    }

    public function scopeForUnit($query, $unit)
    {
        return $query->where('business_unit', $unit);
    }

    // Helpers

    public function isOverdue(): bool
    {
        return !in_array($this->status, ['resolved', 'closed'])
            && $this->deadline_at
            && Carbon::now()->gt($this->deadline_at);
    }

    public function getResolutionHours(): ?float
    {
        if (!$this->resolved_at) {
            return null;
        }

        return round($this->created_at->diffInMinutes($this->resolved_at) / 60, 1);
    }

    public function metSla(): ?bool
    {
        if (!$this->resolved_at || !$this->deadline_at) {
            return null;
        }

        return $this->resolved_at->lte($this->deadline_at);
    }
}
