<?php

namespace Tests\Feature;

use App\Support\GatewayCredentials;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Modules\ProviderOnboarding\Http\Controllers\Backend\OnboardingSettingsController;
use Modules\ProviderOnboarding\Models\IntegrationSetting;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Страница «Онбординг → Настройки интеграций» (2026-10-13):
 * - поле-пароль выводило в value шифр из БД; при сохранении он шифровался
 *   ещё раз и ключ (DaData и др.) ломался;
 * - ключи шлюзов (GatewayCredentials) лежат в той же таблице, но здесь их
 *   не показываем — у них свои экраны.
 */
class OnboardingSettingsPageTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function saving_the_form_keeps_password_keys_intact(): void
    {
        IntegrationSetting::create(['key' => 'dadata_api_key', 'type' => 'password', 'group' => 'fns', 'label' => 'DaData API Key']);
        IntegrationSetting::set('dadata_api_key', 'real-dadata-key');

        // Форма присылает пустой пароль (поле больше не заполняется шифром)
        (new OnboardingSettingsController)->update(new Request(['dadata_api_key' => '']));

        $this->assertSame('real-dadata-key', IntegrationSetting::get('dadata_api_key'));

        // В блоке пароля шифр из БД не должен попадать в value (у текстовых полей — можно)
        $view = file_get_contents(resource_path('views/backend/onboarding/settings/index.blade.php'));
        preg_match('/@if\(\$setting->type === \'password\'\)(.*?)@elseif/s', $view, $m);
        $this->assertNotEmpty($m, 'блок пароля найден');
        $this->assertStringNotContainsString('$setting->value }}"', $m[1]);
    }

    #[Test]
    public function gateway_keys_are_not_listed_here(): void
    {
        IntegrationSetting::create(['key' => 'onboarding_enabled', 'type' => 'toggle', 'group' => 'general', 'label' => 'Онбординг']);
        GatewayCredentials::set('yookassa_shop_id', '123', 'payment_gateways', 'ЮКасса: Shop ID');

        $groups = (new OnboardingSettingsController)->index()->getData()['settings']->keys()->all();

        $this->assertContains('general', $groups);
        $this->assertNotContains('payment_gateways', $groups);
    }
}
