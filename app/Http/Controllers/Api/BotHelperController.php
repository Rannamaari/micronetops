<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class BotHelperController extends Controller
{
    /**
     * GET /api/bot/bootstrap
     * Returns common lookup data (IDs + names) for bot integrations.
     * Protected by api.token middleware (static bearer token).
     */
    public function bootstrap(): JsonResponse
    {
        $ttlSeconds = 300; // 5 minutes

        $categories = Cache::remember('bot:expense_categories:v1', $ttlSeconds, function () {
            return ExpenseCategory::where('is_active', true)
                ->orderBy('type')
                ->orderBy('name')
                ->get(['id', 'name', 'type']);
        });

        $vendors = Cache::remember('bot:vendors:v1', $ttlSeconds, function () {
            return Vendor::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'phone', 'contact_name', 'address']);
        });

        $accounts = Cache::remember('bot:accounts:v1', $ttlSeconds, function () {
            return Account::where('is_active', true)
                ->where('is_system', false)
                ->orderBy('name')
                ->get(['id', 'name', 'balance']);
        });

        return response()->json([
            'as_of' => now()->format('Y-m-d H:i:s'),
            'business_units' => Expense::getBusinessUnits(),
            'expense_category_types' => ExpenseCategory::getTypes(),
            'expense_categories' => $categories,
            'vendors' => $vendors,
            'accounts' => $accounts->map(fn ($a) => [
                'id' => $a->id,
                'name' => $a->name,
                'balance' => number_format((float) $a->balance, 2),
            ]),
        ]);
    }
}

