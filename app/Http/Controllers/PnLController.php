<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\InventoryPurchase;
use App\Models\Job;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PnLController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->query('period', 'month'); // month | quarter | year
        $year = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month', now()->month);
        $quarter = (int) $request->query('quarter', (int) ceil(now()->month / 3));

        [$startDate, $endDate] = $this->resolvePeriod($period, $year, $month, $quarter);

        $businessUnits = Expense::getBusinessUnits();
        $units = array_keys($businessUnits);

        // Revenue (accrual): completed jobs in period
        $revenueByUnit = Job::query()
            ->select('job_type', DB::raw('SUM(total_amount) as total'))
            ->where('status', Job::STATUS_COMPLETED)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('completed_at', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->whereNull('completed_at')
                            ->whereBetween('job_date', [$startDate->toDateString(), $endDate->toDateString()]);
                    });
            })
            ->groupBy('job_type')
            ->pluck('total', 'job_type')
            ->all();

        $revenue = $this->normalizeUnits($units, $revenueByUnit);

        // COGS from inventory purchases (accrual for now)
        $purchaseCogsByUnit = InventoryPurchase::query()
            ->select('business_unit', DB::raw('SUM(total_cost) as total'))
            ->whereBetween('purchased_at', [$startDate, $endDate])
            ->groupBy('business_unit')
            ->pluck('total', 'business_unit')
            ->all();

        $purchaseCogs = $this->normalizeUnits($units, $purchaseCogsByUnit);

        // COGS expenses (non-inventory)
        $cogsExpenseByUnit = Expense::query()
            ->select('expenses.business_unit', DB::raw('SUM(expenses.amount) as total'))
            ->join('expense_categories', 'expense_categories.id', '=', 'expenses.expense_category_id')
            ->where('expense_categories.type', ExpenseCategory::TYPE_COGS)
            ->whereBetween('incurred_at', [$startDate, $endDate])
            ->groupBy('expenses.business_unit')
            ->pluck('total', 'expenses.business_unit')
            ->all();

        $cogsExpenses = $this->normalizeUnits($units, $cogsExpenseByUnit);

        $cogs = $this->sumUnits($units, [$purchaseCogs, $cogsExpenses]);

        // Operating expenses
        $opexByUnit = Expense::query()
            ->select('expenses.business_unit', DB::raw('SUM(expenses.amount) as total'))
            ->join('expense_categories', 'expense_categories.id', '=', 'expenses.expense_category_id')
            ->where('expense_categories.type', ExpenseCategory::TYPE_OPERATING)
            ->whereBetween('incurred_at', [$startDate, $endDate])
            ->groupBy('expenses.business_unit')
            ->pluck('total', 'expenses.business_unit')
            ->all();

        $opex = $this->normalizeUnits($units, $opexByUnit);

        $grossProfit = $this->subtractUnits($units, $revenue, $cogs);
        $netProfit = $this->subtractUnits($units, $grossProfit, $opex);

        return view('reports.pnl', [
            'period' => $period,
            'year' => $year,
            'month' => $month,
            'quarter' => $quarter,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'businessUnits' => $businessUnits,
            'revenue' => $revenue,
            'cogs' => $cogs,
            'opex' => $opex,
            'grossProfit' => $grossProfit,
            'netProfit' => $netProfit,
            'purchaseCogs' => $purchaseCogs,
            'cogsExpenses' => $cogsExpenses,
        ]);
    }

    private function resolvePeriod(string $period, int $year, int $month, int $quarter): array
    {
        if ($period === 'year') {
            $start = Carbon::create($year, 1, 1)->startOfDay();
            $end = Carbon::create($year, 12, 31)->endOfDay();
            return [$start, $end];
        }

        if ($period === 'quarter') {
            $quarter = max(1, min(4, $quarter));
            $startMonth = ($quarter - 1) * 3 + 1;
            $start = Carbon::create($year, $startMonth, 1)->startOfDay();
            $end = $start->copy()->addMonths(3)->subDay()->endOfDay();
            return [$start, $end];
        }

        $month = max(1, min(12, $month));
        $start = Carbon::create($year, $month, 1)->startOfDay();
        $end = $start->copy()->endOfMonth()->endOfDay();

        return [$start, $end];
    }

    private function normalizeUnits(array $units, array $data): array
    {
        $normalized = [];
        foreach ($units as $unit) {
            $normalized[$unit] = (float) ($data[$unit] ?? 0);
        }
        return $normalized;
    }

    private function sumUnits(array $units, array $sets): array
    {
        $sum = array_fill_keys($units, 0.0);
        foreach ($sets as $set) {
            foreach ($units as $unit) {
                $sum[$unit] += (float) ($set[$unit] ?? 0);
            }
        }
        return $sum;
    }

    private function subtractUnits(array $units, array $a, array $b): array
    {
        $out = [];
        foreach ($units as $unit) {
            $out[$unit] = (float) ($a[$unit] ?? 0) - (float) ($b[$unit] ?? 0);
        }
        return $out;
    }
}
