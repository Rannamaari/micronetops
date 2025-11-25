<?php

use App\Http\Controllers\AcUnitController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InventoryCategoryController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\JobItemController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PettyCashController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoadWorthinessReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('home');
})->name('home');

// Contact form route (public, no auth required)
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

// Operations system route - redirects to login or dashboard
Route::get('/ops', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
})->name('ops');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Jobs - All authenticated users can manage
    Route::resource('jobs', JobController::class)->only([
        'index', 'create', 'store', 'show', 'update', 'destroy',
    ]);

    // Job items (parts & consumables on a job)
    Route::post('jobs/{job}/items', [JobItemController::class, 'store'])
        ->name('jobs.items.store');

    Route::delete('jobs/{job}/items/{item}', [JobItemController::class, 'destroy'])
        ->name('jobs.items.destroy');

    // Payments on a job
    Route::post('jobs/{job}/payments', [PaymentController::class, 'store'])
        ->name('jobs.payments.store');

    Route::delete('jobs/{job}/payments/{payment}', [PaymentController::class, 'destroy'])
        ->name('jobs.payments.destroy');

    // Invoice
    Route::get('jobs/{job}/invoice', [JobController::class, 'invoice'])
        ->name('jobs.invoice');

    // Customers - All authenticated users can manage
    Route::resource('customers', CustomerController::class);

    // Nested create for vehicles & AC units under customer
    Route::post('customers/{customer}/vehicles', [VehicleController::class, 'store'])
        ->name('customers.vehicles.store');

    Route::post('customers/{customer}/ac-units', [AcUnitController::class, 'store'])
        ->name('customers.ac-units.store');

    // Petty Cash - All authenticated users can view and create
    Route::get('petty-cash', [PettyCashController::class, 'index'])->name('petty-cash.index');
    Route::get('petty-cash/create', [PettyCashController::class, 'create'])->name('petty-cash.create');
    Route::post('petty-cash', [PettyCashController::class, 'store'])->name('petty-cash.store');

    // User & Role Management (admin only)
    Route::middleware('role:admin')->group(function () {
        Route::resource('roles', RoleController::class);
        Route::resource('users', UserManagementController::class)->except(['show']);
    });

    // Inventory Management (admin, manager)
    Route::middleware('role:admin,manager')->group(function () {
        Route::resource('inventory', InventoryController::class);
        Route::post('inventory/{inventory}/toggle-active', [InventoryController::class, 'toggleActive'])
            ->name('inventory.toggle-active');
        Route::post('inventory/{inventory}/adjust-stock', [InventoryController::class, 'adjustStock'])
            ->name('inventory.adjust-stock');
        Route::resource('inventory-categories', InventoryCategoryController::class);
    });

    // Reports (admin, manager)
    Route::middleware('role:admin,manager')->group(function () {
        Route::get('reports/road-worthiness', [RoadWorthinessReportController::class, 'index'])
            ->name('reports.road-worthiness');
    });

    // Petty Cash Approval (admin, manager)
    Route::middleware('role:admin,manager')->group(function () {
        Route::post('petty-cash/{pettyCash}/approve', [PettyCashController::class, 'approve'])
            ->name('petty-cash.approve');
        Route::post('petty-cash/{pettyCash}/reject', [PettyCashController::class, 'reject'])
            ->name('petty-cash.reject');
    });
});

require __DIR__.'/auth.php';
