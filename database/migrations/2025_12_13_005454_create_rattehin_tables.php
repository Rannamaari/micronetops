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
        // Bills table - main bill information
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('restaurant_name')->nullable();
            $table->decimal('service_charge_percentage', 5, 2)->default(0);
            $table->boolean('gst_enabled')->default(false);
            $table->boolean('gst_on_service')->default(false);
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('service_charge_amount', 10, 2)->default(0);
            $table->decimal('gst_amount', 10, 2)->default(0);
            $table->decimal('grand_total', 10, 2)->default(0);
            $table->string('currency', 3)->default('MVR');
            $table->text('ocr_extracted_text')->nullable();
            $table->json('calculation_metadata')->nullable();
            $table->timestamps();
        });

        // Bill participants - people sharing the bill
        Schema::create('bill_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('personal_items', 10, 2)->default(0);
            $table->decimal('shared_items', 10, 2)->default(0);
            $table->decimal('service_charge', 10, 2)->default(0);
            $table->decimal('gst', 10, 2)->default(0);
            $table->timestamps();
        });

        // Bill items - individual items on the bill (personal items)
        Schema::create('bill_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->json('assigned_to')->nullable(); // Array of participant names
            $table->boolean('is_from_ocr')->default(false);
            $table->decimal('ocr_confidence', 5, 2)->nullable();
            $table->timestamps();
        });

        // Bill shared items - items shared among all participants
        Schema::create('bill_shared_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->boolean('is_from_ocr')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_shared_items');
        Schema::dropIfExists('bill_items');
        Schema::dropIfExists('bill_participants');
        Schema::dropIfExists('bills');
    }
};
