<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // adjustment | transfer_in | transfer_out | eod_deposit | reimbursement
            $table->decimal('amount', 12, 2); // positive or negative
            $table->date('occurred_at');
            $table->string('description')->nullable();
            $table->string('related_type')->nullable();
            $table->unsignedBigInteger('related_id')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['account_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_transactions');
    }
};
