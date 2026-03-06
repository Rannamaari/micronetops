<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\DailySalesLog;
use App\Models\FaultTicket;
use App\Models\InventoryItem;
use App\Models\Job;
use App\Models\JobItem;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\PettyCash;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * GET /api/dashboard
     * Full business snapshot for the OpenClaw bot.
     */
    public function index(): JsonResponse
    {
        $now          = Carbon::now();
        $today        = $now->toDateString();
        $startOfDay   = $now->copy()->startOfDay();
        $endOfDay     = $now->copy()->endOfDay();
        $startOfWeek  = $now->copy()->startOfWeek();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfYear  = $now->copy()->startOfYear();

        return response()->json([
            'as_of'     => $now->format('Y-m-d H:i'),
            'sales'     => $this->sales($startOfDay, $endOfDay, $startOfWeek, $startOfMonth),
            'customers' => $this->customers($startOfMonth),
            'inventory' => $this->inventory(),
            'leads'     => $this->leads($now),
            'faults'    => $this->faults($startOfWeek, $startOfMonth),
            'petty_cash'=> $this->pettyCash(),
            'best_sellers' => $this->bestSellers(),
            'monthly_trend'=> $this->monthlyTrend(),
        ]);
    }

    // -------------------------------------------------------------------------
    private function sales($startOfDay, $endOfDay, $startOfWeek, $startOfMonth): array
    {
        $base = fn($start, $end = null) => Payment::join('jobs', 'payments.job_id', '=', 'jobs.id')
            ->when($end, fn($q) => $q->whereBetween('jobs.job_date', [$start, $end]),
                         fn($q) => $q->where('jobs.job_date', '>=', $start));

        $today      = (float) $base($startOfDay, $endOfDay)->sum('payments.amount');
        $todayMoto  = (float) $base($startOfDay, $endOfDay)->where('jobs.job_type', 'moto')->sum('payments.amount');
        $todayCool  = (float) $base($startOfDay, $endOfDay)->where('jobs.job_type', 'ac')->sum('payments.amount');

        $week       = (float) $base($startOfWeek)->sum('payments.amount');
        $month      = (float) $base($startOfMonth)->sum('payments.amount');
        $monthMoto  = (float) $base($startOfMonth)->where('jobs.job_type', 'moto')->sum('payments.amount');
        $monthCool  = (float) $base($startOfMonth)->where('jobs.job_type', 'ac')->sum('payments.amount');

        $jobsToday  = Job::where('total_amount', '>', 0)->whereBetween('job_date', [$startOfDay, $endOfDay])->count();
        $jobsWeek   = Job::where('total_amount', '>', 0)->where('job_date', '>=', $startOfWeek)->count();
        $jobsMonth  = Job::where('total_amount', '>', 0)->where('job_date', '>=', $startOfMonth)->count();

        // Last 7 days breakdown
        $last7 = [];
        for ($i = 6; $i >= 0; $i--) {
            $d   = Carbon::now()->subDays($i);
            $amt = (float) Payment::join('jobs', 'payments.job_id', '=', 'jobs.id')
                ->whereBetween('jobs.job_date', [$d->copy()->startOfDay(), $d->copy()->endOfDay()])
                ->sum('payments.amount');
            $last7[] = ['date' => $d->format('D d M'), 'total' => $amt];
        }

        return [
            'today'           => ['total' => $today, 'moto' => $todayMoto, 'cool' => $todayCool],
            'this_week'       => ['total' => $week],
            'this_month'      => ['total' => $month, 'moto' => $monthMoto, 'cool' => $monthCool],
            'jobs_today'      => $jobsToday,
            'jobs_this_week'  => $jobsWeek,
            'jobs_this_month' => $jobsMonth,
            'last_7_days'     => $last7,
        ];
    }

    // -------------------------------------------------------------------------
    private function customers($startOfMonth): array
    {
        $total    = Customer::count();
        $newMonth = Customer::where('created_at', '>=', $startOfMonth)->count();
        $moto     = Customer::where('category', 'moto')->count();
        $ac       = Customer::where('category', 'ac')->count();

        // Top 5 customers by revenue (all time)
        $top = Job::select('customer_id', DB::raw('SUM(total_amount) as revenue'))
            ->whereNotNull('customer_id')
            ->groupBy('customer_id')
            ->orderByDesc('revenue')
            ->limit(5)
            ->with('customer:id,name,phone')
            ->get()
            ->map(fn($j) => [
                'name'    => $j->customer?->name ?? 'Unknown',
                'phone'   => $j->customer?->phone,
                'revenue' => (float) $j->revenue,
            ]);

        return [
            'total'          => $total,
            'new_this_month' => $newMonth,
            'micro_moto'     => $moto,
            'micro_cool'     => $ac,
            'top_by_revenue' => $top,
        ];
    }

    // -------------------------------------------------------------------------
    private function inventory(): array
    {
        $total    = InventoryItem::active()->where('is_service', false)->count();
        $lowStock = InventoryItem::active()
            ->where('is_service', false)
            ->where('low_stock_limit', '>', 0)
            ->whereColumn('quantity', '<=', 'low_stock_limit')
            ->get(['name', 'sku', 'quantity', 'low_stock_limit'])
            ->map(fn($i) => [
                'name'            => $i->name,
                'sku'             => $i->sku,
                'quantity'        => $i->quantity,
                'low_stock_limit' => $i->low_stock_limit,
            ]);

        $outOfStock = InventoryItem::active()
            ->where('is_service', false)
            ->where('quantity', '<=', 0)
            ->count();

        return [
            'total_items'   => $total,
            'out_of_stock'  => $outOfStock,
            'low_stock'     => ['count' => $lowStock->count(), 'items' => $lowStock],
        ];
    }

    // -------------------------------------------------------------------------
    private function leads($now): array
    {
        $active   = Lead::whereIn('status', ['new', 'contacted', 'interested', 'qualified'])
            ->where('do_not_contact', false)->where('archived', false)->count();
        $overdue  = Lead::where('follow_up_date', '<', $now)
            ->whereIn('status', ['new', 'contacted', 'interested', 'qualified'])
            ->where('do_not_contact', false)->where('archived', false)->count();
        $converted = Lead::where('status', 'converted')->count();
        $lost      = Lead::where('status', 'lost')->count();

        $overdueList = Lead::where('follow_up_date', '<', $now)
            ->whereIn('status', ['new', 'contacted', 'interested', 'qualified'])
            ->where('do_not_contact', false)->where('archived', false)
            ->orderBy('follow_up_date')
            ->limit(5)
            ->get(['id', 'name', 'phone', 'status', 'follow_up_date'])
            ->map(fn($l) => [
                'id'          => $l->id,
                'name'        => $l->name,
                'phone'       => $l->phone,
                'status'      => $l->status,
                'overdue_by'  => Carbon::parse($l->follow_up_date)->diffForHumans(),
            ]);

        return [
            'active'           => $active,
            'overdue'          => $overdue,
            'converted'        => $converted,
            'lost'             => $lost,
            'overdue_list'     => $overdueList,
        ];
    }

    // -------------------------------------------------------------------------
    private function faults($startOfWeek, $startOfMonth): array
    {
        $open    = FaultTicket::open()->count();
        $overdue = FaultTicket::overdue()->count();
        $resolvedWeek  = FaultTicket::where('status', 'resolved')->where('resolved_at', '>=', $startOfWeek)->count();

        $resolvedMonth = FaultTicket::where('status', 'resolved')
            ->where('resolved_at', '>=', $startOfMonth)
            ->whereNotNull('resolved_at')->get();

        $avgHours   = $resolvedMonth->count() > 0
            ? round($resolvedMonth->avg(fn($t) => $t->getResolutionHours()), 1) : null;
        $slaPercent = $resolvedMonth->count() > 0
            ? round(($resolvedMonth->filter(fn($t) => $t->metSla())->count() / $resolvedMonth->count()) * 100, 1) : null;

        return [
            'open'                    => $open,
            'overdue'                 => $overdue,
            'resolved_this_week'      => $resolvedWeek,
            'avg_resolution_hours'    => $avgHours,
            'sla_met_percent'         => $slaPercent,
        ];
    }

    // -------------------------------------------------------------------------
    private function pettyCash(): array
    {
        $balance  = (float) PettyCash::currentBalance();
        $pending  = (int) PettyCash::where('status', 'pending')->where('type', 'expense')->count();
        $pendingAmt = (float) PettyCash::where('status', 'pending')->where('type', 'expense')->sum('amount');

        return [
            'balance'         => $balance,
            'pending_count'   => $pending,
            'pending_amount'  => $pendingAmt,
        ];
    }

    // -------------------------------------------------------------------------
    private function bestSellers(): array
    {
        $since = Carbon::now()->subDays(30);

        $format = fn($rows) => $rows->map(fn($r) => [
            'name'     => $r->inventoryItem?->name ?? $r->item_name ?? 'Unknown',
            'qty_sold' => (int) $r->total_quantity,
            'revenue'  => (float) $r->total_revenue,
        ]);

        $query = fn($type, $service) => JobItem::select(
                'inventory_item_id',
                DB::raw('SUM(job_items.quantity) as total_quantity'),
                DB::raw('SUM(job_items.subtotal) as total_revenue')
            )
            ->join('jobs', 'job_items.job_id', '=', 'jobs.id')
            ->join('inventory_items', 'job_items.inventory_item_id', '=', 'inventory_items.id')
            ->where('jobs.job_type', $type)
            ->where('inventory_items.is_service', $service)
            ->where('jobs.job_date', '>=', $since)
            ->groupBy('inventory_item_id')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->with('inventoryItem:id,name')
            ->get();

        return [
            'period'        => 'Last 30 days',
            'moto_parts'    => $format($query('moto', false)),
            'moto_services' => $format($query('moto', true)),
            'cool_parts'    => $format($query('ac', false)),
            'cool_services' => $format($query('ac', true)),
        ];
    }

    // -------------------------------------------------------------------------
    private function monthlyTrend(): array
    {
        $trend = [];
        for ($i = 5; $i >= 0; $i--) {
            $d     = Carbon::now()->subMonths($i);
            $start = $d->copy()->startOfMonth();
            $end   = $d->copy()->endOfMonth();

            $moto = (float) Payment::join('jobs', 'payments.job_id', '=', 'jobs.id')
                ->whereBetween('jobs.job_date', [$start, $end])
                ->where('jobs.job_type', 'moto')->sum('payments.amount');

            $cool = (float) Payment::join('jobs', 'payments.job_id', '=', 'jobs.id')
                ->whereBetween('jobs.job_date', [$start, $end])
                ->where('jobs.job_type', 'ac')->sum('payments.amount');

            $trend[] = [
                'month' => $d->format('M Y'),
                'moto'  => $moto,
                'cool'  => $cool,
                'total' => $moto + $cool,
            ];
        }
        return $trend;
    }
}
