<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Phpblaze\Bladelib\Middles\A3;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Секреты не должны уходить в публичный /api/settings, а push из приложений
 * идёт через бэкенд (POST /api/push/send), который один держит ключ Firebase.
 *
 * До 2026-10-13 /api/settings отдавал agora.certificate, firebase_server_key
 * и firebase.service_json (сервисный ключ Firebase с private_key), а сам ключ
 * лежал в public/admin/assets/firebase.json и скачивался кем угодно.
 *
 * Успешная отправка в FCM здесь не проверяется: Google_Client получает
 * OAuth-токен своим HTTP-клиентом, мимо Http::fake().
 */
class SecretsExposureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Лицензионный middleware Fixit (phpblaze/bladelib) встраивается в группу 'api'.
        $this->withoutMiddleware(A3::class);
        // Никогда не трогать настоящий ключ: тесты смотрят во временную папку.
        config(['services.firebase.service_account_path' => sys_get_temp_dir().'/clean2go-test-no-such-key.json']);
    }

    #[Test]
    public function public_settings_do_not_expose_secrets(): void
    {
        // Только сид настроек, не весь db:seed: RoleSeeder в общем прогоне
        // ломается на повторном сиде (уникальность ролей).
        $this->seed([
            \Database\Seeders\CurrencySeeder::class,
            \Database\Seeders\SystemLangSeeder::class,
            \Database\Seeders\SettingSeeder::class,
        ]);
        $setting = Setting::first();
        $values = $setting->values;
        $values['agora'] = ['app_id' => 'public-app-id', 'certificate' => 'secret-certificate'];
        $values['general']['firebase_server_key'] = 'secret-server-key';
        $values['firebase']['service_json'] = ['private_key' => 'secret-private-key'];
        $values['firebase']['yandex_map_api_key'] = 'public-map-key';
        $setting->update(['values' => $values]);

        $response = $this->getJson('/api/settings')->assertOk();

        $body = $response->getContent();
        $this->assertStringNotContainsString('secret-certificate', $body);
        $this->assertStringNotContainsString('secret-server-key', $body);
        $this->assertStringNotContainsString('secret-private-key', $body);
        // То, что приложениям нужно, остаётся.
        $response->assertJsonPath('values.agora.app_id', 'public-app-id');
        $response->assertJsonPath('values.firebase.yandex_map_api_key', 'public-map-key');
    }

    #[Test]
    public function push_send_requires_login(): void
    {
        $this->postJson('/api/push/send', ['message' => ['token' => 'device-token']])
            ->assertUnauthorized();
    }

    #[Test]
    public function push_send_requires_device_token(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/push/send', ['message' => ['notification' => ['title' => 'Hi']]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('message.token');
    }

    #[Test]
    public function push_send_reports_missing_firebase_key(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/push/send', [
                'message' => [
                    'token' => 'device-token',
                    'notification' => ['title' => 'Новое сообщение', 'body' => 'Привет'],
                    'data' => ['chatId' => 5, 'image' => null],
                ],
            ])
            ->assertStatus(503)
            ->assertJsonPath('success', false);
    }
}
