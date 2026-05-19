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
        Schema::table('wallet_bonuses', function (Blueprint $table) {
            $table->integer('usage_limit_per_user')->nullable();
            $table->integer('total_usage_limit')->nullable();
            $table->integer('is_unlimited')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallet_bonuses', function (Blueprint $table) {
            //
        });
    }
};
