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
        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('wallet_bonus_id')->nullable();
            $table->unsignedBigInteger('wallet_bonus_amount')->nullable();
            $table->integer('is_admin_funded')->default(0)->nullable();
            $table->decimal('max_bonus', 12,2)->default(0)->nullable();

            $table->foreign('wallet_bonus_id')->references('id')->on('wallet_bonuses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['wallet_bonus_id']);
            $table->dropColumn(['wallet_bonus_id','wallet_bonus_amount','is_admin_funded','max_bonus']);
        });
    }
};
