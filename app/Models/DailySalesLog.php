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

    public function submit(string $paymentMethod, ?float $cashTendered = null): void
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

        if ($this->customer_id) {
            $customer = Customer::find($this->customer_id);
        }

        if (empty($customer)) {
            $customer = Customer::firstOrCreate(
                ['phone' => $unit === 'moto' ? '0000000' : '0000001'],
                ['name' => 'Walk-in Customer', 'category' => $jobType]
            );
        }

        $job = Job::create([
            'job_date'       => $this->date,
            'job_type'       => $jobType,
            'job_category'   => 'general',
            'title'          => 'Daily Sales — ' . $this->date->format('d M Y'),
            'customer_id'    => $customer->id,
            'customer_name'  => $customer->name,
            'customer_phone' => $customer->phone,
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

        $job->recalculateTotals();

        // Create a single payment for the grand total
        $totals = $this->totals;
        Payment::create([
            'job_id' => $job->id,
            'amount' => $totals['grand'],
            'method' => $paymentMethod,
            'status' => 'completed',
        ]);

        $this->update([
            'status' => 'submitted',
            'submitted_at' => now(),
            'submitted_by' => Auth::id(),
            'job_id' => $job->id,
            'payment_method' => $paymentMethod,
            'cash_tendered' => $paymentMethod === 'cash' ? $cashTendered : null,
        ]);
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

        $this->update([
            'status' => 'draft',
            'submitted_at' => null,
            'submitted_by' => null,
            'job_id' => null,
            'payment_method' => null,
            'cash_tendered' => null,
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
