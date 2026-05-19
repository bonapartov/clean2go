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
            $table->boolean('is_scheduled_booking')->default(false)->after('is_advance_payment_enabled');
            $table->string('booking_frequency')->nullable()->after('is_scheduled_booking')->comment('daily, weekly, monthly, yearly, custom');
            $table->date('schedule_start_date')->nullable()->after('booking_frequency');
            $table->date('schedule_end_date')->nullable()->after('schedule_start_date');
            $table->time('schedule_time')->nullable()->after('schedule_end_date');
            $table->json('selected_weekdays')->nullable()->after('schedule_time')->comment('Array of selected weekdays for daily frequency');
            $table->json('scheduled_dates_json')->nullable()->after('selected_weekdays')->comment('JSON array of all scheduled dates and times');
            $table->integer('scheduled_services_count')->nullable()->after('scheduled_dates_json')->comment('Total number of scheduled service instances');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'is_scheduled_booking',
                'booking_frequency',
                'schedule_start_date',
                'schedule_end_date',
                'schedule_time',
                'selected_weekdays',
                'scheduled_dates_json',
                'scheduled_services_count',
            ]);
        });
    }
};
