<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function assignedJobs()
    {
        return $this->hasMany(Job::class, 'assigned_user_id');
    }

    public function pettyCashRequests()
    {
        return $this->hasMany(PettyCash::class);
    }

    public function approvedPettyCash()
    {
        return $this->hasMany(PettyCash::class, 'approved_by');
    }

    public function inventoryLogs()
    {
        return $this->hasMany(InventoryLog::class);
    }

    /**
     * Hardcoded roles
     */
    public const ROLE_ADMIN = 'admin';
    public const ROLE_MANAGER = 'manager';
    public const ROLE_MECHANIC = 'mechanic';
    public const ROLE_CASHIER = 'cashier';

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $roleName): bool
    {
        return $this->role === $roleName;
    }

    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole(array $roleNames): bool
    {
        return in_array($this->role, $roleNames);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Check if user is manager
     */
    public function isManager(): bool
    {
        return $this->role === self::ROLE_MANAGER;
    }

    /**
     * Check if user is mechanic
     */
    public function isMechanic(): bool
    {
        return $this->role === self::ROLE_MECHANIC;
    }

    /**
     * Check if user is cashier
     */
    public function isCashier(): bool
    {
        return $this->role === self::ROLE_CASHIER;
    }

    /**
     * Check if user can delete (only admin)
     */
    public function canDelete(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Check if user can manage users (admin or manager)
     */
    public function canManageUsers(): bool
    {
        return $this->hasAnyRole([self::ROLE_ADMIN, self::ROLE_MANAGER]);
    }

    /**
     * Check if user can approve expenses (admin or manager)
     */
    public function canApproveExpenses(): bool
    {
        return $this->hasAnyRole([self::ROLE_ADMIN, self::ROLE_MANAGER]);
    }

    /**
     * Check if user can manage top-ups (admin only)
     */
    public function canManageTopUps(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Check if user can create jobs
     */
    public function canCreateJobs(): bool
    {
        return !$this->isCashier();
    }

    /**
     * Check if user can view customers
     */
    public function canViewCustomers(): bool
    {
        return !$this->isCashier();
    }

    /**
     * Check if user can create expenses
     */
    public function canCreateExpenses(): bool
    {
        return $this->hasAnyRole([self::ROLE_ADMIN, self::ROLE_MANAGER, self::ROLE_MECHANIC]);
    }

    /**
     * Check if user can view reports
     */
    public function canViewReports(): bool
    {
        return true; // All users can view reports
    }
}
