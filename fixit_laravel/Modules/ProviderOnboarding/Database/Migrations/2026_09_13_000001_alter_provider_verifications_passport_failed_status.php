<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // VerifyPassportJob::failed() пишет passport_status = 'failed', когда верификация
        // упала с исключением — этого значения не было в ENUM, из-за чего сам обработчик
        // ошибки падал повторно (Data truncated for column 'passport_status').
        DB::statement("ALTER TABLE provider_verifications MODIFY COLUMN passport_status ENUM('pending','verified','rejected','manual_review','failed') NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE provider_verifications MODIFY COLUMN passport_status ENUM('pending','verified','rejected','manual_review') NULL");
    }
};
