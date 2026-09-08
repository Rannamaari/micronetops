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
            $table->boolean('is_paid')->default(true)->after('amount');
            $table->date('due_date')->nullable()->after('incurred_at');
            $table->date('paid_at')->nullable()->after('due_date');
            $table->index(['is_paid', 'due_date']);
        });

        DB::table('expenses')->update([
            'is_paid' => true,
            'paid_at' => DB::raw('CAST(incurred_at AS DATE)'),
        ]);
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex(['is_paid', 'due_date']);
            $table->dropColumn(['is_paid', 'due_date', 'paid_at']);
        });
    }
};
