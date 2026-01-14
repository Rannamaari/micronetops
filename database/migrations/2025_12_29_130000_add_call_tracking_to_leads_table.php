<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->integer('call_attempts')->default(0)->after('last_contact_at');
            $table->string('lost_reason')->nullable()->after('converted_at');
            $table->boolean('do_not_contact')->default(false)->after('lost_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['call_attempts', 'lost_reason', 'do_not_contact']);
        });
    }
};
