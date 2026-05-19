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
        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedBigInteger('recurring_booking_id')->nullable()->after('parent_id');
            $table->foreign('recurring_booking_id')->references('id')->on('recurring_bookings')->onDelete('set null');
            $table->index('recurring_booking_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['recurring_booking_id']);
            $table->dropIndex(['recurring_booking_id']);
            $table->dropColumn('recurring_booking_id');
        });
    }
};
