<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employee_salaries', function (Blueprint $table) {
            $table->integer('absent_days')->default(0)->after('loan_deduction');
            $table->decimal('absent_deduction', 10, 2)->default(0)->after('absent_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_salaries', function (Blueprint $table) {
            $table->dropColumn(['absent_days', 'absent_deduction']);
        });
    }
};
