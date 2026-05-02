<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sms_messages', function (Blueprint $table) {
            $table->string('status', 30)->default('sent')->after('audience');
            $table->timestamp('scheduled_for')->nullable()->after('content');
            $table->text('error_message')->nullable()->after('failed_count');
        });

        DB::table('sms_messages')
            ->whereNull('sent_at')
            ->update(['status' => 'draft']);
    }

    public function down(): void
    {
        Schema::table('sms_messages', function (Blueprint $table) {
            $table->dropColumn(['status', 'scheduled_for', 'error_message']);
        });
    }
};
