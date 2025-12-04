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
        Schema::create('employee_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();

            // Loan details
            $table->string('loan_type'); // advance, loan, emergency
            $table->decimal('amount', 10, 2);
            $table->decimal('remaining_balance', 10, 2);
            $table->decimal('monthly_deduction', 10, 2)->default(0);

            // Dates
            $table->date('loan_date');
            $table->date('start_deduction_date')->nullable();

            // Status
            $table->string('status')->default('active'); // active, completed, cancelled

            // Approval
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('approved_date')->nullable();

            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_loans');
    }
};
