<?php

namespace Tests\Feature;

use App\Helpers\Helpers;
use App\Support\GatewayCredentials;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Ключи платёжных и SMS-шлюзов вводятся в админке («Платёжные методы»,
 * «SMS-шлюзы») и хранятся зашифрованными в integration_settings; модули
 * читают их через GatewayCredentials, а не env() (CLAUDE.md, «Sensitive
 * Settings»). .env — только запасной вариант, пока ключ не сохраняли.
 */
class GatewayCredentialsTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        putenv('TEST_GATEWAY_KEY');
        parent::tearDown();
    }

    #[Test]
    public function falls_back_to_env_until_saved_in_admin(): void
    {
        putenv('TEST_GATEWAY_KEY=from-env');

        $this->assertSame('from-env', GatewayCredentials::get('test_gateway_key'));

        GatewayCredentials::set('test_gateway_key', 'from-admin', 'payment_gateways', 'Test: key');

        $this->assertSame('from-admin', GatewayCredentials::get('test_gateway_key'));
    }

    #[Test]
    public function stored_encrypted(): void
    {
        GatewayCredentials::set('yookassa_secret_key', 'live_secret_123', 'payment_gateways', 'ЮКасса: Секретный ключ');

        $raw = DB::table('integration_settings')->where('key', 'gateway.yookassa_secret_key')->value('value');
        $this->assertNotSame('live_secret_123', $raw);
        $this->assertStringNotContainsString('live_secret_123', $raw);
        $this->assertSame('live_secret_123', GatewayCredentials::get('yookassa_secret_key'));
    }

    #[Test]
    public function clearing_in_admin_does_not_fall_back_to_env(): void
    {
        putenv('TEST_GATEWAY_KEY=from-env');
        GatewayCredentials::set('test_gateway_key', '', 'sms_gateways', 'Test: key');

        $this->assertSame('', GatewayCredentials::get('test_gateway_key'));
    }

    #[Test]
    public function module_configs_are_resolved_from_admin_values(): void
    {
        GatewayCredentials::set('yookassa_shop_id', '123456', 'payment_gateways', 'ЮКасса: Shop ID');
        GatewayCredentials::set('yookassa_secret_key', 'secret', 'payment_gateways', 'ЮКасса: Секретный ключ');

        $yookassa = collect(Helpers::getPaymentMethodConfigs())->firstWhere('slug', 'yookassa');

        $this->assertSame(['yookassa_shop_id' => '123456', 'yookassa_secret_key' => 'secret'], $yookassa['configs']);
    }

    #[Test]
    public function module_config_files_hold_no_values(): void
    {
        foreach (['YooKassa/Config/payment.php', 'CloudPayments/Config/payment.php', 'Smsru/Config/sms.php', 'Smsc/Config/sms.php'] as $file) {
            $config = include base_path('Modules/'.$file);
            foreach ($config['configs'] as $key => $value) {
                $this->assertSame('', $value, "$file: $key must be blank — values live in the admin");
            }
        }
    }
}
