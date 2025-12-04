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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_number')->unique();
            $table->string('name');
            $table->string('email')->nullable()->unique();
            $table->string('phone');
            $table->string('secondary_phone')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();

            // Employment details
            $table->string('type'); // full-time, part-time, contract
            $table->string('position');
            $table->string('department')->nullable();
            $table->date('hire_date');
            $table->string('status')->default('active'); // active, inactive, terminated

            // Address and personal
            $table->text('address')->nullable();
            $table->string('nationality')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('id_number')->nullable(); // National ID or passport number

            // Salary
            $table->decimal('basic_salary', 10, 2)->default(0);

            // Photo
            $table->string('photo_path')->nullable();

            // Notes
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
