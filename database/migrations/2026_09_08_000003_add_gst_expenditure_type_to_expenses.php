<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('gst_expenditure_type', 20)->nullable();
        });

        DB::table('expenses')->where('is_gst_applicable', true)
            ->update(['gst_expenditure_type' => 'revenue']);
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('gst_expenditure_type');
        });
    }
};
