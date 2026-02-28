<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExpenseCategory extends Model
{
    use HasFactory;

    public const TYPE_OPERATING = 'operating';
    public const TYPE_COGS = 'cogs';
    public const TYPE_OTHER = 'other';

    protected $fillable = [
        'name',
        'type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public static function getTypes(): array
    {
        return [
            self::TYPE_OPERATING => 'Operating',
            self::TYPE_COGS => 'COGS',
            self::TYPE_OTHER => 'Other',
        ];
    }
}
