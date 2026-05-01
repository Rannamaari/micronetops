<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_sales_lines', function (Blueprint $table) {
            $table->unsignedInteger('warranty_value')->nullable()->after('note');
            $table->string('warranty_unit', 20)->nullable()->after('warranty_value');
        });

        Schema::table('job_items', function (Blueprint $table) {
            $table->unsignedInteger('warranty_value')->nullable()->after('item_description');
            $table->string('warranty_unit', 20)->nullable()->after('warranty_value');
        });
    }

    public function down(): void
    {
        Schema::table('daily_sales_lines', function (Blueprint $table) {
            $table->dropColumn(['warranty_value', 'warranty_unit']);
        });

        Schema::table('job_items', function (Blueprint $table) {
            $table->dropColumn(['warranty_value', 'warranty_unit']);
        });
    }
};
