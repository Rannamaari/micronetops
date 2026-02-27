<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;

class DailySalesLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'business_unit',
        'status',
        'created_by',
        'submitted_at',
        'submitted_by',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'submitted_at' => 'datetime',
    ];

    public function setDateAttribute($value)
    {
        $this->attributes['date'] = \Carbon\Carbon::parse($value)->format('Y-m-d');
    }

    public function lines()
    {
        return $this->hasMany(DailySalesLine::class);
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function submittedByUser()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function inventoryLogs()
    {
        return $this->hasMany(InventoryLog::class);
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    public function scopeForUnit($query, $unit)
    {
        return $query->where('business_unit', $unit);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function submit(): void
    {
        foreach ($this->lines as $line) {
            if ($line->is_stock_item && $line->inventory_item_id) {
                $item = $line->inventoryItem;
                if ($item) {
                    $item->quantity -= $line->qty;
                    $item->save();

                    InventoryLog::create([
                        'inventory_item_id' => $item->id,
                        'daily_sales_log_id' => $this->id,
                        'quantity_change' => -$line->qty,
                        'type' => 'sale',
                        'user_id' => Auth::id(),
                        'notes' => 'Daily sale log #' . $this->id . ' (' . $this->date->format('Y-m-d') . ')',
                    ]);
                }
            }
        }

        $this->update([
            'status' => 'submitted',
            'submitted_at' => now(),
            'submitted_by' => Auth::id(),
        ]);
    }

    public function reopen(): void
    {
        $logs = InventoryLog::where('daily_sales_log_id', $this->id)->get();

        foreach ($logs as $log) {
            $item = $log->inventoryItem;
            if ($item) {
                $item->quantity += abs($log->quantity_change);
                $item->save();
            }
            $log->delete();
        }

        $this->update([
            'status' => 'draft',
            'submitted_at' => null,
            'submitted_by' => null,
        ]);
    }

    public function getTotalsAttribute(): array
    {
        $cash = $this->lines->where('payment_method', 'cash')->sum('line_total');
        $transfer = $this->lines->where('payment_method', 'transfer')->sum('line_total');

        return [
            'cash' => $cash,
            'transfer' => $transfer,
            'grand' => $cash + $transfer,
        ];
    }
}
