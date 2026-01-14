<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add item snapshot columns to preserve inventory item data at time of sale.
     * This ensures invoices show the original item details even if inventory changes later.
     */
    public function up(): void
    {
        Schema::table('job_items', function (Blueprint $table) {
            // Snapshot item information at time of adding to job
            $table->string('item_name')->nullable()->after('inventory_item_id');
            $table->text('item_description')->nullable()->after('item_name');

            // Note: unit_price already exists and serves as price snapshot
        });

        // Populate existing records with current item data
        DB::statement("
            UPDATE job_items ji
            SET item_name = COALESCE(ii.name, 'Item'),
                item_description = CONCAT(
                    COALESCE(ii.brand, ''),
                    CASE WHEN ii.brand IS NOT NULL THEN ' - ' ELSE '' END,
                    COALESCE(ii.sku, '')
                )
            FROM inventory_items ii
            WHERE ji.inventory_item_id = ii.id
            AND ji.item_name IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_items', function (Blueprint $table) {
            $table->dropColumn(['item_name', 'item_description']);
        });
    }
};
