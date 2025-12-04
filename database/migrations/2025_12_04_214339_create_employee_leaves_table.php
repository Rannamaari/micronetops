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
        Schema::create('employee_leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();

            // Leave details
            $table->string('leave_type'); // sick, annual, unpaid, emergency, maternity, paternity
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('days');

            // Reason and attachments
            $table->text('reason')->nullable();
            $table->string('attachment_path')->nullable(); // Medical certificate, etc.

            // Status and approval
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('approved_date')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_leaves');
    }
};
