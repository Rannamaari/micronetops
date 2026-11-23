<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Job Desk: acceptance tracking, "invoice given" stamp, customer-contact
 * tracking and per-user language.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('job_assignees', 'accepted_at')) {
            Schema::table('job_assignees', function (Blueprint $table) {
                $table->timestamp('accepted_at')->nullable();
            });
        }

        Schema::table('jobs', function (Blueprint $table) {
            if (!Schema::hasColumn('jobs', 'invoiced_at')) {
                $table->timestamp('invoiced_at')->nullable();
            }
            if (!Schema::hasColumn('jobs', 'customer_contact_required_since')) {
                $table->timestamp('customer_contact_required_since')->nullable();
            }
            if (!Schema::hasColumn('jobs', 'customer_contact_reason')) {
                $table->string('customer_contact_reason', 50)->nullable();
            }
            if (!Schema::hasColumn('jobs', 'customer_last_contacted_at')) {
                $table->timestamp('customer_last_contacted_at')->nullable();
            }
        });

        if (!Schema::hasColumn('users', 'locale')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('locale', 5)->default('en');
            });
        }

        // Backfill so existing work does not flood the board with alerts on day one.
        DB::table('job_assignees')
            ->whereNull('accepted_at')
            ->update(['accepted_at' => DB::raw('COALESCE(assigned_at, CURRENT_TIMESTAMP)')]);

        DB::table('jobs')
            ->where('status', 'completed')
            ->whereNull('invoiced_at')
            ->update(['invoiced_at' => DB::raw('COALESCE(completed_at, CURRENT_TIMESTAMP)')]);
    }

    public function down(): void
    {
        Schema::table('job_assignees', function (Blueprint $table) {
            $table->dropColumn('accepted_at');
        });

        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn([
                'invoiced_at',
                'customer_contact_required_since',
                'customer_contact_reason',
                'customer_last_contacted_at',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('locale');
        });
    }
};
