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
        Schema::create('referral_bonuses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('referrer_id')->index();
            $table->unsignedBigInteger('referred_id')->index();
            $table->decimal('bonus_amount', 8, 2)->default(0.00);
            $table->enum('referrer_type', ['user', 'provider'])->default('user');
            $table->enum('referred_type', ['user', 'provider'])->default('user');
            $table->string('status')->default('pending');
            $table->decimal('booking_amount', 10, 2)->default(0.00);
            $table->decimal('referrer_percentage', 5, 2)->default(0.00);
            $table->decimal('referred_percentage', 5, 2)->default(0.00);
            $table->decimal('referred_bonus_amount', 8, 2)->default(0.00);
            $table->decimal('referrer_bonus_amount', 8, 2)->default(0.00);
            $table->string('currency_symbol')->nullable();
            $table->timestamp('credited_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('referrer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('referred_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_bonuses');
    }
};
