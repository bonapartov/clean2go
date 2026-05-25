<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('onboarding_completed')->default(false)->after('id');
            $table->tinyInteger('onboarding_step')->default(0)->after('onboarding_completed');
            $table->boolean('onboarding_blocked')->default(false)->after('onboarding_step');
            $table->text('onboarding_blocked_reason')->nullable()->after('onboarding_blocked');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['onboarding_completed', 'onboarding_step', 'onboarding_blocked', 'onboarding_blocked_reason']);
        });
    }
};
