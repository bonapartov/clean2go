<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * App-to-app calls (client ↔ executor) over our own LiveKit server.
 * One row per call; the LiveKit room name is unique per call.
 * Lifecycle: ringing → accepted → ended, or ringing → declined / cancelled
 * / missed. See App\Http\Controllers\API\CallController.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->foreignId('caller_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('callee_id')->constrained('users')->cascadeOnDelete();
            $table->string('room', 64)->unique();
            $table->enum('type', ['audio', 'video'])->default('audio');
            $table->enum('status', ['ringing', 'accepted', 'declined', 'cancelled', 'missed', 'ended'])->default('ringing');
            $table->timestamp('answered_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->index(['callee_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calls');
    }
};
