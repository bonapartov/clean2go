<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Support\AgoraRtcToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Phpblaze\Bladelib\Middles\A3;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Токен звонка Agora подписывает сервер (POST /api/agora/token), сертификат
 * в приложение не уходит. Формат — порт RtcTokenBuilder.buildTokenWithUid
 * из npm agora-access-token@2.0.4; эталонные строки ниже получены этим
 * пакетом (node) с теми же salt/ts.
 */
class AgoraTokenTest extends TestCase
{
    use RefreshDatabase;

    private const APP_ID = '970CA35de60c44645bbae8a215061b33';

    private const CERT = '5CFd2fd1755d40ecb72977518be15d3b';

    #[Test]
    public function matches_official_agora_package(): void
    {
        $this->assertSame(
            '006970CA35de60c44645bbae8a215061b33IAB9oxCDmtGPTB3fCD8wtTBsxxo1iYIKpgcMKX9ZqQbNNs5H7eQAAAAAIgAVzVsHgDuxagQAAQAA0klrAgAA0klrAwAA0klrBAAA0klr',
            AgoraRtcToken::publisher(self::APP_ID, self::CERT, 'booking_42_7', 0, 1800000000, 123456789, 1790000000)
        );
        $this->assertSame(
            '006970CA35de60c44645bbae8a215061b33IADA3s7y8w3NOHVZWtIt+u3DM2WP98SVTDo0LORDqcOB+JisUYNXoFHlIgD/////gDuxagQAAQAA0klrAgAA0klrAwAA0klrBAAA0klr',
            AgoraRtcToken::publisher(self::APP_ID, self::CERT, 'канал', 2882341273, 1800000000, 4294967295, 1790000000)
        );
    }

    private function seedSettings(array $agora): void
    {
        $this->withoutMiddleware(A3::class);
        $this->seed([
            \Database\Seeders\CurrencySeeder::class,
            \Database\Seeders\SystemLangSeeder::class,
            \Database\Seeders\SettingSeeder::class,
        ]);
        $setting = Setting::first();
        $values = $setting->values;
        $values['agora'] = $agora;
        $setting->update(['values' => $values]);
    }

    #[Test]
    public function endpoint_signs_on_server(): void
    {
        $this->seedSettings(['app_id' => self::APP_ID, 'certificate' => self::CERT]);

        $response = $this->actingAs(User::factory()->create(), 'sanctum')
            ->postJson('/api/agora/token')
            ->assertOk();

        $this->assertStringStartsWith('006'.self::APP_ID, $response->json('data.token'));
        $this->assertStringStartsWith('c2g_', $response->json('data.channelName'));
        $this->assertStringNotContainsString(self::CERT, $response->getContent());
    }

    #[Test]
    public function endpoint_reports_missing_config(): void
    {
        $this->seedSettings(['app_id' => '', 'certificate' => '']);

        $this->actingAs(User::factory()->create(), 'sanctum')
            ->postJson('/api/agora/token')
            ->assertStatus(503);
    }

    #[Test]
    public function endpoint_requires_login(): void
    {
        $this->withoutMiddleware(A3::class);
        $this->postJson('/api/agora/token')->assertUnauthorized();
    }
}
