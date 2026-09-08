<?php

namespace App\Http\Controllers;

use App\Models\GstSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SystemController extends Controller
{
    /**
     * Show the admin settings page
     */
    public function settings()
    {
        // Only admins can access
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized. Only administrators can access system settings.');
        }

        $gstSetting = GstSetting::query()->first();

        return view('system.settings', compact('gstSetting'));
    }

    public function updateGstSettings(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized. Only administrators can update GST settings.');
        }

        $validated = $request->validate([
            'taxpayer_tin' => ['required', 'string', 'max:50', 'regex:/^\d{7}GST\d{3}$/i'],
            'activity_number_moto' => ['nullable', 'string', 'max:50'],
            'activity_number_cool' => ['nullable', 'string', 'max:50'],
            'activity_number_it' => ['nullable', 'string', 'max:50'],
            'activity_number_easyfix' => ['nullable', 'string', 'max:50'],
            'activity_number_shared' => ['nullable', 'string', 'max:50'],
        ]);

        $validated = collect($validated)->map(fn ($value) => is_string($value) ? (blank($value) ? null : strtoupper(trim($value))) : $value)->all();
        $validated['updated_by'] = auth()->id();

        GstSetting::query()->updateOrCreate(['id' => 1], $validated);

        return back()->with('success', 'GST reporting settings updated successfully.');
    }

    /**
     * Purge all transactional data from the system (admin only)
     * Keeps: users, roles, customers, vehicles, ac_units, inventory items/categories,
     *        expense categories, accounts, vendors, employees, recurring expenses
     */
    public function purgeAllData(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized. Only administrators can purge data.');
        }

        try {
            DB::beginTransaction();

            // Delete child tables first to avoid FK violations

            // Jobs & related
            DB::table('payments')->delete();
            DB::table('job_items')->delete();
            DB::table('job_notes')->delete();
            DB::table('job_assignees')->delete();
            DB::table('inventory_logs')->delete();
            DB::table('jobs')->delete();

            // Daily sales
            DB::table('daily_sales_lines')->delete();
            DB::table('daily_sales_logs')->delete();

            // Expenses & purchases
            DB::table('inventory_purchases')->delete();
            DB::table('expenses')->delete();

            // EOD
            DB::table('eod_reconciliations')->delete();

            // NOTE: Petty cash is preserved

            // Leads
            DB::table('lead_interactions')->delete();
            DB::table('leads')->delete();

            // Account transactions & transfers
            DB::table('account_transfers')->delete();
            DB::table('account_transactions')->delete();

            // Bills
            DB::table('bill_items')->delete();
            DB::table('bill_shared_items')->delete();
            DB::table('bill_participants')->delete();
            DB::table('bills')->delete();

            // Reset inventory quantities to 0
            DB::table('inventory_items')->update(['quantity' => 0]);

            // Reset account balances to 0
            DB::table('accounts')->update(['balance' => 0]);

            // NOTE: We do NOT delete:
            // - users, roles, user_roles
            // - customers, vehicles, ac_units, road_worthiness_history, insurance_histories
            // - inventory_items, inventory_categories
            // - expense_categories, accounts, vendors, recurring_expenses
            // - petty_cash
            // - employees and payroll data

            DB::commit();

            Log::info('System data purged by admin user: ' . auth()->user()->email);

            return redirect()->back()->with('success', 'All transactional data purged. Jobs, sales, expenses, leads, EOD, and bills deleted. Inventory quantities and account balances reset to zero. Customers, inventory items, categories, petty cash, and settings preserved.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to purge system data: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Failed to purge data: ' . $e->getMessage());
        }
    }
}
