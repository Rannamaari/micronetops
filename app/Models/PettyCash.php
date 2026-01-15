<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PettyCash extends Model
{
    use HasFactory;

    protected $table = 'petty_cash';

    protected $fillable = [
        'user_id',
        'assigned_to',     // which user this petty cash belongs to
        'amount',
        'purpose',
        'category',
        'type',            // topup / expense / adjustment
        'attachment_path',
        'approved_by',
        'status',          // pending / approved / rejected
        'paid_at',
        'source_payment_id', // link to payment if created from cash payment
    ];

    protected $casts = [
        'amount'   => 'decimal:2',
        'paid_at'  => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function sourcePayment()
    {
        return $this->belongsTo(Payment::class, 'source_payment_id');
    }

    /** Scope for approved only */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /** Compute current balance (helper) - centralized/global balance */
    public static function currentBalance(): float
    {
        $topups = static::approved()->where('type', 'topup')->sum('amount');
        $expenses = static::approved()->where('type', 'expense')->sum('amount');

        return (float) $topups - (float) $expenses;
    }

    /** Compute balance for a specific user */
    public static function userBalance(User $user): float
    {
        $topups = static::approved()
            ->where('type', 'topup')
            ->where('assigned_to', $user->id)
            ->sum('amount');

        $expenses = static::approved()
            ->where('type', 'expense')
            ->where('assigned_to', $user->id)
            ->sum('amount');

        return (float) $topups - (float) $expenses;
    }

    /** Get all users with their balances (for admin dashboard) */
    public static function allUserBalances()
    {
        $users = User::whereIn('role', ['admin', 'manager', 'mechanic', 'cashier', 'hr'])
            ->orderBy('name')
            ->get();

        return $users->map(function ($user) {
            return [
                'user' => $user,
                'balance' => static::userBalance($user),
                'total_topups' => static::approved()
                    ->where('type', 'topup')
                    ->where('assigned_to', $user->id)
                    ->sum('amount'),
                'total_expenses' => static::approved()
                    ->where('type', 'expense')
                    ->where('assigned_to', $user->id)
                    ->sum('amount'),
                'pending_expenses' => static::where('status', 'pending')
                    ->where('type', 'expense')
                    ->where('assigned_to', $user->id)
                    ->sum('amount'),
            ];
        });
    }
}
