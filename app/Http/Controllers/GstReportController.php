<?php

namespace App\Http\Controllers;

use App\Models\DailySalesLog;
use App\Models\Expense;
use App\Models\GstSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class GstReportController extends Controller
{
    private array $activityNumbers = [];

    public function index(Request $request)
    {
        Gate::authorize('view-reports');
        $report = $this->buildReport($request);

        return view('reports.gst', $report);
    }

    public function export(Request $request, string $statement)
    {
        Gate::authorize('view-reports');
        abort_unless(in_array($statement, ['input', 'tax-invoices', 'other-transactions'], true), 404);

        $report = $this->buildReport($request);
        $filename = "gst-{$statement}-{$report['startDate']->format('Y-m-d')}-to-{$report['endDate']->format('Y-m-d')}.csv";

        return response()->streamDownload(function () use ($statement, $report) {
            $handle = fopen('php://output', 'w');

            if ($statement === 'input') {
                fputcsv($handle, ['#', 'Supplier TIN', 'Supplier Name', 'Supplier Invoice Number', 'Invoice Date', 'Invoice Total (excluding GST)', 'GST charged at 6%', 'GST charged at 8%', 'GST charged at 12%', 'GST charged at 16%', 'GST charged at 17%', 'Your Taxable Activity Number', 'Revenue / Capital']);
                foreach ($report['inputExpenses'] as $index => $expense) {
                    fputcsv($handle, [$index + 1, $expense->vendorEntity?->gst_number, $expense->vendorEntity?->name ?? $expense->vendor, $expense->reference, $expense->incurred_at?->format('j F Y'), $this->money($expense->subtotal_amount), '0.00', $this->money($expense->gst_amount), '0.00', '0.00', '0.00', $this->activityNumber($expense->business_unit), ucfirst($expense->gst_expenditure_type ?? 'revenue')]);
                }
            } elseif ($statement === 'tax-invoices') {
                fputcsv($handle, ['Customer TIN', 'Customer Name', 'Invoice No.', 'Invoice Date', 'Value of Supplies Subject to GST at 8% or 17%', 'Value of Zero-Rated Supplies', 'Value of Exempt Supplies', 'Value of Out-of-Scope Supplies', 'Your Taxable Activity No.']);
                foreach ($report['taxInvoices'] as $invoice) {
                    fputcsv($handle, [$invoice['customer_tin'], $invoice['customer_name'], $invoice['invoice_number'], $invoice['date']->format('j F Y'), $this->money($invoice['taxable']), '0.00', '0.00', '0.00', $invoice['activity_number']]);
                }
            } else {
                fputcsv($handle, ['Your Taxable Activity No.', 'Value of Supplies Subject to GST at 8% or 17%', 'Value of Zero-Rated Supplies', 'Value of Exempt Supplies', 'Value of Out-of-Scope Supplies']);
                foreach ($report['otherTransactions'] as $row) {
                    fputcsv($handle, [$row['activity_number'], $this->money($row['taxable']), '0.00', '0.00', '0.00']);
                }
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function buildReport(Request $request): array
    {
        $storedSettings = GstSetting::query()->first();
        $this->activityNumbers = [
            'moto' => $storedSettings?->activity_number_moto ?: config('gst.activity_numbers.moto'),
            'cool' => $storedSettings?->activity_number_cool ?: config('gst.activity_numbers.cool'),
            'ac' => $storedSettings?->activity_number_cool ?: config('gst.activity_numbers.ac'),
            'it' => $storedSettings?->activity_number_it ?: config('gst.activity_numbers.it'),
            'easyfix' => $storedSettings?->activity_number_easyfix ?: config('gst.activity_numbers.easyfix'),
            'shared' => $storedSettings?->activity_number_shared ?: config('gst.activity_numbers.shared'),
        ];
        $validated = $request->validate([
            'period_type' => ['nullable', Rule::in(['monthly', 'quarterly'])],
            'year' => ['nullable', 'integer', 'between:2000,2100'],
            'month' => ['nullable', 'integer', 'between:1,12'],
            'quarter' => ['nullable', 'integer', 'between:1,4'],
        ]);

        $periodType = $validated['period_type'] ?? 'monthly';
        $year = (int) ($validated['year'] ?? now()->year);
        $month = (int) ($validated['month'] ?? now()->month);
        $quarter = (int) ($validated['quarter'] ?? now()->quarter);
        $startDate = $periodType === 'quarterly'
            ? Carbon::create($year, (($quarter - 1) * 3) + 1, 1)->startOfMonth()
            : Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $periodType === 'quarterly' ? $startDate->copy()->addMonths(2)->endOfMonth() : $startDate->copy()->endOfMonth();

        $inputExpenses = Expense::with(['vendorEntity', 'category'])
            ->where('is_gst_applicable', true)
            ->whereBetween('incurred_at', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('incurred_at')->orderBy('id')->get();

        $sales = DailySalesLog::with(['lines', 'customer', 'job'])
            ->whereIn('status', ['submitted', DailySalesLog::STATUS_INVOICED, DailySalesLog::STATUS_PARTIAL_PAID, DailySalesLog::STATUS_PAID])
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('date')->orderBy('id')->get();

        $outputInvoices = $sales->map(function (DailySalesLog $sale) {
            $taxable = round((float) $sale->lines->where('is_gst_applicable', true)->sum('line_total'), 2);
            $gst = round((float) $sale->lines->where('is_gst_applicable', true)->sum('gst_amount'), 2);
            $unclassified = round((float) $sale->lines->where('is_gst_applicable', false)->sum('line_total'), 2);
            $tin = strtoupper(trim((string) $sale->customer?->gst_number));

            return [
                'sale' => $sale,
                'date' => $sale->date,
                'invoice_number' => 'JOB-' . str_pad((string) ($sale->job_id ?: $sale->id), 5, '0', STR_PAD_LEFT),
                'customer_name' => $sale->job?->customer_name ?? $sale->customer?->name ?? 'Walk-in',
                'customer_tin' => $tin,
                'is_gst_customer' => (bool) preg_match('/^\d{7}GST\d{3}$/', $tin),
                'taxable' => $taxable,
                'gst' => $gst,
                'unclassified' => $unclassified,
                'activity_number' => $this->activityNumber($sale->business_unit),
            ];
        })->filter(fn (array $invoice) => $invoice['taxable'] > 0 || $invoice['unclassified'] > 0)->values();

        $taxInvoices = $outputInvoices->where('is_gst_customer', true)->where('taxable', '>', 0)->values();
        $otherTransactions = $outputInvoices->where('is_gst_customer', false)->where('taxable', '>', 0)
            ->groupBy('activity_number')->map(fn (Collection $rows, $activity) => [
                'activity_number' => $activity,
                'taxable' => round((float) $rows->sum('taxable'), 2),
                'gst' => round((float) $rows->sum('gst'), 2),
            ])->values();

        $outputGst = round((float) $outputInvoices->sum('gst'), 2);
        $inputGst = round((float) $inputExpenses->sum('gst_amount'), 2);
        $unclassifiedSales = round((float) $outputInvoices->sum('unclassified'), 2);
        $missingActivities = collect($outputInvoices->pluck('sale.business_unit'))->merge($inputExpenses->pluck('business_unit'))
            ->unique()->filter(fn ($unit) => blank($this->activityNumber((string) $unit)))->values();

        return compact('periodType', 'year', 'month', 'quarter', 'startDate', 'endDate', 'inputExpenses', 'outputInvoices', 'taxInvoices', 'otherTransactions', 'outputGst', 'inputGst', 'unclassifiedSales', 'missingActivities') + [
            'netGst' => round($outputGst - $inputGst, 2),
            'taxpayerTin' => $storedSettings?->taxpayer_tin ?: config('gst.taxpayer_tin'),
        ];
    }

    private function activityNumber(string $unit): string
    {
        return (string) ($this->activityNumbers[$unit] ?? '');
    }

    private function money($amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }
}
