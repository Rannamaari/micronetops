<?php

use App\Http\Controllers\Api\InventoryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| OpenClaw Bot API Routes
|--------------------------------------------------------------------------
| Protected by a static bearer token (OPENCLAW_API_TOKEN in .env).
| All routes here are stateless (no session / CSRF).
*/

Route::middleware('api.token')->prefix('inventory')->group(function () {
    Route::get('/',        [InventoryController::class, 'index']);
    Route::get('/search',  [InventoryController::class, 'search']);
    Route::post('/update', [InventoryController::class, 'update']);
});
