<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('jobs', 'due_date')) {
            return;
        }

        Schema::table('jobs', function (Blueprint $table) {
            $table->date('due_date')->nullable();
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('jobs', 'due_date')) {
            return;
        }

        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn('due_date');
        });
    }
};

