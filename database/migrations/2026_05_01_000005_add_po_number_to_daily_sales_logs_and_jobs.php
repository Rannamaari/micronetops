<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_sales_logs', function (Blueprint $table) {
            $table->string('po_number', 100)->nullable()->after('customer_address_text');
        });

        Schema::table('jobs', function (Blueprint $table) {
            $table->string('po_number', 100)->nullable()->after('customer_notes');
        });
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn('po_number');
        });

        Schema::table('daily_sales_logs', function (Blueprint $table) {
            $table->dropColumn('po_number');
        });
    }
};
