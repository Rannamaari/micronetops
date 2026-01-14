<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add customer snapshot columns to preserve customer data at time of job creation.
     * This ensures invoices remain accurate even if customer details change later.
     */
    public function up(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            // Snapshot customer information at time of job creation
            $table->string('customer_name')->nullable()->after('customer_id');
            $table->string('customer_phone', 50)->nullable()->after('customer_name');
            $table->string('customer_email')->nullable()->after('customer_phone');

            // Note: address column already exists, so we'll use that for snapshot
        });

        // Populate existing records with current customer data
        DB::statement("
            UPDATE jobs j
            SET customer_name = c.name,
                customer_phone = c.phone,
                customer_email = c.email
            FROM customers c
            WHERE j.customer_id = c.id
            AND j.customer_name IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn(['customer_name', 'customer_phone', 'customer_email']);
        });
    }
};
