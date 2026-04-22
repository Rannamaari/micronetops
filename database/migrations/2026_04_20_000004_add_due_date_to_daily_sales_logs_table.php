<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('daily_sales_logs', 'due_date')) {
            return;
        }

        Schema::table('daily_sales_logs', function (Blueprint $table) {
            $table->date('due_date')->nullable();
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('daily_sales_logs', 'due_date')) {
            return;
        }

        Schema::table('daily_sales_logs', function (Blueprint $table) {
            $table->dropColumn('due_date');
        });
    }
};

