<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\Job;
use App\Models\Payment;
use App\Models\PettyCash;
use Carbon\Carbon;

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
            ->where('created_at', '>=', Job::formatCreatedAtForQuery($startOfWeek))
            ->count();

        // Jobs this month (with sales - created this month)
        $jobsThisMonth = Job::where('total_amount', '>', 0)
            ->where('created_at', '>=', Job::formatCreatedAtForQuery($startOfMonth))
            ->count();

        // Sales today (payments received today)
        $salesToday = Payment::whereBetween('created_at', [$startOfDay, $endOfDay])
            ->sum('amount');

        // Sales this month (payments received this month)
        $salesThisMonth = Payment::where('created_at', '>=', $startOfMonth)
            ->sum('amount');

        // Sales this month - AC jobs (payments for AC jobs)
        $salesThisMonthAC = Payment::where('payments.created_at', '>=', $startOfMonth)
            ->join('jobs', 'payments.job_id', '=', 'jobs.id')
            ->where('jobs.job_type', 'ac')
            ->sum('payments.amount');

        // Sales this month - Moto jobs (payments for Moto jobs)
        $salesThisMonthMoto = Payment::where('payments.created_at', '>=', $startOfMonth)
            ->join('jobs', 'payments.job_id', '=', 'jobs.id')
            ->where('jobs.job_type', 'moto')
            ->sum('payments.amount');

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
