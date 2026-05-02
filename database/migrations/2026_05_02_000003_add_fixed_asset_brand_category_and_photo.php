<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fixed_asset_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code', 10)->unique();
            $table->timestamps();
        });

        Schema::create('fixed_asset_brands', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code', 10)->unique();
            $table->timestamps();
        });

        Schema::table('fixed_assets', function (Blueprint $table) {
            $table->foreignId('fixed_asset_category_id')->nullable()->after('name')->constrained('fixed_asset_categories')->nullOnDelete();
            $table->foreignId('fixed_asset_brand_id')->nullable()->after('fixed_asset_category_id')->constrained('fixed_asset_brands')->nullOnDelete();
            $table->string('photo_path')->nullable()->after('serial_number');
        });
    }

    public function down(): void
    {
        Schema::table('fixed_assets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('fixed_asset_brand_id');
            $table->dropConstrainedForeignId('fixed_asset_category_id');
            $table->dropColumn('photo_path');
        });

        Schema::dropIfExists('fixed_asset_brands');
        Schema::dropIfExists('fixed_asset_categories');
    }
};
