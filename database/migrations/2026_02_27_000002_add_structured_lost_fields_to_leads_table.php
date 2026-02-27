<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('lost_reason_id')->nullable()->after('lost_reason');
            $table->text('lost_notes')->nullable()->after('lost_reason_id');
            $table->timestamp('lost_at')->nullable()->after('lost_notes');
            $table->foreignId('lost_by')->nullable()->after('lost_at')
                  ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['lost_by']);
            $table->dropColumn(['lost_reason_id', 'lost_notes', 'lost_at', 'lost_by']);
        });
    }
};
