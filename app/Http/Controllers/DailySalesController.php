<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\ActivityLog;
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
        $search = trim((string) $request->get('search', ''));

        if ($search !== '') {
            $query = DailySalesLog::query();
            $normalizedSearch = mb_strtolower($search);

            $query->where(function ($q) use ($normalizedSearch) {
                $q->whereHas('customer', function ($customerQuery) use ($normalizedSearch) {
                    $customerQuery->whereRaw('LOWER(name) LIKE ?', ['%' . $normalizedSearch . '%']);
                })->orWhereHas('job', function ($jobQuery) use ($normalizedSearch) {
                    $jobQuery->whereRaw('LOWER(customer_name) LIKE ?', ['%' . $normalizedSearch . '%']);
                });
            });
        } else {
            $query = DailySalesLog::query()->forDate($date);
        }

        $logs = $query
            ->with('lines', 'createdByUser', 'customer')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return view('sales.daily-index', compact('date', 'logs', 'search'));
    }

    public function destroy(DailySalesLog $dailySalesLog)
    {
        if (!Auth::user()->canDeleteSales()) {
            abort(403);
        }

        if (!$dailySalesLog->canEditQuotation()) {
            return back()->with('error', 'Cannot delete an invoiced or paid sale. Reopen it first.');
        }

        $id = $dailySalesLog->id;
        $dailySalesLog->lines()->delete();
        $dailySalesLog->delete();

        ActivityLog::record('sale.deleted', "Draft sale #{$id} deleted");

        return redirect()->route('sales.daily.index', ['date' => $dailySalesLog->date->format('Y-m-d')])
            ->with('success', 'Draft sale #' . $id . ' deleted.');
    }

    public function openLog(Request $request)
    {
        $validated = $request->validate([
            'date' => ['required', 'date', 'before_or_equal:today'],
            'business_unit' => ['required', 'in:moto,cool,it,easyfix'],
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
            'status' => DailySalesLog::STATUS_DRAFT,
            'approval_method' => 'not_applicable',
        ]);

        return redirect()->route('sales.daily.show', $log);
    }

    public function show(DailySalesLog $dailySalesLog)
    {
        return redirect()->route(
            $dailySalesLog->isInvoiceStage() ? 'sales.daily.invoice-workflow' : 'sales.daily.quotation-builder',
            $dailySalesLog
        );
    }

    public function quotationBuilder(DailySalesLog $dailySalesLog)
    {
        if ($dailySalesLog->isInvoiceStage()) {
            return redirect()->route('sales.daily.invoice-workflow', $dailySalesLog);
        }

        return view('sales.daily-show', $this->buildShowViewData($dailySalesLog, 'builder'));
    }

    public function invoiceWorkflow(DailySalesLog $dailySalesLog)
    {
        return view('sales.daily-show', $this->buildShowViewData($dailySalesLog, 'invoice'));
    }

    private function buildShowViewData(DailySalesLog $dailySalesLog, string $screen): array
    {
        if ($dailySalesLog->job_id) {
            $dailySalesLog->syncWorkflowStatus($dailySalesLog->job);
            $dailySalesLog->refresh();
        } else {
            $dailySalesLog->syncWorkflowStatus();
            $dailySalesLog->refresh();
        }

        $dailySalesLog->load('lines.inventoryItem', 'createdByUser', 'submittedByUser', 'customer.addresses', 'customerAddress', 'transferAccount');

        $categoryMap = match ($dailySalesLog->business_unit) {
            'cool' => 'ac',
            'it' => 'it',
            'easyfix' => 'easyfix',
            default => 'moto',
        };
        $inventoryItems = InventoryItem::active()
            ->ofCategory($categoryMap)
            ->orderBy('name')
            ->get();

        $accounts = Account::where('is_active', true)->where('is_system', false)->orderBy('name')->get();

        $accountTransaction = null;
        if ($dailySalesLog->isSubmitted() && $dailySalesLog->transfer_account_id) {
            $accountTransaction = AccountTransaction::where('related_type', DailySalesLog::class)
                ->where('related_id', $dailySalesLog->id)
                ->first();
        }

        return [
            'log' => $dailySalesLog,
            'inventoryItems' => $inventoryItems,
            'accounts' => $accounts,
            'accountTransaction' => $accountTransaction,
            'screen' => $screen,
        ];
    }

    public function updateDueDate(Request $request, DailySalesLog $dailySalesLog)
    {
        if (!$dailySalesLog->canEditQuotation()) {
            return back()->with('error', 'Cannot change due date after the quotation has been converted to an invoice.');
        }

        $validated = $request->validate([
            'due_date' => ['nullable', 'date'],
        ]);

        $dailySalesLog->update([
            'due_date' => $validated['due_date'] ?? null,
        ]);

        $dailySalesLog->syncLinkedDraftJob();

        $label = $dailySalesLog->due_date ? $dailySalesLog->due_date->format('Y-m-d') : 'Due upon receipt';
        ActivityLog::record('sale.due_date_updated', "Sale #{$dailySalesLog->id} due date → {$label}", $dailySalesLog);

        return back()->with('success', 'Due date updated.');
    }

    public function updateQuotationValidity(Request $request, DailySalesLog $dailySalesLog)
    {
        if (!$dailySalesLog->canEditQuotation()) {
            return back()->with('error', 'Cannot change quotation validity after the quotation has been converted to an invoice.');
        }

        $validated = $request->validate([
            'quotation_validity_days' => ['required', 'integer', 'min:1', 'max:365'],
        ]);

        $dailySalesLog->update([
            'quotation_validity_days' => $validated['quotation_validity_days'],
        ]);

        $dailySalesLog->syncLinkedDraftJob();

        ActivityLog::record(
            'sale.quotation_validity_updated',
            "Sale #{$dailySalesLog->id} quotation validity → {$validated['quotation_validity_days']} day(s)",
            $dailySalesLog
        );

        return back()->with('success', 'Quotation validity updated.');
    }

    public function updateNotes(Request $request, DailySalesLog $dailySalesLog)
    {
        if (!$dailySalesLog->canEditQuotation()) {
            return back()->with('error', 'Cannot change notes after the quotation has been converted to an invoice.');
        }

        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $dailySalesLog->update([
            'notes' => $validated['notes'] ?? null,
        ]);

        $dailySalesLog->syncLinkedDraftJob();

        ActivityLog::record('sale.notes_updated', "Sale #{$dailySalesLog->id} notes updated", $dailySalesLog);

        return back()->with('success', 'Notes updated.');
    }

    public function updatePoNumber(Request $request, DailySalesLog $dailySalesLog)
    {
        if (!$dailySalesLog->canEditQuotation()) {
            return back()->with('error', 'Cannot change PO number after the quotation has been converted to an invoice.');
        }

        $validated = $request->validate([
            'po_number' => ['required', 'string', 'max:100'],
        ]);

        $dailySalesLog->update([
            'po_number' => trim((string) $validated['po_number']),
        ]);

        $dailySalesLog->syncLinkedDraftJob();

        ActivityLog::record(
            'sale.po_number_updated',
            "Sale #{$dailySalesLog->id} PO number updated",
            $dailySalesLog
        );

        return back()->with('success', 'PO number saved.');
    }

    public function updateApprovalMethod(Request $request, DailySalesLog $dailySalesLog)
    {
        if (!$dailySalesLog->canEditQuotation()) {
            return back()->with('error', 'Cannot change approval method after the quotation has been converted to an invoice.');
        }

        $validated = $request->validate([
            'approval_method' => ['required', 'in:po,signed_copy,not_applicable'],
            'po_number' => ['nullable', 'string', 'max:100', 'required_if:approval_method,po'],
        ]);

        $dailySalesLog->update([
            'approval_method' => $validated['approval_method'],
            'po_number' => $validated['approval_method'] === 'po'
                ? trim((string) ($validated['po_number'] ?? $dailySalesLog->po_number))
                : null,
        ]);

        $dailySalesLog->syncLinkedDraftJob();

        ActivityLog::record(
            'sale.approval_method_updated',
            "Sale #{$dailySalesLog->id} approval method → {$validated['approval_method']}",
            $dailySalesLog
        );

        return back()->with('success', 'Approval method updated.');
    }

    public function addLine(Request $request, DailySalesLog $dailySalesLog)
    {
        if (!$dailySalesLog->canEditQuotation()) {
            return back()->with('error', 'Cannot add lines after the quotation has been converted to an invoice.');
        }

        $validated = $request->validate([
            'inventory_item_id' => ['nullable', 'exists:inventory_items,id'],
            'description' => ['required_without:inventory_item_id', 'nullable', 'string', 'max:255'],
            'qty' => ['required', 'integer', 'min:1'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'note' => ['nullable', 'string', 'max:1000'],
            'warranty_value' => ['nullable', 'integer', 'min:1', 'required_with:warranty_unit'],
            'warranty_unit' => ['nullable', 'in:days,months', 'required_with:warranty_value'],
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
            'warranty_value' => $validated['warranty_value'] ?? null,
            'warranty_unit' => $validated['warranty_unit'] ?? null,
            'is_gst_applicable' => $isGst,
            'gst_amount' => $gstAmount,
        ]);

        $dailySalesLog->syncWorkflowStatus();
        $dailySalesLog->syncLinkedDraftJob();

        return back()->with('success', 'Line added.');
    }

    public function removeLine(DailySalesLog $dailySalesLog, DailySalesLine $line)
    {
        if (!$dailySalesLog->canEditQuotation()) {
            return back()->with('error', 'Cannot remove lines after the quotation has been converted to an invoice.');
        }

        if ($line->daily_sales_log_id !== $dailySalesLog->id) {
            abort(404);
        }

        $line->delete();

        $dailySalesLog->syncWorkflowStatus();
        $dailySalesLog->syncLinkedDraftJob();

        return back()->with('success', 'Line removed.');
    }

    public function submit(Request $request, DailySalesLog $dailySalesLog)
    {
        if ($dailySalesLog->status === DailySalesLog::STATUS_PARTIAL_PAID || $dailySalesLog->status === DailySalesLog::STATUS_PAID) {
            return back()->with('error', 'Payment has already been recorded for this invoice.');
        }

        $dailySalesLog->load('lines.inventoryItem');
        $grandTotal = $dailySalesLog->totals['grand'];

        $validated = $request->validate([
            'payment_method' => ['required', 'in:cash,transfer'],
            'cash_tendered' => ['nullable', 'required_if:payment_method,cash', 'numeric', 'min:' . $grandTotal],
            'transfer_account_id' => ['nullable', 'required_if:payment_method,transfer', 'exists:accounts,id'],
        ]);

        $dailySalesLog->submit(
            $validated['payment_method'],
            $validated['payment_method'] === 'cash' ? (float) $validated['cash_tendered'] : null,
            $validated['payment_method'] === 'transfer' ? (int) $validated['transfer_account_id'] : null
        );

        $unit = match ($dailySalesLog->business_unit) {
            'moto' => 'Micro Moto',
            'cool' => 'Micro Cool',
            'it' => 'Micronet',
            'easyfix' => 'Micronet - Easy Fix',
            default => $dailySalesLog->business_unit,
        };
        $total = number_format($dailySalesLog->fresh()->totals['grand'] ?? 0, 2);
        ActivityLog::record('sale.submitted', "Sale #{$dailySalesLog->id} submitted — {$unit}, MVR {$total} ({$validated['payment_method']})", $dailySalesLog);

        return back()->with('success', 'Sale submitted and stock updated.');
    }

    public function reopen(DailySalesLog $dailySalesLog)
    {
        if (!Auth::user()->hasAnyRole(['admin', 'manager'])) {
            abort(403);
        }

        if (!$dailySalesLog->isInvoiceStage()) {
            return back()->with('error', 'This sale is still in quotation stage.');
        }

        $eod = EodReconciliation::where('date', $dailySalesLog->date)
            ->where('business_unit', $dailySalesLog->business_unit)
            ->whereIn('status', ['closed', 'deposited'])
            ->first();

        if ($eod) {
            return back()->with('error', 'Cannot reopen — End of Day has been closed for this date.');
        }

        $dailySalesLog->reopen();

        ActivityLog::record('sale.reopened', "Sale #{$dailySalesLog->id} reopened — stock reversed", $dailySalesLog);

        return back()->with('success', 'Sale reopened. Stock movements reversed.');
    }

    public function createAndSetCustomer(Request $request, DailySalesLog $dailySalesLog)
    {
        if (!$dailySalesLog->canEditQuotation()) {
            return back()->with('error', 'Cannot change customer after the quotation has been converted to an invoice.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50', 'regex:/^[0-9\\s,;\\/|()+-]+$/'],
            'gst_number' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
        ], [
            'phone.regex' => 'Phone number can contain digits and common separators only. Letters are not allowed.',
        ]);

        $category = match ($dailySalesLog->business_unit) {
            'cool' => 'ac',
            'it' => 'it',
            'easyfix' => 'easyfix',
            default => 'moto',
        };

        $customer = Customer::firstOrCreate(
            ['phone' => $validated['phone']],
            ['name' => $validated['name'], 'category' => $category, 'gst_number' => $validated['gst_number'] ?? null]
        );

        if (!empty($validated['gst_number']) && $customer->gst_number !== $validated['gst_number']) {
            $customer->gst_number = $validated['gst_number'];
            $customer->save();
        }

        $address = null;
        if (!empty($validated['address'])) {
            $address = $customer->addresses()
                ->where('address', $validated['address'])
                ->orderByDesc('is_default')
                ->first();

            if (!$address) {
                $address = $customer->addresses()->create([
                    'label' => 'Primary',
                    'address' => $validated['address'],
                    'is_default' => !$customer->addresses()->exists(),
                ]);
            }

            if ($address->is_default) {
                $customer->update(['address' => $address->address]);
            }
        }

        $dailySalesLog->update([
            'customer_id' => $customer->id,
            'customer_address_id' => $address?->id,
            'customer_address_text' => $address?->address,
        ]);
        $dailySalesLog->syncLinkedDraftJob();

        return back()->with('success', 'Customer "' . $customer->name . '" created and assigned.');
    }

    public function setCustomer(Request $request, DailySalesLog $dailySalesLog)
    {
        if (!$dailySalesLog->canEditQuotation()) {
            return back()->with('error', 'Cannot change customer after the quotation has been converted to an invoice.');
        }

        $validated = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'customer_address_id' => ['nullable', 'exists:customer_addresses,id'],
        ]);

        $customer = !empty($validated['customer_id']) ? Customer::with('addresses')->find($validated['customer_id']) : null;
        $address = null;

        if ($customer && !empty($validated['customer_address_id'])) {
            $address = $customer->addresses->firstWhere('id', (int) $validated['customer_address_id']);

            if (!$address) {
                return back()->withErrors([
                    'customer_address_id' => 'Selected address does not belong to this customer.',
                ])->withInput();
            }
        } elseif ($customer) {
            $address = $customer->addresses->firstWhere('is_default', true) ?? $customer->addresses->first();
        }

        $dailySalesLog->update([
            'customer_id' => $validated['customer_id'],
            'customer_address_id' => $address?->id,
            'customer_address_text' => $address?->address ?? $customer?->address,
        ]);
        $dailySalesLog->syncLinkedDraftJob();

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
        } elseif ($unit === 'it') {
            $brand = [
                'name' => 'Micronet',
                'tagline' => 'IT & Technical Services',
                'address' => 'Janavaree Hingun, Near Dharubaaruge',
                'phone' => '+960 9996210',
                'email' => 'hello@micronet.mv',
                'website' => 'micronet.mv',
            ];
        } elseif ($unit === 'easyfix') {
            $brand = [
                'name' => 'Micronet - Easy Fix',
                'tagline' => 'Handyman Services in Greater Male Area',
                'address' => 'Janavaree Hingun, Near Dharubaaruge',
                'phone' => '+960 9996210',
                'email' => 'hello@micronet.mv',
                'website' => 'easyfix.mv',
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

    public function convertToInvoice(DailySalesLog $dailySalesLog)
    {
        if ($dailySalesLog->isInvoiceStage()) {
            if ($dailySalesLog->job_id) {
                return redirect()->route('jobs.invoice', $dailySalesLog->job_id);
            }
            return back()->with('error', 'This sale is already in invoice stage, but no invoice job was found.');
        }

        $dailySalesLog->load('lines');
        if (!$dailySalesLog->customer_id) {
            return back()->with('error', 'Please select a customer before creating an invoice.');
        }

        if ($dailySalesLog->lines->isEmpty()) {
            return back()->with('error', 'Add at least one line item before creating an invoice.');
        }

        if (!$dailySalesLog->isApprovalReady()) {
            return back()->with('error', 'Please complete the approval details before converting this quotation to an invoice.');
        }

        $job = $dailySalesLog->createOrUpdateInvoiceJob(false);
        $dailySalesLog->update([
            'job_id' => $job->id,
            'status' => DailySalesLog::STATUS_INVOICED,
        ]);

        ActivityLog::record('sale.invoice_created', "Invoice created for sale #{$dailySalesLog->id}", $dailySalesLog);

        return redirect()->route('jobs.invoice', $job->id);
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
