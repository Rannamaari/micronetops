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
        Schema::table('employees', function (Blueprint $table) {
            // Add only fields that don't exist yet
            $table->text('permanent_address')->nullable()->after('address');
            $table->string('contact_number_home')->nullable()->after('phone');
            $table->string('emergency_contact_relationship')->nullable()->after('emergency_contact_phone');
            $table->decimal('basic_salary_usd', 10, 2)->nullable()->after('basic_salary');
            $table->text('job_description')->nullable()->after('basic_salary_usd');
            $table->string('work_site')->default('Micro Moto H. Goldenmeet aage')->after('job_description');
            $table->enum('work_status', ['permanent', 'contract'])->default('permanent')->after('work_site');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'permanent_address',
                'contact_number_home',
                'emergency_contact_relationship',
                'basic_salary_usd',
                'job_description',
                'work_site',
                'work_status',
            ]);
        });
    }
};
