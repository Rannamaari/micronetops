<?php

namespace App\Console\Commands;

use App\Models\Vendor;
use Illuminate\Console\Command;

class DedupeVendors extends Command
{
    protected $signature = 'vendors:dedupe {--dry-run : Show what would change without deleting}';

    protected $description = 'Remove duplicate vendors by phone number, keeping the earliest record and reassigning expenses.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $groups = Vendor::query()
            ->select('phone')
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->groupBy('phone')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('phone');

        if ($groups->isEmpty()) {
            $this->info('No duplicate vendors found.');
            return self::SUCCESS;
        }

        foreach ($groups as $phone) {
            $vendors = Vendor::where('phone', $phone)->orderBy('id')->get();
            $keeper = $vendors->first();
            $duplicates = $vendors->slice(1);

            $this->info("Phone {$phone}: keep #{$keeper->id} ({$keeper->name}), remove " . $duplicates->pluck('id')->join(', '));

            foreach ($duplicates as $dup) {
                if (!$dryRun) {
                    $dup->expenses()->update(['vendor_id' => $keeper->id, 'vendor' => $keeper->name]);
                    $dup->delete();
                }
            }
        }

        if ($dryRun) {
            $this->info('Dry run complete. No changes made.');
        } else {
            $this->info('Deduplication complete.');
        }

        return self::SUCCESS;
    }
}
