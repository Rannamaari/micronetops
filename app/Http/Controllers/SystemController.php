<?php

namespace App\Http\Controllers;

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

        return view('system.settings');
    }

    /**
     * Purge all data from the system (admin only)
     * This deletes jobs, inventory, and petty cash but KEEPS customers
     * Useful for testing and starting fresh while preserving customer data
     */
    public function purgeAllData(Request $request)
    {
        // Only admins can purge data
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized. Only administrators can purge data.');
        }

        try {
            DB::beginTransaction();

            // Delete in order to avoid foreign key constraint violations
            // NOTE: Customers, vehicles, and AC units are preserved

            // 1. Delete payments (child of jobs)
            DB::table('payments')->delete();

            // 2. Delete job_items (child of both jobs and inventory_items)
            DB::table('job_items')->delete();

            // 3. Delete inventory_logs (child of both jobs and inventory_items)
            DB::table('inventory_logs')->delete();

            // 4. Delete jobs (parent, also cascades to related data)
            DB::table('jobs')->delete();

            // 5. Delete inventory_items
            DB::table('inventory_items')->delete();

            // 6. Delete petty_cash entries
            DB::table('petty_cash')->delete();

            // NOTE: We do NOT delete:
            // - customers
            // - vehicles
            // - ac_units
            // - road_worthiness_history (tied to vehicles)
            // - inventory_categories
            // - users

            DB::commit();

            Log::info('System data purged by admin user: ' . auth()->user()->email);

            return redirect()->back()->with('success', 'Data purged successfully! Jobs, inventory, and petty cash have been deleted. Customers and their data have been preserved.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to purge system data: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Failed to purge data: ' . $e->getMessage());
        }
    }
}
