<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite doesn't support ALTER COLUMN, so rebuild the constraint
        // Drop the existing foreign key and re-add as nullable
        Schema::table('job_items', function (Blueprint $table) {
            $table->dropForeign(['inventory_item_id']);
        });

        // Make the column nullable
        DB::statement('CREATE TABLE job_items_tmp AS SELECT * FROM job_items');
        Schema::drop('job_items');

        Schema::create('job_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('inventory_item_id')->nullable();
            $table->string('item_name')->nullable();
            $table->string('item_description')->nullable();
            $table->boolean('is_service')->default(false);
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->timestamps();

            $table->foreign('inventory_item_id')->references('id')->on('inventory_items')->nullOnDelete();
        });

        DB::statement('INSERT INTO job_items SELECT * FROM job_items_tmp');
        Schema::drop('job_items_tmp');
    }

    public function down(): void
    {
        // Not reversible cleanly for SQLite
    }
};
