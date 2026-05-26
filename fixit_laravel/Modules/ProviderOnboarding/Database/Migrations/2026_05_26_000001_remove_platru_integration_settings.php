<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('integration_settings')
            ->whereIn('key', ['platru_api_key', 'platru_endpoint'])
            ->delete();
    }

    public function down(): void
    {
        DB::table('integration_settings')->insert([
            ['key' => 'platru_api_key', 'type' => 'password', 'group' => 'npd', 'label' => 'Plat.ru API Key', 'value' => null, 'updated_by' => null, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'platru_endpoint', 'type' => 'text',    'group' => 'npd', 'label' => 'Plat.ru Endpoint URL', 'value' => null, 'updated_by' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
};
