<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('audience', 50); // manual | all_customers
            $table->string('source', 50)->nullable();
            $table->text('content');
            $table->json('destinations')->nullable();        // full list of numbers
            $table->unsignedInteger('destinations_count')->default(0);
            $table->json('invalid_destinations')->nullable(); // numbers/strings skipped as invalid
            $table->unsignedInteger('invalid_count')->default(0);
            $table->json('responses')->nullable();           // gateway responses (can be multiple chunks)
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['audience', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_messages');
    }
};

