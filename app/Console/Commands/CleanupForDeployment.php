<?php

namespace App\Console\Commands;

use App\Models\InventoryItem;
use App\Models\User;
use Illuminate\Console\Command;

class CleanupForDeployment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cleanup-for-deployment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up database for deployment: Remove non-admin users and all inventory items';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting cleanup for deployment...');

        // Delete all inventory items (services and parts)
        $inventoryCount = InventoryItem::count();
        InventoryItem::query()->delete();
        $this->info("Deleted {$inventoryCount} inventory items (services and parts).");

        // Delete all users who are NOT admins
        $adminUsers = User::whereHas('roles', function($query) {
            $query->where('name', 'admin')->orWhere('slug', 'admin');
        })->pluck('id');

        $nonAdminCount = User::whereNotIn('id', $adminUsers)->count();
        User::whereNotIn('id', $adminUsers)->delete();
        $this->info("Deleted {$nonAdminCount} non-admin users.");

        $remainingUsers = User::count();
        $this->info("Remaining users: {$remainingUsers} (all should be admins).");

        $this->info('✅ Cleanup completed successfully!');
        return Command::SUCCESS;
    }
}
