<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;

class DailySalesLog extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_QUOTATION = 'quotation';
    public const STATUS_INVOICED = 'invoiced';
    public const STATUS_PARTIAL_PAID = 'partial_paid';
    public const STATUS_PAID = 'paid';

    protected $fillable = [
        'date',
        'due_date',
        'quotation_validity_days',
        'business_unit',
        'status',
        'created_by',
        'submitted_at',
        'submitted_by',
        'notes',
        'search_note',
        'job_id',
        'customer_id',
        'customer_address_id',
        'customer_address_text',
        'po_number',
        'approval_method',
        'payment_method',
        'cash_tendered',
        'transfer_account_id',
    ];

    protected $casts = [
        'date' => 'date',
        'due_date' => 'date',
        'quotation_validity_days' => 'integer',
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

    public function customerAddress()
    {
        return $this->belongsTo(CustomerAddress::class);
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
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeSubmitted($query)
    {
        return $query->whereIn('status', [
            'submitted',
            self::STATUS_INVOICED,
            self::STATUS_PARTIAL_PAID,
            self::STATUS_PAID,
        ]);
    }

    public function isSubmitted(): bool
    {
        return in_array($this->status, ['submitted', self::STATUS_PARTIAL_PAID, self::STATUS_PAID], true);
    }

    public function isInvoiceStage(): bool
    {
        return in_array($this->status, [
            'submitted',
            self::STATUS_INVOICED,
            self::STATUS_PARTIAL_PAID,
            self::STATUS_PAID,
        ], true);
    }

    public function canEditQuotation(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_QUOTATION], true);
    }

    public function isApprovalReady(): bool
    {
        return in_array(($this->approval_method ?? 'not_applicable'), ['signed_copy', 'not_applicable'], true)
            || !blank($this->po_number);
    }

    public function isReadyForInvoice(): bool
    {
        return (bool) $this->customer_id
            && $this->isApprovalReady()
            && $this->lines()->exists();
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_QUOTATION => 'Quotation',
            self::STATUS_INVOICED => 'Invoiced',
            self::STATUS_PARTIAL_PAID => 'Partial Paid',
            self::STATUS_PAID => 'Paid',
            'submitted' => 'Paid',
            default => str($this->status)->replace('_', ' ')->title()->toString(),
        };
    }

    public function syncWorkflowStatus(?Job $job = null): string
    {
        $job ??= $this->job_id ? Job::find($this->job_id) : null;

        if ($job) {
            $newStatus = match ($job->payment_status) {
                'paid' => self::STATUS_PAID,
                'partial' => self::STATUS_PARTIAL_PAID,
                default => self::STATUS_INVOICED,
            };
        } else {
            $newStatus = $this->lines()->exists() ? self::STATUS_QUOTATION : self::STATUS_DRAFT;
        }

        $attributes = ['status' => $newStatus];

        if (in_array($newStatus, [self::STATUS_DRAFT, self::STATUS_QUOTATION], true)) {
            $attributes['submitted_at'] = null;
            $attributes['submitted_by'] = null;
        } elseif (in_array($newStatus, [self::STATUS_PARTIAL_PAID, self::STATUS_PAID], true) && !$this->submitted_at) {
            $attributes['submitted_at'] = now();
            $attributes['submitted_by'] = Auth::id();
        }

        $dirty = false;
        foreach ($attributes as $key => $value) {
            if ($this->getAttribute($key) != $value) {
                $dirty = true;
                break;
            }
        }

        if ($dirty) {
            $this->forceFill($attributes)->save();
        }

        return $newStatus;
    }

    public function syncLinkedDraftJob(): ?Job
    {
        if ($this->isInvoiceStage() || !$this->job_id) {
            return null;
        }

        $this->loadMissing('lines', 'customer', 'customerAddress');

        $job = Job::find($this->job_id);
        if (!$job) {
            return null;
        }

        $customer = $this->customer_id ? Customer::find($this->customer_id) : null;
        $addressText = $this->customer_address_text ?: $this->customerAddress?->address ?: $customer?->address;

        $job->fill([
            'job_date'       => $this->date,
            'due_date'       => $this->due_date,
            'quotation_validity_days' => $this->quotation_validity_days ?: 3,
            'customer_id'    => $customer?->id,
            'customer_address_id' => $this->customer_address_id,
            'customer_name'  => $customer?->name ?? 'Walk-in',
            'customer_phone' => $customer?->phone,
            'customer_email' => $customer?->email,
            'address'        => $addressText,
            'location'       => $addressText,
            'customer_notes' => $this->notes,
            'search_note'    => $this->search_note,
            'po_number'      => $this->po_number,
            'approval_method' => $this->approval_method ?: 'not_applicable',
        ]);
        $job->save();

        $job->items()->delete();

        foreach ($this->lines as $line) {
            JobItem::create([
                'job_id'            => $job->id,
                'inventory_item_id' => $line->inventory_item_id,
                'item_name'         => $line->description ?: ($line->inventoryItem?->name ?? ($line->is_stock_item ? 'Item' : 'Service')),
                'item_description'  => $line->note ?: null,
                'warranty_value'    => $line->warranty_value,
                'warranty_unit'     => $line->warranty_unit,
                'is_service'        => !$line->is_stock_item,
                'quantity'          => $line->qty,
                'unit_price'        => $line->unit_price,
                'subtotal'          => $line->line_total,
                'is_gst_applicable' => (bool) $line->is_gst_applicable,
            ]);
        }

        $job->recalculateTotals();

        return $job->fresh(['items.inventoryItem', 'payments']);
    }

    public function createOrUpdateInvoiceJob(bool $markPaid, ?string $paymentMethod = null): Job
    {
        $this->loadMissing('lines', 'customerAddress');

        $unit = $this->business_unit; // 'moto' | 'cool' | 'it' | 'easyfix'
        $jobType = match ($unit) {
            'cool' => 'ac',
            'it' => 'it',
            'easyfix' => 'easyfix',
            default => 'moto',
        };

        $customer = $this->customer_id ? Customer::find($this->customer_id) : null;
        $addressText = $this->customer_address_text ?: $this->customerAddress?->address ?: $customer?->address;

        $job = $this->job_id ? Job::find($this->job_id) : null;

        if ($job) {
            $job->payments()->delete();
            $job->items()->delete();

            $job->fill([
                'job_date'       => $this->date,
                'due_date'       => $this->due_date,
                'quotation_validity_days' => $this->quotation_validity_days ?: 3,
                'job_type'       => $jobType,
                'job_category'   => 'general',
                'title'          => 'Daily Sales — ' . $this->date->format('d M Y'),
                'customer_id'    => $customer?->id,
                'customer_address_id' => $this->customer_address_id,
                'customer_name'  => $customer?->name ?? 'Walk-in',
                'customer_phone' => $customer?->phone,
                'customer_email' => $customer?->email,
                'address'        => $addressText,
                'location'       => $addressText,
                'customer_notes' => $this->notes,
                'search_note'    => $this->search_note,
                'po_number'      => $this->po_number,
                'approval_method' => $this->approval_method ?: 'not_applicable',
                'status'         => 'completed',
                'payment_status' => $markPaid ? 'paid' : 'unpaid',
                'priority'       => 'normal',
                'completed_at'   => now(),
            ]);
            $job->save();
        } else {
            $job = Job::create([
                'job_date'       => $this->date,
                'due_date'       => $this->due_date,
                'quotation_validity_days' => $this->quotation_validity_days ?: 3,
                'job_type'       => $jobType,
                'job_category'   => 'general',
                'title'          => 'Daily Sales — ' . $this->date->format('d M Y'),
                'customer_id'    => $customer?->id,
                'customer_address_id' => $this->customer_address_id,
                'customer_name'  => $customer?->name ?? 'Walk-in',
                'customer_phone' => $customer?->phone,
                'customer_email' => $customer?->email,
                'address'        => $addressText,
                'location'       => $addressText,
                'customer_notes' => $this->notes,
                'search_note'    => $this->search_note,
                'po_number'      => $this->po_number,
                'approval_method' => $this->approval_method ?: 'not_applicable',
                'status'         => 'completed',
                'payment_status' => $markPaid ? 'paid' : 'unpaid',
                'priority'       => 'normal',
                'completed_at'   => now(),
            ]);
        }

        foreach ($this->lines as $line) {
            JobItem::create([
                'job_id'            => $job->id,
                'inventory_item_id' => $line->inventory_item_id,
                'item_name'         => $line->description ?: ($line->inventoryItem?->name ?? ($line->is_stock_item ? 'Item' : 'Service')),
                'item_description'  => $line->note ?: null,
                'warranty_value'    => $line->warranty_value,
                'warranty_unit'     => $line->warranty_unit,
                'is_service'        => !$line->is_stock_item,
                'quantity'          => $line->qty,
                'unit_price'        => $line->unit_price,
                'subtotal'          => $line->line_total,
                'is_gst_applicable' => (bool) $line->is_gst_applicable,
            ]);
        }

        if ($markPaid) {
            $totals = $this->totals;
            Payment::create([
                'job_id' => $job->id,
                'amount' => $totals['grand'],
                'method' => $paymentMethod ?? 'cash',
                'status' => 'completed',
            ]);
        }

        $job->recalculateTotals();

        return $job;
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

        // --- Create/update invoice job from daily sales log ---
        $job = $this->createOrUpdateInvoiceJob(true, $paymentMethod);

        $this->update([
            'status' => self::STATUS_PAID,
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
                $totals = $this->totals;
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
            'status' => $this->lines()->exists() ? self::STATUS_QUOTATION : self::STATUS_DRAFT,
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
