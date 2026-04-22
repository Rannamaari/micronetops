<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('jobs', 'gst_amount')) {
            return;
        }

        Schema::table('jobs', function (Blueprint $table) {
            $table->decimal('gst_amount', 10, 2)->default(0);
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('jobs', 'gst_amount')) {
            return;
        }

        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn('gst_amount');
        });
    }
};

