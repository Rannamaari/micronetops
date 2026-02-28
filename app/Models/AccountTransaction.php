<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AccountTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'type',
        'amount',
        'occurred_at',
        'description',
        'related_type',
        'related_id',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'occurred_at' => 'date',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
