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
        $this->assertFalse(session()->has('insurance'), 'выключено в админке — выбор игнорируется');

        $this->configure(['status' => true, 'price' => 199]);
        $this->post('/cart/insurance', ['insurance' => 1])->assertRedirect();
        $this->assertTrue(session('insurance'));

        $this->post('/cart/insurance', ['insurance' => 0])->assertRedirect();
        $this->assertFalse(session()->has('insurance'));
    }

    #[Test]
    public function booking_stores_the_snapshot(): void
    {
        $this->assertTrue(Schema::hasColumns('bookings', ['is_insured', 'insurance_amount', 'insurance_insurer']));
        $this->assertContains('is_insured', (new \App\Models\Booking)->getFillable());
        $this->assertContains('insurance_amount', (new \App\Models\Booking)->getFillable());
        $this->assertContains('insurance_insurer', (new \App\Models\Booking)->getFillable());
    }
}
