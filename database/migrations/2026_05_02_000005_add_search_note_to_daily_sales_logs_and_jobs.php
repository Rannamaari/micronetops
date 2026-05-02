<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_sales_logs', function (Blueprint $table) {
            $table->text('search_note')->nullable()->after('notes');
        });

        Schema::table('jobs', function (Blueprint $table) {
            $table->text('search_note')->nullable()->after('customer_notes');
        });
    }

    public function down(): void
    {
        Schema::table('daily_sales_logs', function (Blueprint $table) {
            $table->dropColumn('search_note');
        });

        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn('search_note');
        });
    }
};
