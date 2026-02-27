<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\DailySalesLog;
use App\Models\DailySalesLine;
use App\Models\EodReconciliation;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DailySalesController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->get('date', now()->toDateString());
        $businessUnit = $request->get('business_unit');

        $query = DailySalesLog::forDate($date);

        if ($businessUnit) {
            $query->forUnit($businessUnit);
        }

        $logs = $query->with('lines', 'createdByUser', 'customer')->get();

        return view('sales.daily-index', compact('date', 'logs', 'businessUnit'));
    }

    public function destroy(DailySalesLog $dailySalesLog)
    {
        if (!Auth::user()->canDeleteSales()) {
            abort(403);
        }

        if ($dailySalesLog->isSubmitted()) {
            return back()->with('error', 'Cannot delete a submitted sale. Reopen it first.');
        }

        $dailySalesLog->lines()->delete();
        $dailySalesLog->delete();

        return redirect()->route('sales.daily.index', ['date' => $dailySalesLog->date->format('Y-m-d')])
            ->with('success', 'Draft sale #' . $dailySalesLog->id . ' deleted.');
    }

    public function openLog(Request $request)
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'business_unit' => ['required', 'in:moto,cool'],
        ]);

        // Mechanics can only create sales for their assigned unit
        $allowedUnit = Auth::user()->allowedBusinessUnit();
        if ($allowedUnit && $validated['business_unit'] !== $allowedUnit) {
            abort(403, 'You can only create sales for your assigned business unit.');
        }

        $eod = EodReconciliation::where('date', $validated['date'])
            ->where('business_unit', $validated['business_unit'])
            ->whereIn('status', ['closed', 'deposited'])
            ->first();

        if ($eod) {
            return back()->with('error', 'Cannot create new sales — End of Day has been closed for this date.');
        }

        $log = DailySalesLog::create([
            'date' => $validated['date'],
            'business_unit' => $validated['business_unit'],
            'created_by' => Auth::id(),
            'status' => 'draft',
        ]);

        return redirect()->route('sales.daily.show', $log);
    }

    public function show(DailySalesLog $dailySalesLog)
    {
        $dailySalesLog->load('lines.inventoryItem', 'createdByUser', 'submittedByUser', 'customer');

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
            return back()->with('error', 'Cannot add lines to a submitted sale.');
        }

        $validated = $request->validate([
            'inventory_item_id' => ['nullable', 'exists:inventory_items,id'],
            'description' => ['required_without:inventory_item_id', 'nullable', 'string', 'max:255'],
            'qty' => ['required', 'integer', 'min:1'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'note' => ['nullable', 'string', 'max:1000'],
            'is_gst_applicable' => ['nullable', 'boolean'],
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
        $isGst = !empty($validated['is_gst_applicable']);
        $gstAmount = $isGst ? round($lineTotal * 0.08, 2) : 0;

        $dailySalesLog->lines()->create([
            'inventory_item_id' => $validated['inventory_item_id'] ?? null,
            'description' => $description,
            'qty' => $qty,
            'unit_price' => $unitPrice,
            'payment_method' => 'cash',
            'line_total' => $lineTotal,
            'is_stock_item' => $isStockItem,
            'note' => $validated['note'] ?? null,
            'is_gst_applicable' => $isGst,
            'gst_amount' => $gstAmount,
        ]);

        return back()->with('success', 'Line added.');
    }

    public function removeLine(DailySalesLog $dailySalesLog, DailySalesLine $line)
    {
        if ($dailySalesLog->isSubmitted()) {
            return back()->with('error', 'Cannot remove lines from a submitted sale.');
        }

        if ($line->daily_sales_log_id !== $dailySalesLog->id) {
            abort(404);
        }

        $line->delete();

        return back()->with('success', 'Line removed.');
    }

    public function submit(Request $request, DailySalesLog $dailySalesLog)
    {
        if ($dailySalesLog->isSubmitted()) {
            return back()->with('error', 'Sale is already submitted.');
        }

        $dailySalesLog->load('lines.inventoryItem');
        $grandTotal = $dailySalesLog->totals['grand'];

        $validated = $request->validate([
            'payment_method' => ['required', 'in:cash,transfer'],
            'cash_tendered' => ['nullable', 'required_if:payment_method,cash', 'numeric', 'min:' . $grandTotal],
        ]);

        $dailySalesLog->submit(
            $validated['payment_method'],
            $validated['payment_method'] === 'cash' ? (float) $validated['cash_tendered'] : null
        );

        return back()->with('success', 'Sale submitted and stock updated.');
    }

    public function reopen(DailySalesLog $dailySalesLog)
    {
        if (!Auth::user()->hasAnyRole(['admin', 'manager'])) {
            abort(403);
        }

        if (!$dailySalesLog->isSubmitted()) {
            return back()->with('error', 'Sale is not submitted.');
        }

        $eod = EodReconciliation::where('date', $dailySalesLog->date)
            ->where('business_unit', $dailySalesLog->business_unit)
            ->whereIn('status', ['closed', 'deposited'])
            ->first();

        if ($eod) {
            return back()->with('error', 'Cannot reopen — End of Day has been closed for this date.');
        }

        $dailySalesLog->reopen();

        return back()->with('success', 'Sale reopened. Stock movements reversed.');
    }

    public function createAndSetCustomer(Request $request, DailySalesLog $dailySalesLog)
    {
        if ($dailySalesLog->isSubmitted()) {
            return back()->with('error', 'Cannot change customer on a submitted sale.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
        ]);

        $category = $dailySalesLog->business_unit === 'cool' ? 'ac' : 'moto';

        $customer = Customer::firstOrCreate(
            ['phone' => $validated['phone']],
            ['name' => $validated['name'], 'category' => $category]
        );

        $dailySalesLog->update(['customer_id' => $customer->id]);

        return back()->with('success', 'Customer "' . $customer->name . '" created and assigned.');
    }

    public function setCustomer(Request $request, DailySalesLog $dailySalesLog)
    {
        if ($dailySalesLog->isSubmitted()) {
            return back()->with('error', 'Cannot change customer on a submitted sale.');
        }

        $validated = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
        ]);

        $dailySalesLog->update(['customer_id' => $validated['customer_id']]);

        $message = $validated['customer_id'] ? 'Customer assigned.' : 'Customer cleared — will use Walk-in.';

        return back()->with('success', $message);
    }

    public function quotation(DailySalesLog $dailySalesLog)
    {
        $dailySalesLog->load('lines', 'customer');

        $unit = $dailySalesLog->business_unit;

        if ($unit === 'cool') {
            $brand = [
                'name' => 'Micro Cool',
                'tagline' => 'Air Conditioning Service',
                'address' => 'Janavaree Hingun, Near Dharubaaruge',
                'phone' => '+960 9996210',
                'email' => 'hello@micronet.mv',
                'website' => 'cool.micronet.mv',
            ];
        } else {
            $brand = [
                'name' => 'Micro Moto Garage',
                'tagline' => 'Motorcycle Service & Repair',
                'address' => 'Janavaree Hingun, Near Dharubaaruge',
                'phone' => '+960 9996210',
                'email' => 'hello@micronet.mv',
                'website' => 'garage.micronet.mv',
            ];
        }

        $quotationNumber = 'DSQ-' . str_pad($dailySalesLog->id, 5, '0', STR_PAD_LEFT);

        return view('sales.daily-quotation', [
            'log' => $dailySalesLog,
            'brand' => $brand,
            'quotationNumber' => $quotationNumber,
        ]);
    }

    public function reports(Request $request)
    {
        if (!Auth::user()->canViewReports()) {
            abort(403);
        }

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
                'payment_method' => $log->payment_method,
                'grand' => $totals['grand'],
            ];
        });

        // Filter by payment method
        $filteredLogs = $paymentMethod ? $logs->where('payment_method', $paymentMethod) : $logs;

        $totalCash = $logs->where('payment_method', 'cash')->sum(fn($l) => $l->totals['grand']);
        $totalTransfer = $logs->where('payment_method', 'transfer')->sum(fn($l) => $l->totals['grand']);
        $grandTotal = $filteredLogs->sum(fn($l) => $l->totals['grand']);

        // For top items, use filtered logs' lines
        $allLines = $filteredLogs->flatMap->lines;

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
            return [
                'cash' => $unitLogs->where('payment_method', 'cash')->sum(fn($l) => $l->totals['grand']),
                'transfer' => $unitLogs->where('payment_method', 'transfer')->sum(fn($l) => $l->totals['grand']),
                'grand' => $unitLogs->sum(fn($l) => $l->totals['grand']),
            ];
        });

        return view('sales.reports', compact(
            'dateFrom', 'dateTo', 'businessUnit', 'paymentMethod',
            'dailySummaries', 'totalCash', 'totalTransfer', 'grandTotal',
            'topItems', 'unitBreakdown'
        ));
    }
}
