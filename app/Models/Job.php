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

    /**
     * Check if created_at is stored as integer (Unix timestamp).
     * Cache the result to avoid repeated checks.
     */
    protected static $createdAtIsInteger = null;

    public static function createdAtIsInteger(): bool
    {
        if (static::$createdAtIsInteger === null) {
            try {
                // Check the actual column type from the database
                $connection = \Illuminate\Support\Facades\DB::connection();
                $table = (new static)->getTable();

                if ($connection->getDriverName() === 'pgsql') {
                    $result = $connection->selectOne(
                        "SELECT data_type FROM information_schema.columns
                         WHERE table_name = ? AND column_name = 'created_at'",
                        [$table]
                    );

                    if ($result) {
                        // integer, bigint, int4, int8 = integer column
                        static::$createdAtIsInteger = in_array(strtolower($result->data_type), ['integer', 'bigint', 'int4', 'int8']);
                    } else {
                        static::$createdAtIsInteger = false;
                    }
                } else {
                    // For non-PostgreSQL, default to false
                    static::$createdAtIsInteger = false;
                }
            } catch (\Exception $e) {
                static::$createdAtIsInteger = false;
            }
        }
        return static::$createdAtIsInteger;
    }

    /**
     * Convert datetime to the format needed for created_at queries.
     */
    public static function formatCreatedAtForQuery($datetime)
    {
        if (!($datetime instanceof \DateTimeInterface)) {
            $datetime = \Carbon\Carbon::parse($datetime);
        }

        return static::createdAtIsInteger() ? $datetime->timestamp : $datetime;
    }

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

            // Set timestamps - let the accessor/mutator handle the format
            if (!$model->created_at) {
                $model->created_at = $now;
            }
            if (!$model->updated_at) {
                $model->updated_at = $now;
            }

            // Set default values for Laravel queue columns (only if they exist)
            try {
                if (!\Illuminate\Support\Facades\Schema::hasColumn('jobs', 'queue')) {
                    return;
                }
                $model->queue = $model->queue ?? 'default';
                $model->payload = $model->payload ?? '';
                $model->attempts = $model->attempts ?? 0;
                $model->available_at = $model->available_at ?? $now->timestamp;
            } catch (\Exception $e) {
                // Queue columns don't exist, skip
            }
        });

        // Manually set updated_at on update
        static::updating(function ($model) {
            if (!$model->updated_at) {
                $model->updated_at = now();
            }
        });
    }

    /**
     * Get created_at as Carbon instance.
     * Handles both integer (Unix timestamp) and timestamp columns.
     */
    public function getCreatedAtAttribute($value)
    {
        if (!$value) {
            return null;
        }

        // If it's an integer (Unix timestamp from production DB)
        if (is_numeric($value) && $value > 1000000000) {
            return \Carbon\Carbon::createFromTimestamp($value);
        }

        // If it's already a timestamp string (local DB)
        return \Carbon\Carbon::parse($value);
    }

    /**
     * Set created_at - adapts based on whether column is integer or timestamp.
     */
    public function setCreatedAtAttribute($value)
    {
        if (!$value) {
            return;
        }

        // Convert to Carbon if not already
        if (!($value instanceof \DateTimeInterface)) {
            $value = \Carbon\Carbon::parse($value);
        }

        // If column is integer, store as Unix timestamp
        if (static::createdAtIsInteger()) {
            $this->attributes['created_at'] = $value->timestamp;
        } else {
            // Otherwise store as is (Laravel will handle datetime conversion)
            $this->attributes['created_at'] = $value;
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
