<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('easyfix_user_id')->nullable()->after('notes');
            $table->index('easyfix_user_id');
        });

        Schema::table('jobs', function (Blueprint $table) {
            $table->string('easyfix_job_id')->nullable()->after('customer_notes');
            $table->string('easyfix_quote_id')->nullable()->after('easyfix_job_id');
            $table->string('easyfix_invoice_id')->nullable()->after('easyfix_quote_id');

            $table->index('easyfix_job_id');
            $table->index('easyfix_quote_id');
            $table->index('easyfix_invoice_id');
        });
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropIndex(['easyfix_job_id']);
            $table->dropIndex(['easyfix_quote_id']);
            $table->dropIndex(['easyfix_invoice_id']);
            $table->dropColumn(['easyfix_job_id', 'easyfix_quote_id', 'easyfix_invoice_id']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['easyfix_user_id']);
            $table->dropColumn('easyfix_user_id');
        });
    }
};
