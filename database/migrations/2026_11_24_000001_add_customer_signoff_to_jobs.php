<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Job completion SMS (sent once when work is completed) and the optional
 * customer sign-off with an SMS code.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            if (!Schema::hasColumn('jobs', 'completion_sms_sent_at')) {
                $table->timestamp('completion_sms_sent_at')->nullable();
            }
            if (!Schema::hasColumn('jobs', 'signoff_code_hash')) {
                $table->string('signoff_code_hash')->nullable();
            }
            if (!Schema::hasColumn('jobs', 'signoff_code_expires_at')) {
                $table->timestamp('signoff_code_expires_at')->nullable();
            }
            if (!Schema::hasColumn('jobs', 'signoff_code_sent_at')) {
                $table->timestamp('signoff_code_sent_at')->nullable();
            }
            if (!Schema::hasColumn('jobs', 'signoff_code_sends')) {
                $table->unsignedSmallInteger('signoff_code_sends')->default(0);
            }
            if (!Schema::hasColumn('jobs', 'signoff_code_attempts')) {
                $table->unsignedSmallInteger('signoff_code_attempts')->default(0);
            }
            if (!Schema::hasColumn('jobs', 'signed_off_at')) {
                $table->timestamp('signed_off_at')->nullable();
            }
            if (!Schema::hasColumn('jobs', 'signed_off_phone')) {
                $table->string('signed_off_phone', 20)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn([
                'completion_sms_sent_at',
                'signoff_code_hash',
                'signoff_code_expires_at',
                'signoff_code_sent_at',
                'signoff_code_sends',
                'signoff_code_attempts',
                'signed_off_at',
                'signed_off_phone',
            ]);
        });
    }
};
