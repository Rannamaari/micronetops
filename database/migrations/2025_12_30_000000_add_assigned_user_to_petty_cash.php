<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('petty_cash', function (Blueprint $table) {
            // Track which user this petty cash belongs to (for topups/allocations)
            // Different from user_id which tracks who created the expense
            $table->foreignId('assigned_to')->nullable()->after('user_id')->constrained('users')->nullOnDelete();

            // Add index for faster queries
            $table->index('assigned_to');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('petty_cash', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->dropIndex(['assigned_to']);
            $table->dropColumn('assigned_to');
        });
    }
};
