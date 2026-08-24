<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('daily_sales_lines', 'sort_order')) {
            Schema::table('daily_sales_lines', function (Blueprint $table) {
                $table->unsignedInteger('sort_order')->nullable()->after('inventory_item_id');
            });
        }

        if (!Schema::hasColumn('purchase_order_lines', 'sort_order')) {
            Schema::table('purchase_order_lines', function (Blueprint $table) {
                $table->unsignedInteger('sort_order')->nullable()->after('inventory_item_id');
            });
        }

        $dailyGroups = DB::table('daily_sales_lines')
            ->select('id', 'daily_sales_log_id')
            ->orderBy('daily_sales_log_id')
            ->orderBy('id')
            ->get()
            ->groupBy('daily_sales_log_id');

        foreach ($dailyGroups as $group) {
            foreach ($group->values() as $index => $line) {
                DB::table('daily_sales_lines')
                    ->where('id', $line->id)
                    ->update(['sort_order' => $index + 1]);
            }
        }

        $poGroups = DB::table('purchase_order_lines')
            ->select('id', 'purchase_order_id')
            ->orderBy('purchase_order_id')
            ->orderBy('id')
            ->get()
            ->groupBy('purchase_order_id');

        foreach ($poGroups as $group) {
            foreach ($group->values() as $index => $line) {
                DB::table('purchase_order_lines')
                    ->where('id', $line->id)
                    ->update(['sort_order' => $index + 1]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('daily_sales_lines', 'sort_order')) {
            Schema::table('daily_sales_lines', function (Blueprint $table) {
                $table->dropColumn('sort_order');
            });
        }

        if (Schema::hasColumn('purchase_order_lines', 'sort_order')) {
            Schema::table('purchase_order_lines', function (Blueprint $table) {
                $table->dropColumn('sort_order');
            });
        }
    }
};
