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
        Schema::create('booking_payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->unsignedBigInteger('payment_transaction_id');
            $table->string('payment_type')->default('full')->comment('advance, remaining, full');
            $table->timestamps();

            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');
            $table->foreign('payment_transaction_id')->references('id')->on('payment_gateways_transactions')->onDelete('cascade');
            $table->index(['booking_id', 'payment_transaction_id'], 'idx_booking_payment_trans');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_payment_transactions');
    }
};
