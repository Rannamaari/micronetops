<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'source',
        'action',
        'entity_type',
        'entity_id',
        'description',
        'ip_address',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record an activity. Call from any controller.
     *
     * ActivityLog::record('expense.deleted', 'Expense #5 deleted (MVR 500.00)', $expense, ['amount' => 500]);
     */
    public static function record(
        string  $action,
        string  $description,
        ?Model  $entity = null,
        array   $meta = [],
        ?int    $userId = null,
        string  $source = 'web'
    ): void {
        try {
            static::create([
                'user_id'     => $userId ?? Auth::id(),
                'source'      => $source,
                'action'      => $action,
                'entity_type' => $entity ? class_basename($entity) : null,
                'entity_id'   => $entity?->id,
                'description' => $description,
                'ip_address'  => Request::ip(),
                'meta'        => $meta ?: null,
            ]);
        } catch (\Throwable) {
            // Never let logging break the main flow
        }
    }
}
