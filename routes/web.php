<?php

use App\Http\Controllers\AcUnitController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DailySalesController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FaultTicketController;
use App\Http\Controllers\EodController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\HRController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AccountTransferController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InventoryCategoryController;
use App\Http\Controllers\InventoryPurchaseController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\JobItemController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\PnLController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PettyCashController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecurringExpenseController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\RattehinController;
use App\Http\Controllers\ReportsController;
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
        // Redirect customers to Rattehin
        if (auth()->user()->isCustomer()) {
            return redirect()->route('rattehin.index');
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
        // Redirect customers to Rattehin (they can't access operations)
        if (auth()->user()->isCustomer()) {
            return redirect()->route('rattehin.index');
        }
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
})->name('ops');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'operations'])
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

    // Reports - Admin and Manager only
    Route::middleware('role:admin,manager')->group(function () {
        Route::get('reports', [ReportsController::class, 'index'])->name('reports.index');
        Route::get('reports/road-worthiness', [RoadWorthinessReportController::class, 'index'])
            ->name('reports.road-worthiness');
        Route::get('reports/daily-sales', [ReportsController::class, 'dailySales'])->name('reports.daily-sales');
        Route::get('reports/best-sellers', [ReportsController::class, 'bestSellers'])->name('reports.best-sellers');
        Route::get('reports/low-inventory', [ReportsController::class, 'lowInventory'])->name('reports.low-inventory');
        Route::get('reports/sales-trends', [ReportsController::class, 'salesTrends'])->name('reports.sales-trends');
        Route::get('reports/inventory-overview', [ReportsController::class, 'inventoryOverview'])->name('reports.inventory-overview');
    });

    // Jobs - Admin, Manager, Moto Mechanic, AC Mechanic
    Route::middleware('role:admin,manager,moto_mechanic,ac_mechanic')->group(function () {
        // Job list & CRUD
        Route::get('jobs', [JobController::class, 'index'])->name('jobs.index');
        Route::get('jobs/create', [JobController::class, 'create'])->name('jobs.create');
        Route::post('jobs', [JobController::class, 'store'])->name('jobs.store');
        Route::get('jobs/{job}', [JobController::class, 'show'])->name('jobs.show');
        Route::patch('jobs/{job}', [JobController::class, 'update'])->name('jobs.update');

        // Calendar view & API
        Route::get('jobs-calendar', [JobController::class, 'calendar'])->name('jobs.calendar');
        Route::get('jobs-calendar/events', [JobController::class, 'calendarEvents'])->name('jobs.calendar-events');
        Route::post('jobs-calendar/quick-create', [JobController::class, 'quickCreate'])->name('jobs.quick-create');

        // Customer search for job creation
        Route::get('jobs/search/customers', [JobController::class, 'searchCustomers'])
            ->name('jobs.search-customers');

        // Job items (parts & consumables on a job)
        Route::post('jobs/{job}/items', [JobItemController::class, 'store'])
            ->name('jobs.items.store');

        // Payments on a job
        Route::post('jobs/{job}/payments', [PaymentController::class, 'store'])
            ->name('jobs.payments.store');

        // Daily Sales Log
        Route::get('sales/daily', [DailySalesController::class, 'index'])->name('sales.daily.index');
        Route::post('sales/daily', [DailySalesController::class, 'openLog'])->name('sales.daily.open');
        Route::get('sales/daily/{dailySalesLog}', [DailySalesController::class, 'show'])->name('sales.daily.show');
        Route::post('sales/daily/{dailySalesLog}/lines', [DailySalesController::class, 'addLine'])->name('sales.daily.add-line');
        Route::delete('sales/daily/{dailySalesLog}/lines/{line}', [DailySalesController::class, 'removeLine'])->name('sales.daily.remove-line');
        Route::post('sales/daily/{dailySalesLog}/set-customer', [DailySalesController::class, 'setCustomer'])->name('sales.daily.set-customer');
        Route::post('sales/daily/{dailySalesLog}/create-customer', [DailySalesController::class, 'createAndSetCustomer'])->name('sales.daily.create-customer');
        Route::post('sales/daily/{dailySalesLog}/submit', [DailySalesController::class, 'submit'])->name('sales.daily.submit');
        Route::get('sales/daily/{dailySalesLog}/quotation', [DailySalesController::class, 'quotation'])->name('sales.daily.quotation');
        Route::post('sales/daily/{dailySalesLog}/reopen', [DailySalesController::class, 'reopen'])->name('sales.daily.reopen');
        Route::delete('sales/daily/{dailySalesLog}', [DailySalesController::class, 'destroy'])->name('sales.daily.destroy');
        Route::get('sales/reports', [DailySalesController::class, 'reports'])->name('sales.reports');

        // End of Day
        Route::get('sales/eod', [EodController::class, 'index'])->name('sales.eod.index');
        Route::post('sales/eod', [EodController::class, 'create'])->name('sales.eod.create');
        Route::get('sales/eod/{eod}', [EodController::class, 'show'])->name('sales.eod.show');
        Route::post('sales/eod/{eod}/close', [EodController::class, 'close'])->name('sales.eod.close');
        Route::post('sales/eod/{eod}/deposit', [EodController::class, 'deposit'])->name('sales.eod.deposit');
        Route::post('sales/eod/{eod}/reopen', [EodController::class, 'reopen'])->name('sales.eod.reopen');

        // Invoice & Quotation
        Route::get('jobs/{job}/invoice', [JobController::class, 'invoice'])
            ->name('jobs.invoice');
        Route::get('jobs/{job}/quotation', [JobController::class, 'quotation'])
            ->name('jobs.quotation');

        // Job workflow actions
        Route::patch('jobs/{job}/status', [JobController::class, 'updateStatus'])
            ->name('jobs.update-status');
        Route::post('jobs/{job}/notes', [JobController::class, 'addNote'])
            ->name('jobs.add-note');
        Route::patch('jobs/{job}/assignees', [JobController::class, 'updateAssignees'])
            ->name('jobs.update-assignees');
        Route::patch('jobs/{job}/reschedule', [JobController::class, 'reschedule'])
            ->name('jobs.reschedule');
        Route::patch('jobs/{job}/restore', [JobController::class, 'restore'])
            ->name('jobs.restore');
        Route::post('jobs/{job}/cancel', [JobController::class, 'cancel'])
            ->name('jobs.cancel');
    });

    // Job & Fault Ticket deletion - Admin only
    Route::middleware('role:admin')->group(function () {
        Route::delete('faults/{faultTicket}', [FaultTicketController::class, 'destroy'])->name('faults.destroy');
        Route::delete('jobs/{job}', [JobController::class, 'destroy'])->name('jobs.destroy');
        Route::delete('jobs/{job}/items/{item}', [JobItemController::class, 'destroy'])
            ->name('jobs.items.destroy');
        Route::delete('jobs/{job}/payments/{payment}', [PaymentController::class, 'destroy'])
            ->name('jobs.payments.destroy');
    });

    // Customers - Admin, Manager, Moto Mechanic, AC Mechanic
    Route::middleware('role:admin,manager,moto_mechanic,ac_mechanic')->group(function () {
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

    // Leads - Admin, Manager, Moto Mechanic, AC Mechanic
    Route::middleware('role:admin,manager,moto_mechanic,ac_mechanic')->group(function () {
        Route::get('leads', [LeadController::class, 'index'])->name('leads.index');
        Route::get('leads/create', [LeadController::class, 'create'])->name('leads.create');
        Route::post('leads', [LeadController::class, 'store'])->name('leads.store');

        // Bulk action must come before {lead} wildcard
        Route::post('leads/bulk-action', [LeadController::class, 'bulkAction'])->name('leads.bulk-action');

        Route::get('leads/{lead}', [LeadController::class, 'show'])->name('leads.show');
        Route::get('leads/{lead}/edit', [LeadController::class, 'edit'])->name('leads.edit');
        Route::patch('leads/{lead}', [LeadController::class, 'update'])->name('leads.update');

        // Archive / Unarchive
        Route::post('leads/{lead}/archive', [LeadController::class, 'archive'])->name('leads.archive');
        Route::post('leads/{lead}/unarchive', [LeadController::class, 'unarchive'])->name('leads.unarchive');

        // Lead conversion to customer
        Route::post('leads/{lead}/convert', [LeadController::class, 'convertToCustomer'])->name('leads.convert');

        // Lead interactions
        Route::post('leads/{lead}/interactions', [LeadController::class, 'recordInteraction'])->name('leads.interactions.store');

        // Mark lead as lost
        Route::post('leads/{lead}/mark-as-lost', [LeadController::class, 'markAsLost'])->name('leads.mark-as-lost');

        // Quick status update
        Route::patch('leads/{lead}/update-status', [LeadController::class, 'updateStatus'])->name('leads.update-status');

        // Reopen lost lead
        Route::post('leads/{lead}/reopen', [LeadController::class, 'reopen'])->name('leads.reopen');
    });

    // Lead deletion - Admin only
    Route::middleware('role:admin')->group(function () {
        Route::delete('leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy');
    });

    // Fault Tickets - All authenticated operations users
    Route::middleware('operations')->group(function () {
        Route::get('faults', [FaultTicketController::class, 'index'])->name('faults.index');
        Route::get('faults/create', [FaultTicketController::class, 'create'])->name('faults.create');
        Route::post('faults', [FaultTicketController::class, 'store'])->name('faults.store');
        Route::get('faults/customer-jobs/{customer}', [FaultTicketController::class, 'customerJobs'])->name('faults.customer-jobs');
        Route::get('faults/{faultTicket}', [FaultTicketController::class, 'show'])->name('faults.show');
        Route::patch('faults/{faultTicket}', [FaultTicketController::class, 'update'])->name('faults.update');
    });

    // Petty Cash History - Operations users only
    Route::middleware('operations')->group(function () {
        Route::get('petty-cash/history', [PettyCashController::class, 'history'])->name('petty-cash.history');
    });

    // Petty Cash - Admin, Manager, Moto Mechanic, AC Mechanic can view and create
    Route::middleware('role:admin,manager,moto_mechanic,ac_mechanic')->group(function () {
        Route::get('petty-cash', [PettyCashController::class, 'index'])->name('petty-cash.index');
        Route::get('petty-cash/create', [PettyCashController::class, 'create'])->name('petty-cash.create');
        Route::post('petty-cash', [PettyCashController::class, 'store'])->name('petty-cash.store');
    });

    // Petty Cash Approval - Admin only
    Route::middleware('role:admin')->group(function () {
        Route::post('petty-cash/{pettyCash}/approve', [PettyCashController::class, 'approve'])
            ->name('petty-cash.approve');
        Route::post('petty-cash/{pettyCash}/reject', [PettyCashController::class, 'reject'])
            ->name('petty-cash.reject');
    });

    // Petty Cash Admin Dashboard - Admin only
    Route::middleware('role:admin')->group(function () {
        Route::get('petty-cash/admin', [PettyCashController::class, 'adminDashboard'])
            ->name('petty-cash.admin-dashboard');
        Route::get('petty-cash/admin/users/{user}/top-up', [PettyCashController::class, 'showTopUpForm'])
            ->name('petty-cash.show-top-up-form');
        Route::post('petty-cash/admin/users/{user}/top-up', [PettyCashController::class, 'topUpUser'])
            ->name('petty-cash.top-up-user');
        Route::get('petty-cash/admin/users/{user}/history', [PettyCashController::class, 'userHistory'])
            ->name('petty-cash.user-history');
    });

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
        Route::get('inventory/{inventory}/purchase', [InventoryPurchaseController::class, 'create'])
            ->name('inventory.purchases.create');
        Route::post('inventory/{inventory}/purchase', [InventoryPurchaseController::class, 'store'])
            ->name('inventory.purchases.store');
        Route::resource('inventory-categories', InventoryCategoryController::class);
    });

    // Inventory deletion - Admin only
    Route::middleware('role:admin')->group(function () {
        Route::delete('inventory/{inventory}', [InventoryController::class, 'destroy'])->name('inventory.destroy');
    });

    // Finance & P&L - Admin, Manager only
    Route::middleware('role:admin,manager')->group(function () {
        Route::get('accounts', [AccountController::class, 'index'])->name('accounts.index');
        Route::get('accounts/logs', [AccountController::class, 'logs'])->name('accounts.logs');
        Route::get('accounts/create', [AccountController::class, 'create'])->name('accounts.create');
        Route::post('accounts', [AccountController::class, 'store'])->name('accounts.store');
        Route::get('accounts/{account}', [AccountController::class, 'show'])->name('accounts.show');
        Route::get('accounts/{account}/edit', [AccountController::class, 'edit'])->name('accounts.edit');
        Route::patch('accounts/{account}', [AccountController::class, 'update'])->name('accounts.update');
        Route::post('accounts/{account}/adjust', [AccountController::class, 'adjust'])->name('accounts.adjust');

        Route::get('account-transfers', [AccountTransferController::class, 'index'])->name('accounts.transfers.index');
        Route::get('account-transfers/create', [AccountTransferController::class, 'create'])->name('accounts.transfers.create');
        Route::post('account-transfers', [AccountTransferController::class, 'store'])->name('accounts.transfers.store');

        Route::get('expenses', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::get('expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
        Route::get('expenses/create-cogs', [ExpenseController::class, 'createCogs'])->name('expenses.create-cogs');
        Route::get('expenses/create-operating', [ExpenseController::class, 'createOperating'])->name('expenses.create-operating');
        Route::post('expenses', [ExpenseController::class, 'store'])->name('expenses.store');
        Route::get('expenses/{expense}', [ExpenseController::class, 'show'])->name('expenses.show');
        Route::get('expenses/{expense}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit');
        Route::patch('expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');

        Route::get('recurring-expenses', [RecurringExpenseController::class, 'index'])->name('recurring-expenses.index');
        Route::get('recurring-expenses/create', [RecurringExpenseController::class, 'create'])->name('recurring-expenses.create');
        Route::post('recurring-expenses', [RecurringExpenseController::class, 'store'])->name('recurring-expenses.store');
        Route::get('recurring-expenses/{recurringExpense}/edit', [RecurringExpenseController::class, 'edit'])->name('recurring-expenses.edit');
        Route::patch('recurring-expenses/{recurringExpense}', [RecurringExpenseController::class, 'update'])->name('recurring-expenses.update');
        Route::post('recurring-expenses/generate', [RecurringExpenseController::class, 'generate'])->name('recurring-expenses.generate');

        Route::get('vendors', [VendorController::class, 'index'])->name('vendors.index');
        Route::get('vendors/create', [VendorController::class, 'create'])->name('vendors.create');
        Route::post('vendors', [VendorController::class, 'store'])->name('vendors.store');
        Route::get('vendors/{vendor}/edit', [VendorController::class, 'edit'])->name('vendors.edit');
        Route::patch('vendors/{vendor}', [VendorController::class, 'update'])->name('vendors.update');

        Route::get('expense-categories', [ExpenseCategoryController::class, 'index'])->name('expense-categories.index');
        Route::get('expense-categories/create', [ExpenseCategoryController::class, 'create'])->name('expense-categories.create');
        Route::post('expense-categories', [ExpenseCategoryController::class, 'store'])->name('expense-categories.store');
        Route::get('expense-categories/{expenseCategory}/edit', [ExpenseCategoryController::class, 'edit'])->name('expense-categories.edit');
        Route::patch('expense-categories/{expenseCategory}', [ExpenseCategoryController::class, 'update'])->name('expense-categories.update');

        Route::get('reports/pnl', [PnLController::class, 'index'])->name('reports.pnl');
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

// Rattehin - Bill Splitting App (Redirects to register if not authenticated)
Route::prefix('rattehin')->name('rattehin.')->middleware('auth.register')->group(function () {
    Route::get('/', [RattehinController::class, 'index'])->name('index');
    Route::get('/create', [RattehinController::class, 'create'])->name('create');
    Route::post('/', [RattehinController::class, 'store'])->name('store');
    Route::get('/{bill}', [RattehinController::class, 'show'])->name('show');
    Route::get('/{bill}/edit', [RattehinController::class, 'edit'])->name('edit');
    Route::patch('/{bill}', [RattehinController::class, 'update'])->name('update');
    Route::delete('/{bill}', [RattehinController::class, 'destroy'])->name('destroy');

    // OCR bill scanning endpoint
    Route::post('/scan', [RattehinController::class, 'scanBill'])->name('scan');
});

require __DIR__.'/auth.php';
