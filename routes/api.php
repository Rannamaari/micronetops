<?php

use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\FaultTicketController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\SalesController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| OpenClaw Bot API Routes
|--------------------------------------------------------------------------
| Protected by a static bearer token (OPENCLAW_API_TOKEN in .env).
| All routes here are stateless (no session / CSRF).
*/

Route::middleware('api.token')->group(function () {

    // --- Dashboard ---
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // --- Inventory ---
    Route::prefix('inventory')->group(function () {
        Route::get('/',          [InventoryController::class, 'index']);
        Route::post('/',         [InventoryController::class, 'store']);
        Route::get('/search',    [InventoryController::class, 'search']);
        Route::post('/update',   [InventoryController::class, 'update']);
        Route::delete('/{id}',   [InventoryController::class, 'destroy']);
    });

    // --- Customers ---
    Route::prefix('customers')->group(function () {
        Route::get('/search',  [CustomerController::class, 'search']);
        Route::post('/',       [CustomerController::class, 'store']);
    });

    // --- Sales (Micro Moto / Micro Cool) ---
    Route::prefix('sales')->group(function () {
        Route::get('/',           [SalesController::class, 'index']);
        Route::post('/',          [SalesController::class, 'store']);
        Route::get('/today',      [SalesController::class, 'today']);
        Route::delete('/{id}',    [SalesController::class, 'destroy']);
    });

    // --- Fault Tickets ---
    Route::prefix('faults')->group(function () {
        Route::get('/',             [FaultTicketController::class, 'index']);
        Route::post('/',            [FaultTicketController::class, 'store']);
        Route::get('/{id}',         [FaultTicketController::class, 'show']);
        Route::patch('/{id}/assign',[FaultTicketController::class, 'assign']);
        Route::patch('/{id}/resolve',[FaultTicketController::class, 'resolve']);
        Route::patch('/{id}/close', [FaultTicketController::class, 'close']);
    });

    // --- Leads ---
    Route::prefix('leads')->group(function () {
        Route::post('/',         [LeadController::class, 'store']);
        Route::get('/pending',   [LeadController::class, 'pending']);
    });

    // --- Expenses (Petty Cash) ---
    Route::prefix('expenses')->group(function () {
        Route::post('/',       [ExpenseController::class, 'store']);
        Route::get('/pending', [ExpenseController::class, 'pending']);
    });
});
