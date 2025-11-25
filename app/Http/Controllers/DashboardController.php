<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\Job;
use App\Models\PettyCash;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();
        $startOfWeek = $now->copy()->startOfWeek();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfDay = $now->copy()->startOfDay();
        $endOfDay = $now->copy()->endOfDay();

        // Total customers
        $totalCustomers = Customer::count();

        // Jobs this week (with sales - created this week)
        $jobsThisWeek = Job::where('total_amount', '>', 0)
            ->where('created_at', '>=', $startOfWeek)
            ->count();

        // Jobs this month (with sales - created this month)
        $jobsThisMonth = Job::where('total_amount', '>', 0)
            ->where('created_at', '>=', $startOfMonth)
            ->count();

        // Sales today (jobs created today with total_amount > 0)
        $salesToday = Job::where('total_amount', '>', 0)
            ->where('created_at', '>=', $startOfDay)
            ->where('created_at', '<=', $endOfDay)
            ->sum('total_amount');

        // Sales this month (jobs created this month with total_amount > 0)
        $salesThisMonth = Job::where('total_amount', '>', 0)
            ->where('created_at', '>=', $startOfMonth)
            ->sum('total_amount');

        // Sales this month - AC jobs
        $salesThisMonthAC = Job::where('total_amount', '>', 0)
            ->where('job_type', 'ac')
            ->where('created_at', '>=', $startOfMonth)
            ->sum('total_amount');

        // Sales this month - Moto jobs
        $salesThisMonthMoto = Job::where('total_amount', '>', 0)
            ->where('job_type', 'moto')
            ->where('created_at', '>=', $startOfMonth)
            ->sum('total_amount');

        // Total inventory items (active, non-service)
        $totalInventoryItems = InventoryItem::active()
            ->where('is_service', false)
            ->count();

        // Low stock items (active, non-service, quantity <= low_stock_limit)
        $lowStockItems = InventoryItem::active()
            ->where('is_service', false)
            ->whereColumn('quantity', '<=', 'low_stock_limit')
            ->count();

        // Petty cash balance
        $pettyCashBalance = PettyCash::currentBalance();

        return view('dashboard', compact(
            'totalCustomers',
            'jobsThisWeek',
            'jobsThisMonth',
            'salesToday',
            'salesThisMonth',
            'salesThisMonthAC',
            'salesThisMonthMoto',
            'totalInventoryItems',
            'lowStockItems',
            'pettyCashBalance'
        ));
    }
}
