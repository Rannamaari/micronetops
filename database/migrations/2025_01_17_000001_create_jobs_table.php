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
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            // moto / ac
            $table->string('job_type');
            // walkin / pickup / ac_service / ac_install / ac_repair
            $table->string('job_category');
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ac_unit_id')->nullable()->constrained()->nullOnDelete();
            // For AC jobs or moto pickups
            $table->string('address')->nullable();
            $table->string('pickup_location')->nullable(); // phase1, phase2, highway, etc.
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete(); // mechanic/tech
            $table->string('status')->default('pending'); 
            // pending / assigned / in_progress / completed / delivered / cancelled
            $table->text('problem_description')->nullable();
            $table->text('internal_notes')->nullable();
            $table->decimal('labour_total', 10, 2)->default(0);
            $table->decimal('parts_total', 10, 2)->default(0);
            $table->decimal('travel_charges', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
