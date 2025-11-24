<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('petty_cash', function (Blueprint $table) {
            // type of transaction: topup / expense / adjustment
            $table->string('type')->default('expense');

            // simple category (fuel, parts, food, misc...)
            $table->string('category')->nullable();

            // date/time when cash was actually handed over
            $table->timestamp('paid_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('petty_cash', function (Blueprint $table) {
            $table->dropColumn(['type', 'category', 'paid_at']);
        });
    }
};
