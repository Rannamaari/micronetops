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
            // Work Permit & Immigration Details
            $table->string('work_permit_number')->nullable()->after('id_number');
            $table->date('date_of_arrival')->nullable()->after('work_permit_number');
            $table->date('work_permit_fee_paid_until')->nullable()->after('date_of_arrival');
            $table->date('quota_slot_fee_paid_until')->nullable()->after('work_permit_fee_paid_until');

            // Passport Details
            $table->string('passport_number')->nullable()->after('quota_slot_fee_paid_until');
            $table->date('passport_expiry_date')->nullable()->after('passport_number');

            // Visa Details
            $table->string('visa_number')->nullable()->after('passport_expiry_date');
            $table->date('visa_expiry_date')->nullable()->after('visa_number');

            // Quota Slot
            $table->string('quota_slot_number')->nullable()->after('visa_expiry_date');

            // Medical & Insurance
            $table->date('medical_checkup_expiry_date')->nullable()->after('quota_slot_number');
            $table->date('insurance_expiry_date')->nullable()->after('medical_checkup_expiry_date');
            $table->string('insurance_number')->nullable()->after('insurance_expiry_date');
            $table->string('insurance_provider')->nullable()->after('insurance_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'work_permit_number',
                'date_of_arrival',
                'work_permit_fee_paid_until',
                'quota_slot_fee_paid_until',
                'passport_number',
                'passport_expiry_date',
                'visa_number',
                'visa_expiry_date',
                'quota_slot_number',
                'medical_checkup_expiry_date',
                'insurance_expiry_date',
                'insurance_number',
                'insurance_provider',
            ]);
        });
    }
};
