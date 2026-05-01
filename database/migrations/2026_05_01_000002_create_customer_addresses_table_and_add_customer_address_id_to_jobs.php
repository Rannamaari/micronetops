<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('label', 50);
            $table->string('address', 500);
            $table->string('contact_name')->nullable();
            $table->string('contact_phone', 50)->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::table('jobs', function (Blueprint $table) {
            $table->foreignId('customer_address_id')->nullable()->after('customer_id')->constrained('customer_addresses')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('customer_address_id');
        });

        Schema::dropIfExists('customer_addresses');
    }
};
