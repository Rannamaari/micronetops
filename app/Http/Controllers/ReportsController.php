<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
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

        $today = now();
        $monthStart = $today->copy()->startOfMonth();
        $monthEnd = $today->copy()->endOfMonth();
        $weekStart = $today->copy()->startOfWeek();
        $weekEnd = $today->copy()->endOfWeek();

        $monthRevenue = (float) Job::query()
            ->where('status', Job::STATUS_COMPLETED)
            ->where(function ($query) use ($monthStart, $monthEnd) {
                $query->whereBetween('completed_at', [$monthStart, $monthEnd])
                    ->orWhere(function ($q) use ($monthStart, $monthEnd) {
                        $q->whereNull('completed_at')
                            ->whereBetween('job_date', [$monthStart->toDateString(), $monthEnd->toDateString()]);
                    });
            })
            ->sum('total_amount');

        $monthExpenses = (float) Expense::query()
            ->whereBetween('incurred_at', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->sum('amount');

        $weekExpenses = (float) Expense::query()
            ->whereBetween('incurred_at', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->sum('amount');

        $expenseEntriesThisMonth = (int) Expense::query()
            ->whereBetween('incurred_at', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->count();

        $lowStockCount = (int) InventoryItem::query()
            ->where('is_service', false)
            ->where('is_active', true)
            ->whereRaw('quantity <= low_stock_limit')
            ->count();

        $rwExpiringSoonCount = (int) Vehicle::query()
            ->whereNotNull('road_worthiness_expires_at')
            ->where('road_worthiness_expires_at', '>=', $today->copy()->startOfDay()->toDateTimeString())
            ->where('road_worthiness_expires_at', '<=', $today->copy()->addDays(30)->endOfDay()->toDateTimeString())
            ->count();

        $expenseTypeBreakdown = Expense::query()
            ->select('expense_categories.type', DB::raw('SUM(expenses.amount) as total'), DB::raw('COUNT(*) as entries'))
            ->join('expense_categories', 'expense_categories.id', '=', 'expenses.expense_category_id')
            ->whereBetween('expenses.incurred_at', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->groupBy('expense_categories.type')
            ->get()
            ->keyBy('type');

        $reportGroups = [
            [
                'title' => 'Financial',
                'description' => 'Share-ready financial views for month-end and weekly reviews.',
                'reports' => [
                    [
                        'title' => 'Expense Dashboard',
                        'description' => 'Full expense reporting with filters, month-wise totals, vendors, accounts, and category summaries.',
                        'route' => route('expenses.reports'),
                        'badge' => 'New',
                        'theme' => 'blue',
                        'links' => [
                            ['label' => 'Today', 'url' => route('expenses.reports', ['period' => 'today'])],
                            ['label' => 'This Week', 'url' => route('expenses.reports', ['period' => 'week'])],
                            ['label' => 'This Month', 'url' => route('expenses.reports', ['period' => 'month'])],
                            ['label' => 'This Year', 'url' => route('expenses.reports', ['period' => 'year'])],
                        ],
                    ],
                    [
                        'title' => 'Profit & Loss',
                        'description' => 'Accrual summary by month, quarter, or year for owner and stakeholder review.',
                        'route' => route('reports.pnl'),
                        'badge' => 'Core',
                        'theme' => 'teal',
                        'links' => [
                            ['label' => 'Month', 'url' => route('reports.pnl', ['period' => 'month'])],
                            ['label' => 'Quarter', 'url' => route('reports.pnl', ['period' => 'quarter'])],
                            ['label' => 'Year', 'url' => route('reports.pnl', ['period' => 'year'])],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Sales & Operations',
                'description' => 'Daily monitoring and performance snapshots for operational follow-up.',
                'reports' => [
                    [
                        'title' => 'Daily Sales',
                        'description' => 'Review daily completed jobs and sales totals with revenue breakdowns.',
                        'route' => route('reports.daily-sales'),
                        'badge' => 'Operations',
                        'theme' => 'green',
                        'links' => [
                            ['label' => 'Today', 'url' => route('reports.daily-sales', ['date' => $today->toDateString()])],
                            ['label' => 'Yesterday', 'url' => route('reports.daily-sales', ['date' => $today->copy()->subDay()->toDateString()])],
                        ],
                    ],
                    [
                        'title' => 'Sales Trends',
                        'description' => 'Understand sales movement over the day, week, and month.',
                        'route' => route('reports.sales-trends'),
                        'badge' => 'Trend',
                        'theme' => 'yellow',
                        'links' => [
                            ['label' => '24 Hours', 'url' => route('reports.sales-trends', ['view' => 'day'])],
                            ['label' => '7 Days', 'url' => route('reports.sales-trends', ['view' => 'week'])],
                            ['label' => '30 Days', 'url' => route('reports.sales-trends', ['view' => 'month'])],
                        ],
                    ],
                    [
                        'title' => 'Best Sellers',
                        'description' => 'See which items and services are driving demand.',
                        'route' => route('reports.best-sellers'),
                        'badge' => 'Sales',
                        'theme' => 'purple',
                        'links' => [
                            ['label' => 'This Month', 'url' => route('reports.best-sellers', ['period' => 'month'])],
                            ['label' => 'This Week', 'url' => route('reports.best-sellers', ['period' => 'week'])],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Assets & Compliance',
                'description' => 'Inventory, tools, and compliance reports for control and planning.',
                'reports' => [
                    [
                        'title' => 'Low Inventory',
                        'description' => 'Highlight items that need replenishment soon.',
                        'route' => route('reports.low-inventory'),
                        'badge' => 'Stock',
                        'theme' => 'red',
                        'links' => [
                            ['label' => 'All', 'url' => route('reports.low-inventory')],
                            ['label' => 'Moto', 'url' => route('reports.low-inventory', ['category' => 'moto'])],
                            ['label' => 'AC', 'url' => route('reports.low-inventory', ['category' => 'ac'])],
                        ],
                    ],
                    [
                        'title' => 'Inventory Overview',
                        'description' => 'Broad inventory view covering items, movements, and totals.',
                        'route' => route('reports.inventory-overview'),
                        'badge' => 'Inventory',
                        'theme' => 'orange',
                        'links' => [
                            ['label' => 'Open', 'url' => route('reports.inventory-overview')],
                        ],
                    ],
                    [
                        'title' => 'Fixed Assets',
                        'description' => 'Track tools currently with staff and custody history.',
                        'route' => route('reports.fixed-assets.current-custody'),
                        'badge' => 'Assets',
                        'theme' => 'slate',
                        'links' => [
                            ['label' => 'Current Custody', 'url' => route('reports.fixed-assets.current-custody')],
                        ],
                    ],
                    [
                        'title' => 'Road Worthiness',
                        'description' => 'Monitor vehicle road worthiness expirations and newly issued records.',
                        'route' => route('reports.road-worthiness'),
                        'badge' => 'Compliance',
                        'theme' => 'indigo',
                        'links' => [
                            ['label' => 'This Month', 'url' => route('reports.road-worthiness', ['month' => $today->format('Y-m')])],
                        ],
                    ],
                ],
            ],
        ];

        return view('reports.index', compact(
            'monthRevenue',
            'monthExpenses',
            'weekExpenses',
            'expenseEntriesThisMonth',
            'lowStockCount',
            'rwExpiringSoonCount',
            'expenseTypeBreakdown',
            'reportGroups'
        ));
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
