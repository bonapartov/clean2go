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
        Schema::create('recurring_bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id')->nullable(); // Reference to initial booking
            $table->unsignedBigInteger('consumer_id');
            $table->unsignedBigInteger('provider_id')->nullable();
            $table->unsignedBigInteger('service_id')->nullable();
            $table->unsignedBigInteger('address_id')->nullable();
            
            // Recurring booking details
            $table->enum('frequency', ['weekly', 'monthly', 'yearly'])->default('monthly');
            $table->date('start_date');
            $table->date('end_date')->nullable(); // Optional end date for recurring bookings
            $table->integer('total_occurrences')->nullable(); // Total number of occurrences
            $table->integer('occurrences_completed')->default(0);
            $table->date('next_booking_date')->nullable();
            
            // Subscription details
            $table->string('subscription_id')->nullable(); // Gateway subscription ID (Stripe, PayPal, RazorPay)
            $table->string('payment_method'); // stripe, paypal, razorpay
            $table->string('payment_status')->default('PENDING');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->nullable();
            
            // Status
            $table->boolean('is_active')->default(true);
            $table->enum('status', ['active', 'paused', 'cancelled', 'completed'])->default('active');
            
            // Booking template data (JSON to recreate bookings)
            $table->json('booking_data')->nullable(); // Store all booking details for recreation
            
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');
            $table->foreign('consumer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('provider_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('service_id')->references('id')->on('services')->onDelete('set null');
            $table->foreign('address_id')->references('id')->on('addresses')->onDelete('set null');
            
            $table->index(['consumer_id', 'is_active']);
            $table->index('subscription_id');
            $table->index('next_booking_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_bookings');
    }
};
