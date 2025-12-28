<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobItem;
use App\Models\InventoryItem;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ReportsController extends Controller
{
    /**
     * Reports dashboard/index
     */
    public function index()
    {
        if (!Gate::allows('view-reports')) {
            abort(403, 'Unauthorized. You do not have permission to view reports.');
        }

        return view('reports.index');
    }

    /**
     * Daily Sales Report
     */
    public function dailySales(Request $request)
    {
        if (!Gate::allows('view-reports')) {
            abort(403, 'Unauthorized. You do not have permission to view reports.');
        }

        $date = $request->query('date', now()->format('Y-m-d'));
        $startOfDay = \Carbon\Carbon::parse($date)->startOfDay();
        $endOfDay = \Carbon\Carbon::parse($date)->endOfDay();

        // Use the Job model's helper method to format datetime for query
        $startValue = Job::formatCreatedAtForQuery($startOfDay);
        $endValue = Job::formatCreatedAtForQuery($endOfDay);

        $jobs = Job::with(['customer', 'vehicle', 'acUnit', 'items.inventoryItem'])
            ->whereBetween('created_at', [$startValue, $endValue])
            ->orderBy('created_at', 'desc')
            ->get();

        $totalRevenue = $jobs->sum('total_amount');
        $motoRevenue = $jobs->where('job_type', 'moto')->sum('total_amount');
        $acRevenue = $jobs->where('job_type', 'ac')->sum('total_amount');

        return view('reports.daily-sales', compact(
            'jobs',
            'date',
            'totalRevenue',
            'motoRevenue',
            'acRevenue'
        ));
    }

    /**
     * Best Sellers Report
     */
    public function bestSellers(Request $request)
    {
        if (!Gate::allows('view-reports')) {
            abort(403, 'Unauthorized. You do not have permission to view reports.');
        }

        $period = $request->query('period', 'month'); // day, week, month, all
        $category = $request->query('category', 'all'); // moto, ac, all

        $query = JobItem::with('inventoryItem')
            ->select('inventory_item_id',
                    DB::raw('SUM(quantity) as total_quantity'),
                    DB::raw('SUM(subtotal) as total_revenue'),
                    DB::raw('COUNT(*) as order_count'))
            ->groupBy('inventory_item_id');

        // Apply time period filter using Carbon datetime objects
        if ($period === 'day') {
            $start = now()->startOfDay();
            $startValue = Job::formatCreatedAtForQuery($start);
            $query->whereHas('job', function($q) use ($startValue) {
                $q->where('created_at', '>=', $startValue);
            });
        } elseif ($period === 'week') {
            $start = now()->startOfWeek();
            $startValue = Job::formatCreatedAtForQuery($start);
            $query->whereHas('job', function($q) use ($startValue) {
                $q->where('created_at', '>=', $startValue);
            });
        } elseif ($period === 'month') {
            $start = now()->startOfMonth();
            $startValue = Job::formatCreatedAtForQuery($start);
            $query->whereHas('job', function($q) use ($startValue) {
                $q->where('created_at', '>=', $startValue);
            });
        }

        // Apply category filter
        if ($category !== 'all') {
            $query->whereHas('inventoryItem', function($q) use ($category) {
                $q->where('category', $category);
            });
        }

        $bestSellers = $query->orderBy('total_revenue', 'desc')
            ->limit(50)
            ->get();

        return view('reports.best-sellers', compact('bestSellers', 'period', 'category'));
    }

    /**
     * Low Inventory Report
     */
    public function lowInventory(Request $request)
    {
        if (!Gate::allows('view-reports')) {
            abort(403, 'Unauthorized. You do not have permission to view reports.');
        }

        $category = $request->query('category', 'all'); // moto, ac, all

        $query = InventoryItem::where('is_service', false)
            ->where('is_active', true)
            ->whereRaw('quantity <= low_stock_limit');

        if ($category !== 'all') {
            $query->where('category', $category);
        }

        $lowStockItems = $query->orderBy('quantity', 'asc')->get();

        $motoCount = InventoryItem::where('is_service', false)
            ->where('category', 'moto')
            ->whereRaw('quantity <= low_stock_limit')
            ->count();

        $acCount = InventoryItem::where('is_service', false)
            ->where('category', 'ac')
            ->whereRaw('quantity <= low_stock_limit')
            ->count();

        return view('reports.low-inventory', compact('lowStockItems', 'category', 'motoCount', 'acCount'));
    }

    /**
     * Sales Trends Report (Per Day/Week/Month)
     */
    public function salesTrends(Request $request)
    {
        if (!Gate::allows('view-reports')) {
            abort(403, 'Unauthorized. You do not have permission to view reports.');
        }

        $view = $request->query('view', 'week'); // day, week, month
        $jobType = $request->query('type', 'all'); // moto, ac, all

        if ($view === 'day') {
            // Last 24 hours by hour
            $salesData = $this->getSalesPerHour($jobType);
        } elseif ($view === 'week') {
            // Last 7 days
            $salesData = $this->getSalesPerDay(7, $jobType);
        } else {
            // Last 30 days
            $salesData = $this->getSalesPerDay(30, $jobType);
        }

        return view('reports.sales-trends', compact('salesData', 'view', 'jobType'));
    }

    private function getSalesPerHour($jobType)
    {
        $data = [];
        $now = now();

        for ($i = 23; $i >= 0; $i--) {
            $hour = $now->copy()->subHours($i);
            $startOfHour = $hour->copy()->startOfHour();
            $endOfHour = $hour->copy()->endOfHour();

            $startValue = Job::formatCreatedAtForQuery($startOfHour);
            $endValue = Job::formatCreatedAtForQuery($endOfHour);

            $query = Job::whereBetween('created_at', [$startValue, $endValue]);

            if ($jobType !== 'all') {
                $query->where('job_type', $jobType);
            }

            $data[] = [
                'label' => $hour->format('H:00'),
                'total' => $query->sum('total_amount'),
                'count' => $query->count(),
            ];
        }

        return $data;
    }

    private function getSalesPerDay($days, $jobType)
    {
        $data = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $startOfDay = $day->copy()->startOfDay();
            $endOfDay = $day->copy()->endOfDay();

            $startValue = Job::formatCreatedAtForQuery($startOfDay);
            $endValue = Job::formatCreatedAtForQuery($endOfDay);

            $query = Job::whereBetween('created_at', [$startValue, $endValue]);

            if ($jobType !== 'all') {
                $query->where('job_type', $jobType);
            }

            $data[] = [
                'label' => $day->format('M d'),
                'total' => $query->sum('total_amount'),
                'count' => $query->count(),
            ];
        }

        return $data;
    }

    /**
     * Inventory Overview Report
     */
    public function inventoryOverview(Request $request)
    {
        if (!Gate::allows('view-reports')) {
            abort(403, 'Unauthorized. You do not have permission to view reports.');
        }

        $category = $request->query('category', 'all'); // moto, ac, all
        $type = $request->query('type', 'all'); // parts, services, all
        $status = $request->query('status', 'all'); // low_stock, in_stock, all

        // Build query for items
        $itemsQuery = InventoryItem::with(['inventoryCategory', 'logs' => function($query) {
            $query->latest()->limit(5);
        }])->where('is_active', true);

        if ($category !== 'all') {
            $itemsQuery->where('category', $category);
        }

        if ($type === 'parts') {
            $itemsQuery->where('is_service', false);
        } elseif ($type === 'services') {
            $itemsQuery->where('is_service', true);
        }

        if ($status === 'low_stock') {
            $itemsQuery->whereRaw('quantity <= low_stock_limit')->where('is_service', false);
        } elseif ($status === 'in_stock') {
            $itemsQuery->whereRaw('quantity > low_stock_limit')->where('is_service', false);
        }

        $items = $itemsQuery->orderBy('name')->get();

        // Calculate summary statistics
        $totalItems = $items->count();
        $totalValue = $items->sum(function($item) {
            return $item->quantity * $item->cost_price;
        });
        $lowStockCount = $items->filter(function($item) {
            return $item->isLowStock();
        })->count();

        // Get recent movements (last 50)
        $recentMovements = \App\Models\InventoryLog::with(['inventoryItem', 'job', 'user'])
            ->when($category !== 'all', function($query) use ($category) {
                $query->whereHas('inventoryItem', function($q) use ($category) {
                    $q->where('category', $category);
                });
            })
            ->latest()
            ->limit(50)
            ->get();

        return view('reports.inventory-overview', compact(
            'items',
            'category',
            'type',
            'status',
            'totalItems',
            'totalValue',
            'lowStockCount',
            'recentMovements'
        ));
    }
}
