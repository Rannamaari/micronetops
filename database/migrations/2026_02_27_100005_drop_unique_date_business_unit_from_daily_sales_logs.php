<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_sales_logs', function (Blueprint $table) {
            $table->dropUnique('daily_sales_logs_date_business_unit_unique');
        });
    }

    public function down(): void
    {
        Schema::table('daily_sales_logs', function (Blueprint $table) {
            $table->unique(['date', 'business_unit']);
        });
    }
};
