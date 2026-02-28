<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurring_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('expense_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->string('vendor_name')->nullable();
            $table->string('vendor_phone')->nullable();
            $table->string('vendor_contact_name')->nullable();
            $table->string('vendor_address')->nullable();
            $table->string('business_unit')->default('shared');
            $table->decimal('amount', 12, 2);
            $table->string('frequency'); // weekly | monthly
            $table->unsignedTinyInteger('day_of_week')->nullable(); // 0 (Sun) - 6 (Sat)
            $table->unsignedTinyInteger('day_of_month')->nullable(); // 1-31
            $table->date('next_due_at');
            $table->date('last_generated_at')->nullable();
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['frequency', 'next_due_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_expenses');
    }
};
