<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Who the client contracted with, as shown before payment (agency model
 * B2C, ROADMAP Sprint 6): name, tax status, ИНН, ОГРН frozen on the booking
 * when it gets its provider. Documents of Sprint 7 (acts, receipts from the
 * executor) read it from here, not from the provider's current profile.
 * Filled by App\Support\ExecutorDisclosure via Booking::saving().
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->json('executor_snapshot')->nullable()->after('provider_id');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('executor_snapshot');
        });
    }
};
