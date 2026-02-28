<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eod_reconciliations', function (Blueprint $table) {
            $table->foreignId('deposited_account_id')->nullable()->after('deposited_by')->constrained('accounts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('eod_reconciliations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('deposited_account_id');
        });
    }
};
