<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * «Застраховать услугу» (ROADMAP Sprint 6): snapshot of the client's
 * choice on the booking, taken at checkout by App\Support\ServiceInsurance.
 * insurance_insurer keeps the insurer's name as it was at order time, so a
 * later change in settings does not rewrite past orders.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->boolean('is_insured')->default(false)->after('total');
            $table->decimal('insurance_amount', 10, 2)->default(0)->after('is_insured');
            $table->string('insurance_insurer')->nullable()->after('insurance_amount');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['is_insured', 'insurance_amount', 'insurance_insurer']);
        });
    }
};
