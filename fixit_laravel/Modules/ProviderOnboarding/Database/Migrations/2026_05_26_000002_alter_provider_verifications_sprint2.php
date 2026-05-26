<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Добавляем 'gph' в ENUM contract_type
        DB::statement("ALTER TABLE provider_verifications MODIFY COLUMN contract_type ENUM('self_employed','ip','ooo','gph') NULL");

        // Добавляем 'pending' в ENUM npd_status
        DB::statement("ALTER TABLE provider_verifications MODIFY COLUMN npd_status ENUM('active','inactive','not_applicable','pending') NULL");

        Schema::table('provider_verifications', function (Blueprint $table) {
            // Версия договора, которую подписал пользователь
            $table->unsignedSmallInteger('contract_version')->nullable()->after('contract_type');

            // Заморозка выплат
            $table->boolean('payments_frozen')->default(false)->after('contract_version');
            $table->string('payments_frozen_reason', 255)->nullable()->after('payments_frozen');
            $table->timestamp('payments_frozen_at')->nullable()->after('payments_frozen_reason');

            $table->index('payments_frozen');
        });
    }

    public function down(): void
    {
        Schema::table('provider_verifications', function (Blueprint $table) {
            $table->dropIndex(['payments_frozen']);
            $table->dropColumn(['contract_version', 'payments_frozen', 'payments_frozen_reason', 'payments_frozen_at']);
        });

        DB::statement("ALTER TABLE provider_verifications MODIFY COLUMN contract_type ENUM('self_employed','ip','ooo') NULL");
        DB::statement("ALTER TABLE provider_verifications MODIFY COLUMN npd_status ENUM('active','inactive','not_applicable') NULL");
    }
};
