<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lead extends Model
{
    use HasFactory;

    const LOST_REASONS = [
        'no_budget'       => 'No Budget',
        'too_expensive'   => 'Too Expensive',
        'competitor'      => 'Went with Competitor',
        'no_response'     => 'No Response / Ghosted',
        'bad_timing'      => 'Bad Timing',
        'not_interested'  => 'Not Interested',
        'duplicate'       => 'Duplicate Lead',
        'other'           => 'Other',
    ];

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'source',
        'status',
        'priority',
        'interested_in',
        'notes',
        'follow_up_date',
        'last_contact_at',
        'call_attempts',
        'created_by',
        'converted_to_customer_id',
        'converted_at',
        'lost_reason',
        'lost_reason_id',
        'lost_notes',
        'lost_at',
        'lost_by',
        'do_not_contact',
        'archived',
        'archived_at',
        'assigned_user_id',
    ];

    protected $casts = [
        'follow_up_date' => 'date',
        'last_contact_at' => 'datetime',
        'converted_at' => 'datetime',
        'do_not_contact' => 'boolean',
        'archived' => 'boolean',
        'archived_at' => 'datetime',
        'lost_at' => 'datetime',
    ];

    public function interactions()
    {
        return $this->hasMany(LeadInteraction::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function convertedToCustomer()
    {
        return $this->belongsTo(Customer::class, 'converted_to_customer_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function lostByUser()
    {
        return $this->belongsTo(User::class, 'lost_by');
    }

    public function convertToCustomer(): Customer
    {
        // Find existing customer by phone or create new one
        $customer = Customer::firstOrCreate(
            ['phone' => $this->phone],
            [
                'name' => $this->name,
                'email' => $this->email,
                'address' => $this->address,
                'category' => $this->interested_in,
                'notes' => $this->notes,
            ]
        );

        // Update lead to mark as converted
        $this->update([
            'status' => 'converted',
            'converted_to_customer_id' => $customer->id,
            'converted_at' => now(),
        ]);

        // Add interaction log
        $this->interactions()->create([
            'user_id' => auth()->id(),
            'type' => 'other',
            'notes' => 'Lead converted to customer #' . $customer->id,
        ]);

        return $customer;
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['new', 'contacted', 'interested', 'qualified'])
                      ->where('archived', false);
    }

    public function scopeArchived($query)
    {
        return $query->where('archived', true);
    }

    public function scopeNotArchived($query)
    {
        return $query->where('archived', false);
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_user_id', $userId);
    }

    public function scopeConverted($query)
    {
        return $query->where('status', 'converted');
    }

    public function scopeLost($query)
    {
        return $query->where('status', 'lost');
    }

    public function scopeByStatus($query, $status)
    {
        if ($status && $status !== 'all') {
            return $query->where('status', $status);
        }
        return $query;
    }

    public function scopeByPriority($query, $priority)
    {
        if ($priority && $priority !== 'all') {
            return $query->where('priority', $priority);
        }
        return $query;
    }

    public function scopeSearch($query, $search)
    {
        if ($search) {
            $s = mb_strtolower($search);
            return $query->where(function ($q) use ($s) {
                $q->whereRaw('lower(name) like ?', ["%{$s}%"])
                  ->orWhereRaw('lower(phone) like ?', ["%{$s}%"])
                  ->orWhereRaw('lower(email) like ?', ["%{$s}%"])
                  ->orWhereRaw('lower(address) like ?', ["%{$s}%"]);
            });
        }
        return $query;
    }

    public function getLeadScore(): int
    {
        $score = 0;

        // Do not contact or lost leads get 0 score
        if ($this->do_not_contact || $this->status === 'lost') {
            return 0;
        }

        // Priority scoring
        if ($this->priority === 'high') $score += 30;
        if ($this->priority === 'medium') $score += 20;
        if ($this->priority === 'low') $score += 10;

        // Status scoring
        if ($this->status === 'qualified') $score += 40;
        if ($this->status === 'interested') $score += 30;
        if ($this->status === 'contacted') $score += 20;
        if ($this->status === 'new') $score += 10;

        // Engagement scoring
        $interactionCount = $this->interactions()->count();
        $score += min($interactionCount * 5, 30);

        // Reduce score based on unsuccessful call attempts
        if ($this->call_attempts >= 3) {
            $score -= 20;
        } elseif ($this->call_attempts >= 2) {
            $score -= 10;
        }

        return max(0, min($score, 100));
    }

    public function markAsLost(string $reasonId, ?string $notes = null): void
    {
        $label = self::LOST_REASONS[$reasonId] ?? $reasonId;

        $this->update([
            'status' => 'lost',
            'lost_reason_id' => $reasonId,
            'lost_notes' => $notes,
            'lost_at' => now(),
            'lost_by' => auth()->id(),
            'lost_reason' => $label,
        ]);

        $logMessage = 'Lead marked as lost. Reason: ' . $label;
        if ($notes) {
            $logMessage .= ' — ' . $notes;
        }

        $this->interactions()->create([
            'user_id' => auth()->id(),
            'type' => 'other',
            'notes' => $logMessage,
        ]);
    }

    public function markAsDoNotContact(): void
    {
        $this->update([
            'do_not_contact' => true,
            'status' => 'lost',
            'lost_reason' => 'Not Interested',
            'lost_reason_id' => 'not_interested',
            'lost_at' => now(),
            'lost_by' => auth()->id(),
        ]);

        $this->interactions()->create([
            'user_id' => auth()->id(),
            'type' => 'other',
            'notes' => 'Customer requested not to be contacted',
        ]);
    }

    public function reopen(): void
    {
        $this->update([
            'status' => 'new',
            'lost_reason' => null,
            'lost_reason_id' => null,
            'lost_notes' => null,
            'lost_at' => null,
            'lost_by' => null,
            'do_not_contact' => false,
        ]);

        $this->interactions()->create([
            'user_id' => auth()->id(),
            'type' => 'other',
            'notes' => 'Lead reopened',
        ]);
    }

    public function getLostReasonLabelAttribute(): ?string
    {
        return self::LOST_REASONS[$this->lost_reason_id] ?? null;
    }

    public function shouldStopCalling(): bool
    {
        return $this->call_attempts >= 3 || $this->do_not_contact;
    }

    public function archive(): void
    {
        $this->update([
            'archived' => true,
            'archived_at' => now(),
        ]);

        $this->interactions()->create([
            'user_id' => auth()->id(),
            'type' => 'other',
            'notes' => 'Lead archived',
        ]);
    }

    public function unarchive(): void
    {
        $this->update([
            'archived' => false,
            'archived_at' => null,
        ]);

        $this->interactions()->create([
            'user_id' => auth()->id(),
            'type' => 'other',
            'notes' => 'Lead restored from archive',
        ]);
    }

    public function getFollowUpDisplayAttribute(): ?string
    {
        if (!$this->follow_up_date) {
            return null;
        }

        if ($this->status === 'converted' || $this->status === 'lost') {
            return $this->follow_up_date->format('M d, Y');
        }

        if ($this->follow_up_date->isToday()) {
            return 'Today';
        }

        $diff = now()->startOfDay()->diffInDays($this->follow_up_date->startOfDay(), false);

        if ($diff < 0) {
            return abs($diff) . 'd overdue';
        }

        return 'in ' . $diff . 'd';
    }

    public function getFollowUpIsOverdueAttribute(): bool
    {
        if (!$this->follow_up_date) {
            return false;
        }

        if ($this->status === 'converted' || $this->status === 'lost') {
            return false;
        }

        return $this->follow_up_date->isPast() && !$this->follow_up_date->isToday();
    }
}
