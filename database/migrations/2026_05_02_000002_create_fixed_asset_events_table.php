<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fixed_asset_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixed_asset_id')->constrained()->cascadeOnDelete();
            $table->string('event_type');
            $table->string('old_status')->nullable();
            $table->string('new_status')->nullable();
            $table->string('old_condition')->nullable();
            $table->string('new_condition')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('event_at');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['fixed_asset_id', 'event_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fixed_asset_events');
    }
};
