<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->string('gst_number', 50)->nullable();
            $table->index('gst_number');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->boolean('is_gst_applicable')->default(false);
            $table->decimal('gst_rate', 5, 2)->default(0);
            $table->decimal('subtotal_amount', 12, 2)->default(0);
            $table->decimal('gst_amount', 12, 2)->default(0);
            $table->index(['is_gst_applicable', 'incurred_at']);
        });

        DB::table('expenses')->update([
            'subtotal_amount' => DB::raw('amount'),
        ]);
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex(['is_gst_applicable', 'incurred_at']);
            $table->dropColumn(['is_gst_applicable', 'gst_rate', 'subtotal_amount', 'gst_amount']);
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->dropIndex(['gst_number']);
            $table->dropColumn('gst_number');
        });
    }
};
