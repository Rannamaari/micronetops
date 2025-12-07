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
        Schema::table('employee_attendance', function (Blueprint $table) {
            // Add absence reason: sick_leave, unpaid_leave, or null for normal absent
            $table->enum('absence_reason', ['sick_leave', 'unpaid_leave'])->nullable()->after('status');
            $table->foreignId('leave_id')->nullable()->constrained('employee_leaves')->nullOnDelete()->after('absence_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_attendance', function (Blueprint $table) {
            $table->dropColumn(['absence_reason', 'leave_id']);
        });
    }
};
