<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('employees') || Schema::hasColumn('employees', 'termination_date')) {
            return;
        }

        Schema::table('employees', function (Blueprint $table) {
            $table->date('termination_date')->nullable()->after('hire_date');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('employees') || !Schema::hasColumn('employees', 'termination_date')) {
            return;
        }

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('termination_date');
        });
    }
};
