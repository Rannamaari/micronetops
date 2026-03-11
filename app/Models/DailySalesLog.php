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
        'job_id',
        'customer_id',
        'payment_method',
        'cash_tendered',
        'transfer_account_id',
    ];

    protected $casts = [
        'date' => 'date',
        'submitted_at' => 'datetime',
        'cash_tendered' => 'decimal:2',
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

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function transferAccount()
    {
        return $this->belongsTo(Account::class, 'transfer_account_id');
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

    public function submit(string $paymentMethod, ?float $cashTendered = null, ?int $transferAccountId = null): void
    {
        // --- Set payment method on all lines (for reports compatibility) ---
        $this->lines()->update(['payment_method' => $paymentMethod]);
        $this->load('lines');

        // --- Stock deduction ---
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

        // --- Create Job from daily sales log ---
        $unit = $this->business_unit; // 'moto' or 'cool'
        $jobType = $unit === 'cool' ? 'ac' : 'moto';

        $customer = $this->customer_id ? Customer::find($this->customer_id) : null;

        $job = Job::create([
            'job_date'       => $this->date,
            'job_type'       => $jobType,
            'job_category'   => 'general',
            'title'          => 'Daily Sales — ' . $this->date->format('d M Y'),
            'customer_id'    => $customer?->id,
            'customer_name'  => $customer?->name ?? 'Walk-in',
            'customer_phone' => $customer?->phone,
            'customer_email' => $customer?->email,
            'status'         => 'completed',
            'payment_status' => 'paid',
            'priority'       => 'normal',
            'completed_at'   => now(),
        ]);

        foreach ($this->lines as $line) {
            JobItem::create([
                'job_id'            => $job->id,
                'inventory_item_id' => $line->inventory_item_id,
                'item_name'         => $line->description,
                'is_service'        => !$line->is_stock_item,
                'quantity'          => $line->qty,
                'unit_price'        => $line->unit_price,
                'subtotal'          => $line->line_total + $line->gst_amount,
            ]);
        }

        // Create payment BEFORE recalculateTotals so updatePaymentStatus sees it
        $totals = $this->totals;
        Payment::create([
            'job_id' => $job->id,
            'amount' => $totals['grand'],
            'method' => $paymentMethod,
            'status' => 'completed',
        ]);

        $job->recalculateTotals();

        $this->update([
            'status' => 'submitted',
            'submitted_at' => now(),
            'submitted_by' => Auth::id(),
            'job_id' => $job->id,
            'payment_method' => $paymentMethod,
            'cash_tendered' => $paymentMethod === 'cash' ? $cashTendered : null,
            'transfer_account_id' => $paymentMethod === 'transfer' ? $transferAccountId : null,
        ]);

        // --- Credit the transfer account ---
        if ($paymentMethod === 'transfer' && $transferAccountId) {
            $account = Account::find($transferAccountId);
            if ($account) {
                $account->balance += $totals['grand'];
                $account->save();

                AccountTransaction::create([
                    'account_id' => $account->id,
                    'type' => 'sale_transfer',
                    'amount' => $totals['grand'],
                    'occurred_at' => now()->toDateString(),
                    'description' => 'Daily sale #' . $this->id . ' transfer (sale date: ' . $this->date->format('Y-m-d') . ')',
                    'related_type' => self::class,
                    'related_id' => $this->id,
                    'created_by' => Auth::id(),
                ]);
            }
        }
    }

    public function reopen(): void
    {
        // Delete the linked job (cascades to job_items via DB; payments too)
        if ($this->job_id) {
            $job = Job::find($this->job_id);
            if ($job) {
                $job->payments()->delete();
                $job->items()->delete();
                $job->delete();
            }
        }

        // Reverse stock deductions
        $logs = InventoryLog::where('daily_sales_log_id', $this->id)->get();

        foreach ($logs as $log) {
            $item = $log->inventoryItem;
            if ($item) {
                $item->quantity += abs($log->quantity_change);
                $item->save();
            }
            $log->delete();
        }

        // Reverse transfer account credit
        if ($this->payment_method === 'transfer' && $this->transfer_account_id) {
            $account = Account::find($this->transfer_account_id);
            if ($account) {
                $grandTotal = $this->totals['grand'];
                $account->balance -= $grandTotal;
                $account->save();
            }
            AccountTransaction::where('related_type', self::class)
                ->where('related_id', $this->id)
                ->delete();
        }

        $this->update([
            'status' => 'draft',
            'submitted_at' => null,
            'submitted_by' => null,
            'job_id' => null,
            'payment_method' => null,
            'cash_tendered' => null,
            'transfer_account_id' => null,
        ]);
    }

    public function getTotalsAttribute(): array
    {
        $subtotal = $this->lines->sum('line_total');
        $gst = $this->lines->sum('gst_amount');
        $grand = $subtotal + $gst;

        return [
            'subtotal' => $subtotal,
            'gst' => $gst,
            'grand' => $grand,
        ];
    }
}
