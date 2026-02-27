<?php

namespace App\Http\Controllers;

use App\Models\DailySalesLog;
use App\Models\DailySalesLine;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DailySalesController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->get('date', now()->toDateString());
        $logs = DailySalesLog::forDate($date)->with('lines', 'createdByUser')->get();

        return view('sales.daily-index', compact('date', 'logs'));
    }

    public function openLog(Request $request)
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'business_unit' => ['required', 'in:moto,cool'],
        ]);

        $log = DailySalesLog::whereDate('date', $validated['date'])
            ->where('business_unit', $validated['business_unit'])
            ->first();

        if (!$log) {
            $log = DailySalesLog::create([
                'date' => $validated['date'],
                'business_unit' => $validated['business_unit'],
                'created_by' => Auth::id(),
                'status' => 'draft',
            ]);
        }

        return redirect()->route('sales.daily.show', $log);
    }

    public function show(DailySalesLog $dailySalesLog)
    {
        $dailySalesLog->load('lines.inventoryItem', 'createdByUser', 'submittedByUser');

        $categoryMap = $dailySalesLog->business_unit === 'moto' ? 'moto' : 'ac';
        $inventoryItems = InventoryItem::active()
            ->ofCategory($categoryMap)
            ->orderBy('name')
            ->get();

        return view('sales.daily-show', [
            'log' => $dailySalesLog,
            'inventoryItems' => $inventoryItems,
        ]);
    }

    public function addLine(Request $request, DailySalesLog $dailySalesLog)
    {
        if ($dailySalesLog->isSubmitted()) {
            return back()->with('error', 'Cannot add lines to a submitted log.');
        }

        $validated = $request->validate([
            'inventory_item_id' => ['nullable', 'exists:inventory_items,id'],
            'description' => ['required_without:inventory_item_id', 'nullable', 'string', 'max:255'],
            'qty' => ['required', 'integer', 'min:1'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', 'in:cash,transfer'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $description = $validated['description'] ?? '';
        $isStockItem = false;

        if (!empty($validated['inventory_item_id'])) {
            $item = InventoryItem::findOrFail($validated['inventory_item_id']);
            $description = $item->name;
            $isStockItem = !$item->is_service;
        }

        $qty = (int) $validated['qty'];
        $unitPrice = (float) $validated['unit_price'];
        $lineTotal = $qty * $unitPrice;

        $dailySalesLog->lines()->create([
            'inventory_item_id' => $validated['inventory_item_id'] ?? null,
            'description' => $description,
            'qty' => $qty,
            'unit_price' => $unitPrice,
            'payment_method' => $validated['payment_method'],
            'line_total' => $lineTotal,
            'is_stock_item' => $isStockItem,
            'note' => $validated['note'] ?? null,
        ]);

        return back()->with('success', 'Line added.');
    }

    public function removeLine(DailySalesLog $dailySalesLog, DailySalesLine $line)
    {
        if ($dailySalesLog->isSubmitted()) {
            return back()->with('error', 'Cannot remove lines from a submitted log.');
        }

        if ($line->daily_sales_log_id !== $dailySalesLog->id) {
            abort(404);
        }

        $line->delete();

        return back()->with('success', 'Line removed.');
    }

    public function submit(DailySalesLog $dailySalesLog)
    {
        if ($dailySalesLog->isSubmitted()) {
            return back()->with('error', 'Log is already submitted.');
        }

        $dailySalesLog->load('lines.inventoryItem');
        $dailySalesLog->submit();

        return back()->with('success', 'Sales log submitted and stock updated.');
    }

    public function reopen(DailySalesLog $dailySalesLog)
    {
        if (!Auth::user()->hasAnyRole(['admin', 'manager'])) {
            abort(403);
        }

        if (!$dailySalesLog->isSubmitted()) {
            return back()->with('error', 'Log is not submitted.');
        }

        $dailySalesLog->reopen();

        return back()->with('success', 'Sales log reopened. Stock movements reversed.');
    }

    public function reports(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->get('date_to', now()->toDateString());
        $businessUnit = $request->get('business_unit');
        $paymentMethod = $request->get('payment_method');

        $query = DailySalesLog::submitted()
            ->whereBetween('date', [$dateFrom, $dateTo]);

        if ($businessUnit) {
            $query->forUnit($businessUnit);
        }

        $logs = $query->with('lines')->orderBy('date', 'desc')->get();

        // Daily summaries
        $dailySummaries = $logs->map(function ($log) {
            $totals = $log->totals;
            return [
                'date' => $log->date,
                'business_unit' => $log->business_unit,
                'cash' => $totals['cash'],
                'transfer' => $totals['transfer'],
                'grand' => $totals['grand'],
            ];
        });

        // Filter by payment method for totals
        $allLines = $logs->flatMap->lines;
        if ($paymentMethod) {
            $allLines = $allLines->where('payment_method', $paymentMethod);
        }

        $totalCash = $allLines->where('payment_method', 'cash')->sum('line_total');
        $totalTransfer = $allLines->where('payment_method', 'transfer')->sum('line_total');
        $grandTotal = $paymentMethod ? $allLines->sum('line_total') : $totalCash + $totalTransfer;

        // Top items
        $topItems = $allLines
            ->groupBy('description')
            ->map(function ($group) {
                return [
                    'description' => $group->first()->description,
                    'qty_sold' => $group->sum('qty'),
                    'revenue' => $group->sum('line_total'),
                ];
            })
            ->sortByDesc('revenue')
            ->take(10)
            ->values();

        // Unit breakdown
        $unitBreakdown = $logs->groupBy('business_unit')->map(function ($unitLogs) {
            $lines = $unitLogs->flatMap->lines;
            return [
                'cash' => $lines->where('payment_method', 'cash')->sum('line_total'),
                'transfer' => $lines->where('payment_method', 'transfer')->sum('line_total'),
                'grand' => $lines->sum('line_total'),
            ];
        });

        return view('sales.reports', compact(
            'dateFrom', 'dateTo', 'businessUnit', 'paymentMethod',
            'dailySummaries', 'totalCash', 'totalTransfer', 'grandTotal',
            'topItems', 'unitBreakdown'
        ));
    }
}
