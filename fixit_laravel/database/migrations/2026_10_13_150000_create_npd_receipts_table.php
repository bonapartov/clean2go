<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Receipts of self-employed executors («Мой налог», НПД) — agency model
 * B2C, ROADMAP Sprint 8. One row per (booking, executor). The row is the
 * platform's record of what must be registered with ФНС and what happened;
 * the actual sending goes through a channel (App\Support\Npd) chosen in
 * admin: «Верификация РФ → Интеграции РФ → Чеки самозанятых».
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('npd_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('executor_id')->constrained('users')->cascadeOnDelete();
            $table->string('executor_inn', 12);
            $table->string('service_name');
            $table->decimal('amount', 12, 2);
            $table->timestamp('operation_time')->comment('момент расчёта с клиентом');
            $table->enum('status', ['pending', 'registered', 'failed', 'cancel_pending', 'cancelled'])->default('pending');
            $table->string('channel', 32);
            $table->string('external_id')->nullable()->comment('ID чека в ФНС / у провайдера');
            $table->string('receipt_url', 500)->nullable();
            $table->unsignedInteger('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->string('cancel_reason')->nullable();
            $table->timestamp('registered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->unique(['booking_id', 'executor_id']);
            $table->index('status');
        });

        // Admin settings rows (rendered by «Интеграции РФ»). Off by default.
        $now = now();
        DB::table('integration_settings')->insertOrIgnore([
            ['key' => 'npd_receipts_enabled', 'value' => '0', 'type' => 'toggle', 'group' => 'npd', 'label' => 'Формировать чеки самозанятых', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'npd_receipts_channel', 'value' => 'mock', 'type' => 'select', 'group' => 'npd', 'label' => 'Канал отправки чеков', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('npd_receipts');
        DB::table('integration_settings')->whereIn('key', ['npd_receipts_enabled', 'npd_receipts_channel'])->delete();
    }
};
