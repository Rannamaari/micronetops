<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Account extends Model
{
    use HasFactory;

    public const TYPE_BUSINESS = 'business';
    public const TYPE_PERSONAL = 'personal';

    protected $fillable = [
        'name',
        'type',
        'is_active',
        'is_system',
        'balance',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_system' => 'boolean',
        'balance' => 'decimal:2',
    ];

    public function transactions()
    {
        return $this->hasMany(AccountTransaction::class);
    }

    public static function getTypes(): array
    {
        return [
            self::TYPE_BUSINESS => 'Business',
            self::TYPE_PERSONAL => 'Personal',
        ];
    }
}
