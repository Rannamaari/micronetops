<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fixed_assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_code')->unique();
            $table->string('name');
            $table->string('category')->nullable();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->enum('condition', ['Good', 'Needs Repair', 'Damaged'])->default('Good');
            $table->enum('status', ['Available', 'Assigned', 'Under Repair', 'Retired', 'Lost'])->default('Available');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('fixed_asset_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixed_asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('assigned_at');
            $table->dateTime('returned_at')->nullable();
            $table->enum('condition_on_assign', ['Good', 'Needs Repair', 'Damaged']);
            $table->enum('condition_on_return', ['Good', 'Needs Repair', 'Damaged'])->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['staff_id', 'returned_at']);
            $table->index(['fixed_asset_id', 'returned_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fixed_asset_assignments');
        Schema::dropIfExists('fixed_assets');
    }
};
