<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lead extends Model
{
    use HasFactory;

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
        'do_not_contact',
    ];

    protected $casts = [
        'follow_up_date' => 'date',
        'last_contact_at' => 'datetime',
        'converted_at' => 'datetime',
        'do_not_contact' => 'boolean',
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

    public function convertToCustomer(): Customer
    {
        // Create a new customer from lead data
        $customer = Customer::create([
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'category' => $this->interested_in,
            'notes' => $this->notes,
        ]);

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
        return $query->whereIn('status', ['new', 'contacted', 'interested', 'qualified']);
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
            return $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('address', 'like', '%' . $search . '%');
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

    public function markAsLost(string $reason): void
    {
        $this->update([
            'status' => 'lost',
            'lost_reason' => $reason,
        ]);

        // Add interaction log
        $this->interactions()->create([
            'user_id' => auth()->id(),
            'type' => 'other',
            'notes' => 'Lead marked as lost. Reason: ' . $reason,
        ]);
    }

    public function markAsDoNotContact(): void
    {
        $this->update([
            'do_not_contact' => true,
            'status' => 'lost',
            'lost_reason' => 'Customer requested not to be contacted',
        ]);

        // Add interaction log
        $this->interactions()->create([
            'user_id' => auth()->id(),
            'type' => 'other',
            'notes' => 'Customer requested not to be contacted',
        ]);
    }

    public function shouldStopCalling(): bool
    {
        return $this->call_attempts >= 3 || $this->do_not_contact;
    }
}
