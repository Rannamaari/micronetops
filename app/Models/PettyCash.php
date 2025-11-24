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

    /** Compute current balance (helper) */
    public static function currentBalance(): float
    {
        $topups = static::approved()->where('type', 'topup')->sum('amount');
        $expenses = static::approved()->where('type', 'expense')->sum('amount');

        return (float) $topups - (float) $expenses;
    }
}
