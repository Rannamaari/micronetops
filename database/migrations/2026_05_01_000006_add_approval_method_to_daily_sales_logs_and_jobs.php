<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_sales_logs', function (Blueprint $table) {
            $table->string('approval_method', 20)->default('po')->after('po_number');
        });

        Schema::table('jobs', function (Blueprint $table) {
            $table->string('approval_method', 20)->default('po')->after('po_number');
        });
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn('approval_method');
        });

        Schema::table('daily_sales_logs', function (Blueprint $table) {
            $table->dropColumn('approval_method');
        });
    }
};
