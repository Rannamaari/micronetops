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
        Schema::create('ac_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('brand')->nullable();
            $table->integer('btu')->nullable(); // 12000, 18000, etc.
            $table->string('gas_type')->nullable(); // R32, R410
            $table->integer('indoor_units')->default(1);
            $table->integer('outdoor_units')->default(1);
            $table->date('last_service_at')->nullable();
            $table->string('location_description')->nullable(); // e.g. "Master bedroom"
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ac_units');
    }
};
