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
        Schema::create('wallet_bonuses', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->enum('type', ['fixed', 'percentage'])->default('fixed')->nullable();
            $table->decimal('bonus', 10, 2)->default(0)->nullable();
            $table->decimal('min_top_up_amount', 10, 2)->default(0)->nullable();
            $table->decimal('max_bonus', 10, 2)->default(0)->nullable();
            $table->integer('status')->default(1)->nullable();
            $table->integer('is_admin_funded')->default(0)->nullable();
            $table->bigInteger('created_by_id')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('created_by_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_bonuses');
    }
};
