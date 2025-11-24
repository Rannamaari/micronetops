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
        Schema::table('petty_cash', function (Blueprint $table) {
            $table->foreignId('source_payment_id')->nullable()->after('approved_by')->constrained('payments')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('petty_cash', function (Blueprint $table) {
            $table->dropForeign(['source_payment_id']);
            $table->dropColumn('source_payment_id');
        });
    }
};
