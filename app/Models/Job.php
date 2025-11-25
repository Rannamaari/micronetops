<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Job extends Model
{
    use HasFactory;

    /**
     * Disable Laravel's automatic timestamp management.
     * We handle timestamps manually due to mixed column types.
     */
    public $timestamps = false;

    protected $fillable = [
        'job_type',
        'job_category',
        'customer_id',
        'vehicle_id',
        'ac_unit_id',
        'address',
        'pickup_location',
        'assigned_user_id',
        'status',
        'payment_status',
        'problem_description',
        'internal_notes',
        'labour_total',
        'parts_total',
        'travel_charges',
        'discount',
        'total_amount',
        'started_at',
        'completed_at',
        'closed_at',
    ];

    protected $casts = [
        'labour_total'    => 'decimal:2',
        'parts_total'     => 'decimal:2',
        'travel_charges'  => 'decimal:2',
        'discount'        => 'decimal:2',
        'total_amount'    => 'decimal:2',
        'updated_at'      => 'datetime',
        'started_at'      => 'datetime',
        'completed_at'    => 'datetime',
        'closed_at'       => 'datetime',
    ];

    /**
     * Boot the model and manually handle timestamps and queue columns.
     */
    protected static function boot()
    {
        parent::boot();

        // Manually set timestamps and queue columns on create
        static::creating(function ($model) {
            $now = now();
            $model->attributes['created_at'] = $now->timestamp;  // Unix timestamp (integer)
            $model->attributes['updated_at'] = $now;             // Carbon instance (will be converted to datetime)

            // Set default values for Laravel queue columns (not used but required by schema)
            $model->queue = $model->queue ?? 'default';
            $model->payload = $model->payload ?? '';
            $model->attempts = $model->attempts ?? 0;
            $model->available_at = $model->available_at ?? $now->timestamp;
        });

        // Manually set updated_at on update
        static::updating(function ($model) {
            $model->attributes['updated_at'] = now();
        });
    }

    /**
     * Get created_at as Carbon instance (stored as Unix timestamp integer).
     */
    public function getCreatedAtAttribute($value)
    {
        return $value ? \Carbon\Carbon::createFromTimestamp($value) : null;
    }

    /**
     * Set created_at as Unix timestamp integer.
     */
    public function setCreatedAtAttribute($value)
    {
        if ($value instanceof \DateTimeInterface) {
            $this->attributes['created_at'] = $value->getTimestamp();
        } elseif (is_numeric($value)) {
            $this->attributes['created_at'] = $value;
        } elseif (is_string($value)) {
            $this->attributes['created_at'] = strtotime($value);
        }
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function acUnit()
    {
        return $this->belongsTo(AcUnit::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function items()
    {
        return $this->hasMany(JobItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function getPaidAmountAttribute(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    public function getBalanceAmountAttribute(): float
    {
        return max(0, (float) $this->total_amount - $this->paid_amount);
    }

    public function updatePaymentStatus(): void
    {
        $paid = $this->paid_amount;
        $total = (float) $this->total_amount;

        if ($total <= 0 && $paid <= 0) {
            $this->payment_status = 'unpaid';
            $this->closed_at = null;
        } elseif ($paid <= 0) {
            $this->payment_status = 'unpaid';
            $this->closed_at = null;
        } elseif ($paid < $total) {
            $this->payment_status = 'partial';
            $this->closed_at = null;
        } else {
            $this->payment_status = 'paid';
            // close job when fully paid
            $this->status = $this->status === 'completed' ? 'completed' : 'completed';
            $this->closed_at = now();
        }

        $this->save();
    }

    public function recalculateTotals(): void
    {
        // Sum parts (non-service items)
        $partsTotal = $this->items()
            ->where('is_service', false)
            ->sum('subtotal');

        // Sum services as labour
        $labourTotal = $this->items()
            ->where('is_service', true)
            ->sum('subtotal');

        $this->parts_total  = $partsTotal;
        $this->labour_total = $labourTotal;

        $this->total_amount = $labourTotal
            + $this->travel_charges
            + $partsTotal
            - $this->discount;

        $this->save();

        $this->updatePaymentStatus();
    }

}
