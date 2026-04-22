<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('job_items', 'is_gst_applicable')) {
            return;
        }

        Schema::table('job_items', function (Blueprint $table) {
            $table->boolean('is_gst_applicable')->default(false);
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('job_items', 'is_gst_applicable')) {
            return;
        }

        Schema::table('job_items', function (Blueprint $table) {
            $table->dropColumn('is_gst_applicable');
        });
    }
};

