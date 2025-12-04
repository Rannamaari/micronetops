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
        Schema::create('employee_salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();

            // Period
            $table->integer('month'); // 1-12
            $table->integer('year');

            // Salary breakdown
            $table->decimal('basic_salary', 10, 2)->default(0);
            $table->decimal('allowances', 10, 2)->default(0);
            $table->decimal('bonuses', 10, 2)->default(0);
            $table->decimal('overtime', 10, 2)->default(0);

            // Deductions
            $table->decimal('loan_deduction', 10, 2)->default(0);
            $table->decimal('other_deductions', 10, 2)->default(0);

            // Net salary
            $table->decimal('gross_salary', 10, 2)->default(0);
            $table->decimal('total_deductions', 10, 2)->default(0);
            $table->decimal('net_salary', 10, 2)->default(0);

            // Payment status
            $table->string('status')->default('pending'); // pending, paid
            $table->date('payment_date')->nullable();
            $table->string('payment_method')->nullable(); // cash, bank_transfer
            $table->string('reference')->nullable(); // Transaction reference

            $table->text('notes')->nullable();
            $table->timestamps();

            // Unique constraint - one salary record per employee per month
            $table->unique(['employee_id', 'month', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_salaries');
    }
};
