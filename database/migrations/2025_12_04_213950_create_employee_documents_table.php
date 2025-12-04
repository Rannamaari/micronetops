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
        Schema::create('employee_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();

            // Document types: passport, work_permit, visa, contract, insurance, driving_license, etc.
            $table->string('document_type');
            $table->string('document_number')->nullable();

            // Dates
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();

            // File attachment
            $table->string('attachment_path')->nullable();

            // Alert settings
            $table->boolean('send_alert')->default(true);
            $table->integer('alert_days_before')->default(30); // Alert 30 days before expiry

            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_documents');
    }
};
