<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE fixed_assets DROP CONSTRAINT IF EXISTS fixed_assets_status_check');
            DB::statement("ALTER TABLE fixed_assets ADD CONSTRAINT fixed_assets_status_check CHECK (status IN ('Available', 'Assigned', 'Under Repair', 'Retired', 'Lost', 'Sold'))");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE fixed_assets DROP CONSTRAINT IF EXISTS fixed_assets_status_check');
            DB::statement("ALTER TABLE fixed_assets ADD CONSTRAINT fixed_assets_status_check CHECK (status IN ('Available', 'Assigned', 'Under Repair', 'Retired', 'Lost'))");
        }
    }
};
