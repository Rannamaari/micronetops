<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\Job;
use App\Models\JobItem;
use App\Models\Payment;
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

        // Jobs this week
        $jobsThisWeek = Job::where('total_amount', '>', 0)
            ->where('created_at', '>=', Job::formatCreatedAtForQuery($startOfWeek))
            ->count();

        // Jobs this month
        $jobsThisMonth = Job::where('total_amount', '>', 0)
            ->where('created_at', '>=', Job::formatCreatedAtForQuery($startOfMonth))
            ->count();

        // Sales today
        $salesToday = Payment::whereBetween('created_at', [$startOfDay, $endOfDay])
            ->sum('amount');

        // Sales this month
        $salesThisMonth = Payment::where('created_at', '>=', $startOfMonth)
            ->sum('amount');

        // Sales this month - AC
        $salesThisMonthAC = Payment::where('payments.created_at', '>=', $startOfMonth)
            ->join('jobs', 'payments.job_id', '=', 'jobs.id')
            ->where('jobs.job_type', 'ac')
            ->sum('payments.amount');

        // Sales this month - Moto
        $salesThisMonthMoto = Payment::where('payments.created_at', '>=', $startOfMonth)
            ->join('jobs', 'payments.job_id', '=', 'jobs.id')
            ->where('jobs.job_type', 'moto')
            ->sum('payments.amount');

        // Total inventory items
        $totalInventoryItems = InventoryItem::active()
            ->where('is_service', false)
            ->count();

        // Low stock items
        $lowStockItems = InventoryItem::active()
            ->where('is_service', false)
            ->whereColumn('quantity', '<=', 'low_stock_limit')
            ->count();

        // Petty cash balance
        $pettyCashBalance = PettyCash::currentBalance();

        // === DAILY SALES GRAPH (Last 10 days) ===
        $dailySalesData = $this->getDailySalesData();

        // === MONTHLY TRENDS (Last 6 months) ===
        $monthlyTrendsData = $this->getMonthlyTrendsData();

        // === BEST SELLING ITEMS & SERVICES ===
        $bestSellingData = $this->getBestSellingData();

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
            'pettyCashBalance',
            'dailySalesData',
            'monthlyTrendsData',
            'bestSellingData'
        ));
    }

    private function getDailySalesData()
    {
        $days = [];
        $sales = [];

        for ($i = 9; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $startOfDay = $date->copy()->startOfDay();
            $endOfDay = $date->copy()->endOfDay();

            $dailySales = Payment::whereBetween('created_at', [$startOfDay, $endOfDay])
                ->sum('amount');

            $days[] = $date->format('M d');
            $sales[] = (float) $dailySales;
        }

        return [
            'labels' => $days,
            'data' => $sales,
        ];
    }

    private function getMonthlyTrendsData()
    {
        $months = [];
        $acSales = [];
        $motoSales = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();

            // AC Sales
            $acMonthlySales = Payment::whereBetween('payments.created_at', [$startOfMonth, $endOfMonth])
                ->join('jobs', 'payments.job_id', '=', 'jobs.id')
                ->where('jobs.job_type', 'ac')
                ->sum('payments.amount');

            // Moto Sales
            $motoMonthlySales = Payment::whereBetween('payments.created_at', [$startOfMonth, $endOfMonth])
                ->join('jobs', 'payments.job_id', '=', 'jobs.id')
                ->where('jobs.job_type', 'moto')
                ->sum('payments.amount');

            $months[] = $date->format('M Y');
            $acSales[] = (float) $acMonthlySales;
            $motoSales[] = (float) $motoMonthlySales;
        }

        return [
            'labels' => $months,
            'acData' => $acSales,
            'motoData' => $motoSales,
        ];
    }

    private function getBestSellingData()
    {
        $thirtyDaysAgo = Carbon::now()->subDays(30);

        // Best selling items for AC jobs (last 30 days)
        $acItems = JobItem::select('inventory_item_id', DB::raw('SUM(job_items.quantity) as total_quantity'))
            ->join('jobs', 'job_items.job_id', '=', 'jobs.id')
            ->join('inventory_items', 'job_items.inventory_item_id', '=', 'inventory_items.id')
            ->where('jobs.job_type', 'ac')
            ->where('inventory_items.is_service', false)
            ->whereRaw('jobs.created_at >= ?::timestamp', [$thirtyDaysAgo])
            ->groupBy('inventory_item_id')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->with('inventoryItem')
            ->get();

        // Best selling services for AC jobs (last 30 days)
        $acServices = JobItem::select('inventory_item_id', DB::raw('SUM(job_items.quantity) as total_quantity'))
            ->join('jobs', 'job_items.job_id', '=', 'jobs.id')
            ->join('inventory_items', 'job_items.inventory_item_id', '=', 'inventory_items.id')
            ->where('jobs.job_type', 'ac')
            ->where('inventory_items.is_service', true)
            ->whereRaw('jobs.created_at >= ?::timestamp', [$thirtyDaysAgo])
            ->groupBy('inventory_item_id')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->with('inventoryItem')
            ->get();

        // Best selling items for Moto jobs (last 30 days)
        $motoItems = JobItem::select('inventory_item_id', DB::raw('SUM(job_items.quantity) as total_quantity'))
            ->join('jobs', 'job_items.job_id', '=', 'jobs.id')
            ->join('inventory_items', 'job_items.inventory_item_id', '=', 'inventory_items.id')
            ->where('jobs.job_type', 'moto')
            ->where('inventory_items.is_service', false)
            ->whereRaw('jobs.created_at >= ?::timestamp', [$thirtyDaysAgo])
            ->groupBy('inventory_item_id')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->with('inventoryItem')
            ->get();

        // Best selling services for Moto jobs (last 30 days)
        $motoServices = JobItem::select('inventory_item_id', DB::raw('SUM(job_items.quantity) as total_quantity'))
            ->join('jobs', 'job_items.job_id', '=', 'jobs.id')
            ->join('inventory_items', 'job_items.inventory_item_id', '=', 'inventory_items.id')
            ->where('jobs.job_type', 'moto')
            ->where('inventory_items.is_service', true)
            ->whereRaw('jobs.created_at >= ?::timestamp', [$thirtyDaysAgo])
            ->groupBy('inventory_item_id')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->with('inventoryItem')
            ->get();

        return [
            'acItems' => $acItems,
            'acServices' => $acServices,
            'motoItems' => $motoItems,
            'motoServices' => $motoServices,
        ];
    }
}
