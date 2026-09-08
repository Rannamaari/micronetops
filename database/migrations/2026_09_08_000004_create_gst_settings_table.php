<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gst_settings', function (Blueprint $table) {
            $table->id();
            $table->string('taxpayer_tin', 50)->nullable();
            $table->string('activity_number_moto', 50)->nullable();
            $table->string('activity_number_cool', 50)->nullable();
            $table->string('activity_number_it', 50)->nullable();
            $table->string('activity_number_easyfix', 50)->nullable();
            $table->string('activity_number_shared', 50)->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gst_settings');
    }
};
