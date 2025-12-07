<?php

use App\Http\Controllers\AcUnitController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\HRController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InventoryCategoryController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\JobItemController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PettyCashController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoadWorthinessReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        // Redirect HR users to HR dashboard
        if (auth()->user()->isHR()) {
            return redirect()->route('hr.dashboard');
        }
        return redirect()->route('dashboard');
    }
    return view('home');
})->name('home');

// Contact form route (public, no auth required)
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

// Operations system route - redirects to login or dashboard
Route::get('/ops', function () {
    if (auth()->check()) {
        // Redirect HR users to HR dashboard
        if (auth()->user()->isHR()) {
            return redirect()->route('hr.dashboard');
        }
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
})->name('ops');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// HR Dashboard - Admin and HR users only
Route::get('/hr', [HRController::class, 'dashboard'])
    ->middleware(['auth', 'hr'])
    ->name('hr.dashboard');

Route::middleware('auth')->group(function () {
    // Profile - All authenticated users
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Reports - All users can view
    Route::get('reports/road-worthiness', [RoadWorthinessReportController::class, 'index'])
        ->name('reports.road-worthiness');

    // Jobs - Admin, Manager, Mechanic (not Cashier)
    Route::middleware('role:admin,manager,mechanic')->group(function () {
        Route::get('jobs', [JobController::class, 'index'])->name('jobs.index');
        Route::get('jobs/create', [JobController::class, 'create'])->name('jobs.create');
        Route::post('jobs', [JobController::class, 'store'])->name('jobs.store');
        Route::get('jobs/{job}', [JobController::class, 'show'])->name('jobs.show');
        Route::patch('jobs/{job}', [JobController::class, 'update'])->name('jobs.update');

        // Customer search for job creation
        Route::get('jobs/search/customers', [JobController::class, 'searchCustomers'])
            ->name('jobs.search-customers');

        // Job items (parts & consumables on a job)
        Route::post('jobs/{job}/items', [JobItemController::class, 'store'])
            ->name('jobs.items.store');

        // Payments on a job
        Route::post('jobs/{job}/payments', [PaymentController::class, 'store'])
            ->name('jobs.payments.store');

        // Invoice & Quotation
        Route::get('jobs/{job}/invoice', [JobController::class, 'invoice'])
            ->name('jobs.invoice');
        Route::get('jobs/{job}/quotation', [JobController::class, 'quotation'])
            ->name('jobs.quotation');

        // Job Status Update
        Route::patch('jobs/{job}/status', [JobController::class, 'updateStatus'])
            ->name('jobs.update-status');
    });

    // Job deletion - Admin only
    Route::middleware('role:admin')->group(function () {
        Route::delete('jobs/{job}', [JobController::class, 'destroy'])->name('jobs.destroy');
        Route::delete('jobs/{job}/items/{item}', [JobItemController::class, 'destroy'])
            ->name('jobs.items.destroy');
        Route::delete('jobs/{job}/payments/{payment}', [PaymentController::class, 'destroy'])
            ->name('jobs.payments.destroy');
    });

    // Customers - Admin, Manager, Mechanic (not Cashier)
    Route::middleware('role:admin,manager,mechanic')->group(function () {
        Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('customers/create', [CustomerController::class, 'create'])->name('customers.create');
        Route::post('customers', [CustomerController::class, 'store'])->name('customers.store');
        Route::get('customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
        Route::get('customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
        Route::patch('customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');

        // Nested create for vehicles & AC units under customer
        Route::post('customers/{customer}/vehicles', [VehicleController::class, 'store'])
            ->name('customers.vehicles.store');
        Route::post('customers/{customer}/ac-units', [AcUnitController::class, 'store'])
            ->name('customers.ac-units.store');
    });

    // Customer deletion - Admin only
    Route::middleware('role:admin')->group(function () {
        Route::delete('customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
    });

    // Petty Cash History - All users can view
    Route::get('petty-cash/history', [PettyCashController::class, 'history'])->name('petty-cash.history');

    // Petty Cash - Admin, Manager, Mechanic can view and create
    Route::middleware('role:admin,manager,mechanic')->group(function () {
        Route::get('petty-cash', [PettyCashController::class, 'index'])->name('petty-cash.index');
        Route::get('petty-cash/create', [PettyCashController::class, 'create'])->name('petty-cash.create');
        Route::post('petty-cash', [PettyCashController::class, 'store'])->name('petty-cash.store');
    });

    // Petty Cash Approval - Admin, Manager only
    Route::middleware('role:admin,manager')->group(function () {
        Route::post('petty-cash/{pettyCash}/approve', [PettyCashController::class, 'approve'])
            ->name('petty-cash.approve');
        Route::post('petty-cash/{pettyCash}/reject', [PettyCashController::class, 'reject'])
            ->name('petty-cash.reject');
    });

    // Top-ups/Add Money - Admin only (these routes will be created later)
    // Route::middleware('role:admin')->group(function () {
    //     Route for top-ups and add money functionality
    // });

    // User Management - Admin and Manager
    Route::middleware('role:admin,manager')->group(function () {
        Route::resource('users', UserManagementController::class)->except(['show']);
    });

    // Role Management - Admin only
    Route::middleware('role:admin')->group(function () {
        Route::resource('roles', RoleController::class);
    });

    // Inventory Management - Admin, Manager only
    Route::middleware('role:admin,manager')->group(function () {
        Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::get('inventory/create', [InventoryController::class, 'create'])->name('inventory.create');
        Route::post('inventory', [InventoryController::class, 'store'])->name('inventory.store');
        Route::get('inventory/{inventory}', [InventoryController::class, 'show'])->name('inventory.show');
        Route::get('inventory/{inventory}/edit', [InventoryController::class, 'edit'])->name('inventory.edit');
        Route::patch('inventory/{inventory}', [InventoryController::class, 'update'])->name('inventory.update');
        Route::post('inventory/{inventory}/toggle-active', [InventoryController::class, 'toggleActive'])
            ->name('inventory.toggle-active');
        Route::post('inventory/{inventory}/adjust-stock', [InventoryController::class, 'adjustStock'])
            ->name('inventory.adjust-stock');
        Route::resource('inventory-categories', InventoryCategoryController::class);
    });

    // Inventory deletion - Admin only
    Route::middleware('role:admin')->group(function () {
        Route::delete('inventory/{inventory}', [InventoryController::class, 'destroy'])->name('inventory.destroy');
    });

    // Employee Management - Admin and HR
    Route::middleware('role:admin,hr')->group(function () {
        Route::resource('employees', EmployeeController::class);

        // Letter of Appointment
        Route::get('employees/{employee}/letter-of-appointment', [EmployeeController::class, 'letterOfAppointment'])
            ->name('employees.letter-of-appointment');

        // Employee Leaves
        Route::get('employees/{employee}/leaves', [App\Http\Controllers\EmployeeLeaveController::class, 'index'])->name('employees.leaves.index');
        Route::get('employees/{employee}/leaves/create', [App\Http\Controllers\EmployeeLeaveController::class, 'create'])->name('employees.leaves.create');
        Route::post('employees/{employee}/leaves', [App\Http\Controllers\EmployeeLeaveController::class, 'store'])->name('employees.leaves.store');
        Route::get('employees/{employee}/leaves/{leave}/edit', [App\Http\Controllers\EmployeeLeaveController::class, 'edit'])->name('employees.leaves.edit');
        Route::patch('employees/{employee}/leaves/{leave}', [App\Http\Controllers\EmployeeLeaveController::class, 'update'])->name('employees.leaves.update');
        Route::delete('employees/{employee}/leaves/{leave}', [App\Http\Controllers\EmployeeLeaveController::class, 'destroy'])->name('employees.leaves.destroy');

        // Global Loans & Advances Management
        Route::get('loans', [App\Http\Controllers\EmployeeLoanController::class, 'allLoans'])->name('loans.index');

        // Employee Allowances
        Route::get('employees/{employee}/allowances', [App\Http\Controllers\EmployeeAllowanceController::class, 'index'])->name('employees.allowances.index');
        Route::get('employees/{employee}/allowances/create', [App\Http\Controllers\EmployeeAllowanceController::class, 'create'])->name('employees.allowances.create');
        Route::post('employees/{employee}/allowances', [App\Http\Controllers\EmployeeAllowanceController::class, 'store'])->name('employees.allowances.store');
        Route::get('employees/{employee}/allowances/{allowance}/edit', [App\Http\Controllers\EmployeeAllowanceController::class, 'edit'])->name('employees.allowances.edit');
        Route::patch('employees/{employee}/allowances/{allowance}', [App\Http\Controllers\EmployeeAllowanceController::class, 'update'])->name('employees.allowances.update');
        Route::delete('employees/{employee}/allowances/{allowance}', [App\Http\Controllers\EmployeeAllowanceController::class, 'destroy'])->name('employees.allowances.destroy');

        // Employee Bonuses
        Route::get('employees/{employee}/bonuses', [App\Http\Controllers\EmployeeBonusController::class, 'index'])->name('employees.bonuses.index');
        Route::get('employees/{employee}/bonuses/create', [App\Http\Controllers\EmployeeBonusController::class, 'create'])->name('employees.bonuses.create');
        Route::post('employees/{employee}/bonuses', [App\Http\Controllers\EmployeeBonusController::class, 'store'])->name('employees.bonuses.store');
        Route::get('employees/{employee}/bonuses/{bonus}/edit', [App\Http\Controllers\EmployeeBonusController::class, 'edit'])->name('employees.bonuses.edit');
        Route::patch('employees/{employee}/bonuses/{bonus}', [App\Http\Controllers\EmployeeBonusController::class, 'update'])->name('employees.bonuses.update');
        Route::delete('employees/{employee}/bonuses/{bonus}', [App\Http\Controllers\EmployeeBonusController::class, 'destroy'])->name('employees.bonuses.destroy');

        // Employee Loans
        Route::get('employees/{employee}/loans', [App\Http\Controllers\EmployeeLoanController::class, 'index'])->name('employees.loans.index');
        Route::get('employees/{employee}/loans/create', [App\Http\Controllers\EmployeeLoanController::class, 'create'])->name('employees.loans.create');
        Route::post('employees/{employee}/loans', [App\Http\Controllers\EmployeeLoanController::class, 'store'])->name('employees.loans.store');
        Route::get('employees/{employee}/loans/{loan}/edit', [App\Http\Controllers\EmployeeLoanController::class, 'edit'])->name('employees.loans.edit');
        Route::patch('employees/{employee}/loans/{loan}', [App\Http\Controllers\EmployeeLoanController::class, 'update'])->name('employees.loans.update');
        Route::post('employees/{employee}/loans/{loan}/mark-paid', [App\Http\Controllers\EmployeeLoanController::class, 'markAsPaid'])->name('employees.loans.mark-paid');
        Route::delete('employees/{employee}/loans/{loan}', [App\Http\Controllers\EmployeeLoanController::class, 'destroy'])->name('employees.loans.destroy');

        // Attendance Management
        Route::get('attendance', [App\Http\Controllers\AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('attendance', [App\Http\Controllers\AttendanceController::class, 'store'])->name('attendance.store');
        Route::post('attendance/mark-all-present', [App\Http\Controllers\AttendanceController::class, 'markAllPresent'])->name('attendance.mark-all-present');

        // Payroll Management
        Route::get('payroll', [App\Http\Controllers\PayrollController::class, 'index'])->name('payroll.index');
        Route::get('payroll/create', [App\Http\Controllers\PayrollController::class, 'create'])->name('payroll.create');
        Route::post('payroll', [App\Http\Controllers\PayrollController::class, 'store'])->name('payroll.store');
        Route::get('payroll/{payroll}', [App\Http\Controllers\PayrollController::class, 'show'])->name('payroll.show');
        Route::delete('payroll/{payroll}', [App\Http\Controllers\PayrollController::class, 'destroy'])->name('payroll.destroy');
    });

    // System Settings & Admin Tools - Admin only
    Route::middleware('role:admin')->group(function () {
        Route::get('system/settings', [SystemController::class, 'settings'])->name('system.settings');
        Route::post('system/purge', [SystemController::class, 'purgeAllData'])->name('system.purge');
    });
});

require __DIR__.'/auth.php';
