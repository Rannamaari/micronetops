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
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->boolean('is_service')->default(false)->after('category');
        });

        Schema::table('job_items', function (Blueprint $table) {
            $table->boolean('is_service')->default(false)->after('inventory_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropColumn('is_service');
        });

        Schema::table('job_items', function (Blueprint $table) {
            $table->dropColumn('is_service');
        });
    }
};
