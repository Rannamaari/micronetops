<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_sales_lines', function (Blueprint $table) {
            $table->boolean('is_gst_applicable')->default(false)->after('line_total');
            $table->decimal('gst_amount', 10, 2)->default(0)->after('is_gst_applicable');
        });
    }

    public function down(): void
    {
        Schema::table('daily_sales_lines', function (Blueprint $table) {
            $table->dropColumn(['is_gst_applicable', 'gst_amount']);
        });
    }
};
