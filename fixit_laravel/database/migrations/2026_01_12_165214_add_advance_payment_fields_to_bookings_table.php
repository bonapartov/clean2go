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
            $table->decimal('advance_payment_amount', 10, 2)->nullable()->after('total');
            $table->decimal('remaining_payment_amount', 10, 2)->nullable()->after('advance_payment_amount');
            $table->enum('advance_payment_status', ['PENDING', 'PAID', 'REFUNDED'])->default('PENDING')->after('remaining_payment_amount');
            $table->enum('remaining_payment_status', ['PENDING', 'PAID', 'REFUNDED'])->default('PENDING')->after('advance_payment_status');
            $table->boolean('is_advance_payment_enabled')->default(false)->after('remaining_payment_status');
            $table->decimal('advance_payment_percentage', 5, 2)->nullable()->after('is_advance_payment_enabled');
            $table->json('transaction_ids')->nullable()->after('advance_payment_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'advance_payment_amount',
                'remaining_payment_amount',
                'advance_payment_status',
                'remaining_payment_status',
                'is_advance_payment_enabled',
                'advance_payment_percentage',
                'transaction_ids'
            ]);
        });
    }
};
