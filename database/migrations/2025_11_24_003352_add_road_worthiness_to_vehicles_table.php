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
        Schema::table('vehicles', function (Blueprint $table) {
            $table->timestamp('road_worthiness_created_at')->nullable()->after('mileage');
            $table->timestamp('road_worthiness_expires_at')->nullable()->after('road_worthiness_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['road_worthiness_created_at', 'road_worthiness_expires_at']);
        });
    }
};
