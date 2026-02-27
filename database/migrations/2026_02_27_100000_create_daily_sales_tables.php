<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_sales_logs', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('business_unit'); // 'moto' or 'cool'
            $table->string('status')->default('draft'); // 'draft' or 'submitted'
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['date', 'business_unit']);
        });

        Schema::create('daily_sales_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_sales_log_id')->constrained('daily_sales_logs')->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->nullable()->constrained('inventory_items')->nullOnDelete();
            $table->string('description');
            $table->integer('qty')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->string('payment_method'); // 'cash' or 'transfer'
            $table->decimal('line_total', 10, 2);
            $table->boolean('is_stock_item')->default(false);
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_sales_lines');
        Schema::dropIfExists('daily_sales_logs');
    }
};
