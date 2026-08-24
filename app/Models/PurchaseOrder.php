<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_ISSUED = 'issued';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'po_number',
        'business_unit',
        'vendor_id',
        'vendor_name',
        'vendor_phone',
        'vendor_contact_name',
        'vendor_address',
        'order_date',
        'expected_date',
        'status',
        'reference',
        'notes',
        'terms',
        'subtotal',
        'total_amount',
        'created_by',
        'issued_at',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_date' => 'date',
        'issued_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function lines()
    {
        return $this->hasMany(PurchaseOrderLine::class)->orderBy('sort_order')->orderBy('id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeLatestFirst($query)
    {
        return $query->orderByDesc('order_date')->orderByDesc('id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_ISSUED => 'Issued',
            self::STATUS_CANCELLED => 'Cancelled',
            default => str($this->status)->replace('_', ' ')->title()->toString(),
        };
    }

    public function canEdit(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_CANCELLED], true);
    }

    public function recalculateTotals(): void
    {
        $subtotal = (float) $this->lines()->sum('line_total');

        $this->forceFill([
            'subtotal' => round($subtotal, 2),
            'total_amount' => round($subtotal, 2),
        ])->save();
    }

    public function ensureNumber(): void
    {
        if ($this->po_number) {
            return;
        }

        $this->forceFill([
            'po_number' => 'PO-' . str_pad((string) $this->id, 5, '0', STR_PAD_LEFT),
        ])->save();
    }
}
