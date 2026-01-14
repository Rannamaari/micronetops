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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->index();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            // website / referral / social / walk-in / phone / whatsapp / other
            $table->string('source')->default('walk-in');
            // new / contacted / interested / qualified / converted / lost
            $table->string('status')->default('new')->index();
            // high / medium / low
            $table->string('priority')->default('medium');
            // moto / ac / both
            $table->string('interested_in')->default('moto');
            $table->text('notes')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->timestamp('last_contact_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('converted_to_customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->timestamp('converted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
