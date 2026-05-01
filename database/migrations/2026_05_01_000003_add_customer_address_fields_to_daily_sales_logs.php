<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_sales_logs', function (Blueprint $table) {
            $table->foreignId('customer_address_id')->nullable()->after('customer_id')->constrained('customer_addresses')->nullOnDelete();
            $table->string('customer_address_text', 500)->nullable()->after('customer_address_id');
        });
    }

    public function down(): void
    {
        Schema::table('daily_sales_logs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('customer_address_id');
            $table->dropColumn('customer_address_text');
        });
    }
};
