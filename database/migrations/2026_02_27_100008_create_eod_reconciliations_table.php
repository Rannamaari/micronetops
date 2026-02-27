<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eod_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('business_unit'); // 'moto' or 'cool'
            $table->string('status')->default('open'); // 'open', 'closed', 'deposited'

            // System-calculated totals
            $table->decimal('expected_cash', 10, 2)->default(0);
            $table->decimal('expected_transfer', 10, 2)->default(0);

            // Denomination counts (Maldivian Rufiyaa)
            $table->integer('note_500')->nullable();
            $table->integer('note_100')->nullable();
            $table->integer('note_50')->nullable();
            $table->integer('note_20')->nullable();
            $table->integer('note_10')->nullable();
            $table->integer('coin_2')->nullable();
            $table->integer('coin_1')->nullable();

            // Computed from denominations
            $table->decimal('counted_cash', 10, 2)->default(0);
            $table->decimal('variance', 10, 2)->default(0);

            $table->text('notes')->nullable();

            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('deposited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('deposited_at')->nullable();

            $table->unique(['date', 'business_unit']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eod_reconciliations');
    }
};
