<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Define authorization gates based on roles
        Gate::define('manage-users', function ($user) {
            return $user->hasAnyRole(['admin', 'manager']);
        });

        Gate::define('manage-roles', function ($user) {
            return $user->hasRole('admin');
        });

        Gate::define('manage-inventory', function ($user) {
            return $user->hasAnyRole(['admin', 'manager']);
        });

        Gate::define('approve-petty-cash', function ($user) {
            return $user->hasAnyRole(['admin', 'manager']);
        });

        Gate::define('view-reports', function ($user) {
            // All users can view reports
            return true;
        });

        Gate::define('manage-jobs', function ($user) {
            return $user->hasAnyRole(['admin', 'manager', 'mechanic']);
        });

        Gate::define('manage-payments', function ($user) {
            return $user->hasAnyRole(['admin', 'manager', 'cashier']);
        });
    }
}
