<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            // Scheduling fields
            $table->dateTime('scheduled_at')->nullable()->after('job_date');
            $table->dateTime('scheduled_end_at')->nullable()->after('scheduled_at');

            // Location (island / flat / house name)
            $table->string('location')->nullable()->after('address');

            // Priority
            $table->string('priority')->default('normal')->after('status'); // urgent, high, normal, low

            // Short title for quick identification
            $table->string('title')->nullable()->after('job_type');
        });

        // Update status enum - we'll handle this in the model as Laravel doesn't support enum modification well
        // Status will be: new, scheduled, in_progress, waiting_parts, completed, cancelled
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn(['scheduled_at', 'scheduled_end_at', 'location', 'priority', 'title']);
        });
    }
};
