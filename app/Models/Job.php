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

    // Status constants
    public const STATUS_NEW = 'new';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_WAITING_PARTS = 'waiting_parts';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    // Priority constants
    public const PRIORITY_URGENT = 'urgent';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_LOW = 'low';

    // Service type constants
    public const TYPE_AC = 'ac';
    public const TYPE_BIKE = 'moto';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_NEW => 'New',
            self::STATUS_SCHEDULED => 'Scheduled',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_WAITING_PARTS => 'Waiting for Parts',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    public static function getPriorities(): array
    {
        return [
            self::PRIORITY_URGENT => 'Urgent',
            self::PRIORITY_HIGH => 'High',
            self::PRIORITY_NORMAL => 'Normal',
            self::PRIORITY_LOW => 'Low',
        ];
    }

    public static function getActiveStatuses(): array
    {
        return [
            self::STATUS_NEW,
            self::STATUS_SCHEDULED,
            self::STATUS_IN_PROGRESS,
            self::STATUS_WAITING_PARTS,
        ];
    }

    protected $fillable = [
        'job_date',
        'job_type',
        'job_category',
        'title',
        'customer_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'vehicle_id',
        'ac_unit_id',
        'address',
        'location',
        'pickup_location',
        'assigned_user_id',
        'status',
        'priority',
        'payment_status',
        'problem_description',
        'internal_notes',
        'labour_total',
        'parts_total',
        'travel_charges',
        'discount',
        'total_amount',
        'scheduled_at',
        'scheduled_end_at',
        'started_at',
        'completed_at',
        'closed_at',
    ];

    protected $casts = [
        'job_date'          => 'date',
        'labour_total'      => 'decimal:2',
        'parts_total'       => 'decimal:2',
        'travel_charges'    => 'decimal:2',
        'discount'          => 'decimal:2',
        'total_amount'      => 'decimal:2',
        'updated_at'        => 'datetime',
        'scheduled_at'      => 'datetime',
        'scheduled_end_at'  => 'datetime',
        'started_at'        => 'datetime',
        'completed_at'      => 'datetime',
        'closed_at'         => 'datetime',
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

    // ========================================
    // New Relationships for Work Order System
    // ========================================

    /**
     * Get all assigned technicians for this job (many-to-many).
     */
    public function assignees()
    {
        return $this->belongsToMany(User::class, 'job_assignees')
            ->withPivot(['assigned_at', 'assigned_by'])
            ->withTimestamps();
    }

    /**
     * Get the notes/updates timeline for this job.
     */
    public function notes()
    {
        return $this->hasMany(JobNote::class)->orderByDesc('created_at');
    }

    // ========================================
    // Status & Workflow Methods
    // ========================================

    /**
     * Check if job is active (not completed or cancelled).
     */
    public function isActive(): bool
    {
        return in_array($this->status, self::getActiveStatuses());
    }

    /**
     * Check if job can be started.
     */
    public function canStart(): bool
    {
        return in_array($this->status, [self::STATUS_NEW, self::STATUS_SCHEDULED]);
    }

    /**
     * Check if job can be completed.
     */
    public function canComplete(): bool
    {
        return in_array($this->status, [self::STATUS_IN_PROGRESS, self::STATUS_WAITING_PARTS]);
    }

    /**
     * Update status with automatic logging.
     */
    public function updateStatus(string $newStatus, ?User $user = null, ?string $notes = null): void
    {
        $oldStatus = $this->status;

        if ($oldStatus === $newStatus) {
            return;
        }

        $this->status = $newStatus;

        // Set timestamps based on status
        if ($newStatus === self::STATUS_IN_PROGRESS && !$this->started_at) {
            $this->started_at = now();
        } elseif ($newStatus === self::STATUS_COMPLETED && !$this->completed_at) {
            $this->completed_at = now();
        }

        $this->save();

        // Log the status change
        $this->notes()->create([
            'user_id' => $user?->id,
            'type' => 'status_change',
            'content' => $notes ?: "Status changed from {$oldStatus} to {$newStatus}",
            'metadata' => ['from' => $oldStatus, 'to' => $newStatus],
        ]);
    }

    /**
     * Add a note to the job timeline.
     */
    public function addNote(string $content, ?User $user = null, string $type = 'note'): JobNote
    {
        return $this->notes()->create([
            'user_id' => $user?->id,
            'type' => $type,
            'content' => $content,
        ]);
    }

    /**
     * Assign technicians to this job.
     */
    public function assignTechnicians(array $userIds, ?User $assignedBy = null): void
    {
        $pivotData = [];
        foreach ($userIds as $userId) {
            $pivotData[$userId] = [
                'assigned_at' => now(),
                'assigned_by' => $assignedBy?->id,
            ];
        }

        $this->assignees()->sync($pivotData);

        // Log the assignment
        if ($assignedBy) {
            $names = User::whereIn('id', $userIds)->pluck('name')->join(', ');
            $this->addNote("Assigned to: {$names}", $assignedBy, 'assignment');
        }
    }

    // ========================================
    // Query Scopes
    // ========================================

    /**
     * Scope to active (non-completed, non-cancelled) jobs.
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', self::getActiveStatuses());
    }

    /**
     * Scope to completed jobs.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope to jobs assigned to a specific user.
     */
    public function scopeAssignedTo($query, $userId)
    {
        return $query->whereHas('assignees', function ($q) use ($userId) {
            $q->where('users.id', $userId);
        });
    }

    /**
     * Scope to jobs of a specific service type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('job_type', $type);
    }

    /**
     * Scope to jobs scheduled on a specific date.
     */
    public function scopeScheduledOn($query, $date)
    {
        return $query->whereDate('scheduled_at', $date);
    }

    /**
     * Scope to jobs scheduled between dates (for calendar).
     */
    public function scopeScheduledBetween($query, $start, $end)
    {
        return $query->whereBetween('scheduled_at', [$start, $end]);
    }

    /**
     * Scope to jobs by priority.
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope for search by customer name, phone, or title.
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
              ->orWhere('customer_name', 'like', "%{$term}%")
              ->orWhere('customer_phone', 'like', "%{$term}%")
              ->orWhere('location', 'like', "%{$term}%");
        });
    }

    // ========================================
    // Calendar Helpers
    // ========================================

    /**
     * Get status color for calendar display.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_NEW => '#6b7280',           // gray
            self::STATUS_SCHEDULED => '#3b82f6',    // blue
            self::STATUS_IN_PROGRESS => '#f59e0b',  // amber
            self::STATUS_WAITING_PARTS => '#ef4444', // red
            self::STATUS_COMPLETED => '#10b981',    // green
            self::STATUS_CANCELLED => '#9ca3af',    // gray-light
            default => '#6b7280',
        };
    }

    /**
     * Get service type color for calendar.
     */
    public function getTypeColorAttribute(): string
    {
        return match ($this->job_type) {
            self::TYPE_AC => '#0ea5e9',    // sky blue (AC = cold)
            self::TYPE_BIKE => '#f97316',  // orange (bike = moto)
            default => '#6b7280',
        };
    }

    /**
     * Get priority badge color.
     */
    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            self::PRIORITY_URGENT => '#dc2626', // red
            self::PRIORITY_HIGH => '#f59e0b',   // amber
            self::PRIORITY_NORMAL => '#3b82f6', // blue
            self::PRIORITY_LOW => '#6b7280',    // gray
            default => '#3b82f6',
        };
    }

    /**
     * Convert to FullCalendar event format.
     */
    public function toCalendarEvent(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title ?: $this->customer_name,
            'start' => $this->scheduled_at?->toIso8601String(),
            'end' => $this->scheduled_end_at?->toIso8601String(),
            'backgroundColor' => $this->type_color,
            'borderColor' => $this->status_color,
            'extendedProps' => [
                'customer_name' => $this->customer_name,
                'customer_phone' => $this->customer_phone,
                'job_type' => $this->job_type,
                'status' => $this->status,
                'status_label' => self::getStatuses()[$this->status] ?? $this->status,
                'status_color' => $this->status_color,
                'priority' => $this->priority,
                'location' => $this->location,
                'assignees' => $this->assignees->pluck('name')->toArray(),
            ],
        ];
    }
}
