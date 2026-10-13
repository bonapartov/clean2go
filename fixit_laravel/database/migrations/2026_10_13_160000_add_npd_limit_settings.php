<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * НПД income limit control (ROADMAP Sprint 8): the self-employed may earn at
 * most 2.4 mln ₽ a year (422-ФЗ). Admin «Верификация РФ → Интеграции РФ →
 * Чеки самозанятых». See App\Support\Npd\NpdReceipts::checkLimit().
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        DB::table('integration_settings')->insertOrIgnore([
            ['key' => 'npd_limit_amount', 'value' => '2400000', 'type' => 'number', 'group' => 'npd', 'label' => 'Годовой лимит дохода НПД, ₽', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'npd_limit_warn_percent', 'value' => '80', 'type' => 'number', 'group' => 'npd', 'label' => 'Предупреждать исполнителя при доходе, % от лимита', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        DB::table('integration_settings')->whereIn('key', ['npd_limit_amount', 'npd_limit_warn_percent'])->delete();
    }
};
