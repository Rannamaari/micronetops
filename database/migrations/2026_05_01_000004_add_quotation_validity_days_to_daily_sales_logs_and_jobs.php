<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_sales_logs', function (Blueprint $table) {
            $table->unsignedInteger('quotation_validity_days')->default(3)->after('due_date');
        });

        Schema::table('jobs', function (Blueprint $table) {
            $table->unsignedInteger('quotation_validity_days')->default(3)->after('due_date');
        });
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn('quotation_validity_days');
        });

        Schema::table('daily_sales_logs', function (Blueprint $table) {
            $table->dropColumn('quotation_validity_days');
        });
    }
};
