<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Support\ServiceInsurance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Phpblaze\Bladelib\Middles\A3;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * «Застраховать услугу» (ROADMAP Sprint 6, агентская модель B2C): опция
 * выключена по умолчанию, включается в админке (Настройки → Общие →
 * Страхование услуги), цена — фикс или процент от заказа.
 */
class ServiceInsuranceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(A3::class);
        // Только сиды настроек: полный db:seed в общем прогоне ломается на RoleSeeder.
        $this->seed([
            \Database\Seeders\CurrencySeeder::class,
            \Database\Seeders\SystemLangSeeder::class,
            \Database\Seeders\SettingSeeder::class,
        ]);
    }

    private function configure(array $insurance): void
    {
        $setting = Setting::first();
        $values = $setting->values;
        $values['insurance'] = array_merge($values['insurance'] ?? [], $insurance);
        $setting->update(['values' => $values]);
    }

    #[Test]
    public function off_by_default_and_charges_nothing(): void
    {
        $this->assertFalse(ServiceInsurance::isAvailable());

        $result = ServiceInsurance::forCheckout(true, 5000);

        $this->assertFalse($result['is_insured']);
        $this->assertSame(0.0, $result['insurance_amount']);
    }

    #[Test]
    public function fixed_price(): void
    {
        $this->configure(['status' => true, 'price_type' => 'fixed', 'price' => 199, 'insurer_name' => 'Страховая А']);

        $result = ServiceInsurance::forCheckout(true, 5000);

        $this->assertTrue($result['is_insured']);
        $this->assertSame(199.0, $result['insurance_amount']);
        $this->assertSame('Страховая А', $result['insurance_insurer']);
    }

    #[Test]
    public function percentage_of_order(): void
    {
        $this->configure(['status' => true, 'price_type' => 'percentage', 'price' => 3]);

        $this->assertSame(150.0, ServiceInsurance::forCheckout(true, 5000)['insurance_amount']);
    }

    #[Test]
    public function not_charged_unless_client_ticked_it(): void
    {
        $this->configure(['status' => true, 'price_type' => 'fixed', 'price' => 199]);

        $this->assertFalse(ServiceInsurance::forCheckout(false, 5000)['is_insured']);
    }

    #[Test]
    public function zero_price_hides_the_option(): void
    {
        $this->configure(['status' => true, 'price' => 0]);

        $this->assertFalse(ServiceInsurance::isAvailable());
    }

    #[Test]
    public function apps_get_the_settings_publicly(): void
    {
        $this->configure(['status' => true, 'price_type' => 'fixed', 'price' => 199, 'insurer_name' => 'Страховая А']);

        $this->getJson('/api/settings')
            ->assertOk()
            ->assertJsonPath('values.insurance.status', true)
            ->assertJsonPath('values.insurance.price', 199)
            ->assertJsonPath('values.insurance.insurer_name', 'Страховая А');
    }

    #[Test]
    public function website_checkbox_is_kept_only_when_available(): void
    {
        $this->post('/cart/insurance', ['insurance' => 1])->assertRedirect();
        $this->assertFalse(session('insurance'), 'выключено в админке — выбор игнорируется');

        $this->configure(['status' => true, 'price' => 199]);
        $this->post('/cart/insurance', ['insurance' => 1])->assertRedirect();
        $this->assertTrue(session('insurance'));

        // Снятая галочка запоминается явно, иначе вернётся значение по умолчанию
        $this->post('/cart/insurance', ['insurance' => 0])->assertRedirect();
        $this->assertTrue(session()->has('insurance'));
        $this->assertFalse(session('insurance'));
    }

    #[Test]
    public function checkbox_is_pre_ticked_by_default(): void
    {
        $this->configure(['status' => true, 'price' => 199]);
        $this->assertTrue(ServiceInsurance::isDefaultOn(), 'решение 13.10.2026: галочка отмечена заранее');

        $this->configure(['default_on' => false]);
        $this->assertFalse(ServiceInsurance::isDefaultOn(), 'админ может выключить');
    }

    #[Test]
    public function recurring_repeat_keeps_insurance_of_first_order(): void
    {
        $source = file_get_contents(app_path('Console/Commands/GenerateRecurringBookings.php'));
        $this->assertStringContainsString("'insurance' =>", $source);
        $template = file_get_contents(app_path('Repositories/API/RecurringBookingRepository.php'));
        $this->assertStringContainsString("'insurance' => (bool) (\$calculatedCosts['total']['is_insured']", $template);
    }

    #[Test]
    public function booking_stores_the_snapshot(): void
    {
        $this->assertTrue(Schema::hasColumns('bookings', ['is_insured', 'insurance_amount', 'insurance_insurer']));
        $this->assertContains('is_insured', (new \App\Models\Booking)->getFillable());
        $this->assertContains('insurance_amount', (new \App\Models\Booking)->getFillable());
        $this->assertContains('insurance_insurer', (new \App\Models\Booking)->getFillable());
    }

    #[Test]
    public function booking_freezes_executor_data(): void
    {
        $consumer = \App\Models\User::factory()->create();
        $provider = \App\Models\User::factory()->create(['name' => 'Иван Петров']);
        \Modules\ProviderOnboarding\Models\ProviderVerification::create([
            'user_id' => $provider->id, 'inn' => '500100732259', 'inn_status' => 'active', 'taxpayer_type' => 'self_employed',
        ]);

        $booking = \App\Models\Booking::create(['consumer_id' => $consumer->id, 'provider_id' => $provider->id, 'booking_number' => 900001]);

        $this->assertSame('500100732259', $booking->fresh()->executor_snapshot['inn']);
        $this->assertSame('Самозанятый (НПД)', $booking->fresh()->executor_snapshot['status_label']);

        // Профиль меняется позже — снимок в заказе остаётся прежним
        $provider->update(['name' => 'Новое имя']);
        $booking->fresh()->update(['description' => 'x']);
        $this->assertSame('Иван Петров', $booking->fresh()->executor_snapshot['name']);
    }

    #[Test]
    public function demo_inns_are_valid_and_fictional(): void
    {
        $inn12 = \Database\Seeders\DemoVerificationSeeder::personInn(3);
        $inn10 = \Database\Seeders\DemoVerificationSeeder::companyInn(11);

        $this->assertSame('00', substr($inn12, 0, 2), 'регион 00 не существует');
        $this->assertSame('00', substr($inn10, 0, 2));
        $this->assertTrue(self::validInn($inn12));
        $this->assertTrue(self::validInn($inn10));
        $this->assertSame(13, strlen(\Database\Seeders\DemoVerificationSeeder::ogrn(1)));
        $this->assertSame(15, strlen(\Database\Seeders\DemoVerificationSeeder::ogrnip(1)));
    }

    /** Независимая проверка контрольных цифр ИНН (по алгоритму ФНС). */
    private static function validInn(string $inn): bool
    {
        $d = array_map('intval', str_split($inn));
        $c = fn (array $w) => array_sum(array_map(fn ($wi, $i) => $wi * $d[$i], $w, array_keys($w))) % 11 % 10;
        if (strlen($inn) === 10) {
            return $c([2, 4, 10, 3, 5, 9, 4, 6, 8]) === $d[9];
        }

        return $c([7, 2, 4, 10, 3, 5, 9, 4, 6, 8]) === $d[10] && $c([3, 7, 2, 4, 10, 3, 5, 9, 4, 6, 8]) === $d[11];
    }
}
