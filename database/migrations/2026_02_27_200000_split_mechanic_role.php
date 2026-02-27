<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Migrate existing 'mechanic' users to 'moto_mechanic' as safe default.
     */
    public function up(): void
    {
        DB::table('users')
            ->where('role', 'mechanic')
            ->update(['role' => 'moto_mechanic']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('users')
            ->whereIn('role', ['moto_mechanic', 'ac_mechanic'])
            ->update(['role' => 'mechanic']);
    }
};
