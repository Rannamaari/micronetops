<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class EodReconciliation extends Model
{
    protected $fillable = [
        'date',
        'business_unit',
        'status',
        'expected_cash',
        'expected_transfer',
        'note_500',
        'note_100',
        'note_50',
        'note_20',
        'note_10',
        'coin_2',
        'coin_1',
        'counted_cash',
        'variance',
        'notes',
        'closed_by',
        'closed_at',
        'deposited_by',
        'deposited_at',
        'deposited_account_id',
    ];

    protected $casts = [
        'date' => 'date',
        'closed_at' => 'datetime',
        'deposited_at' => 'datetime',
        'expected_cash' => 'decimal:2',
        'expected_transfer' => 'decimal:2',
        'counted_cash' => 'decimal:2',
        'variance' => 'decimal:2',
    ];

    public function setDateAttribute($value)
    {
        $this->attributes['date'] = \Carbon\Carbon::parse($value)->format('Y-m-d');
    }

    // Relationships

    public function closedByUser()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function depositedByUser()
    {
        return $this->belongsTo(User::class, 'deposited_by');
    }

    public function depositedAccount()
    {
        return $this->belongsTo(Account::class, 'deposited_account_id');
    }

    // Scopes

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    public function scopeForUnit($query, $unit)
    {
        return $query->where('business_unit', $unit);
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    // Status helpers

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function isDeposited(): bool
    {
        return $this->status === 'deposited';
    }

    // Computed attribute: sum denominations

    public function getCountedCashFromDenominations(): float
    {
        return (($this->note_500 ?? 0) * 500)
             + (($this->note_100 ?? 0) * 100)
             + (($this->note_50 ?? 0) * 50)
             + (($this->note_20 ?? 0) * 20)
             + (($this->note_10 ?? 0) * 10)
             + (($this->coin_2 ?? 0) * 2)
             + (($this->coin_1 ?? 0) * 1);
    }

    // Calculate expected totals from submitted sales

    public function calculateExpected(): void
    {
        $sales = DailySalesLog::forDate($this->date)
            ->forUnit($this->business_unit)
            ->submitted()
            ->with('lines')
            ->get();

        $cashTotal = 0;
        $transferTotal = 0;

        foreach ($sales as $sale) {
            $grand = $sale->totals['grand'];
            if ($sale->payment_method === 'cash') {
                $cashTotal += $grand;
            } else {
                $transferTotal += $grand;
            }
        }

        $this->expected_cash = $cashTotal;
        $this->expected_transfer = $transferTotal;
        $this->save();
    }

    // Close EOD with denomination counts

    public function close(array $denominations, ?string $notes): void
    {
        $this->note_500 = $denominations['note_500'] ?? null;
        $this->note_100 = $denominations['note_100'] ?? null;
        $this->note_50 = $denominations['note_50'] ?? null;
        $this->note_20 = $denominations['note_20'] ?? null;
        $this->note_10 = $denominations['note_10'] ?? null;
        $this->coin_2 = $denominations['coin_2'] ?? null;
        $this->coin_1 = $denominations['coin_1'] ?? null;

        $this->calculateExpected();

        $this->counted_cash = $this->getCountedCashFromDenominations();
        $this->variance = $this->counted_cash - $this->expected_cash;
        $this->notes = $notes;
        $this->status = 'closed';
        $this->closed_by = Auth::id();
        $this->closed_at = now();
        $this->save();
    }

    // Mark as deposited

    public function markDeposited(): void
    {
        $this->status = 'deposited';
        $this->deposited_by = Auth::id();
        $this->deposited_at = now();
        $this->save();
    }

    // Reopen (admin only — reset to open)

    public function reopenEod(): void
    {
        $this->status = 'open';
        $this->note_500 = null;
        $this->note_100 = null;
        $this->note_50 = null;
        $this->note_20 = null;
        $this->note_10 = null;
        $this->coin_2 = null;
        $this->coin_1 = null;
        $this->counted_cash = 0;
        $this->variance = 0;
        $this->notes = null;
        $this->closed_by = null;
        $this->closed_at = null;
        $this->deposited_by = null;
        $this->deposited_at = null;
        $this->save();
    }
}
