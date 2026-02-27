<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_sales_logs', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->after('notes');
            $table->decimal('cash_tendered', 10, 2)->nullable()->after('payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('daily_sales_logs', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'cash_tendered']);
        });
    }
};
