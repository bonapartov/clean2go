<?php

namespace Tests\Feature;

use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\NpdReceipt;
use App\Models\User;
use App\Support\Npd\NpdReceipts;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\ProviderOnboarding\Models\IntegrationSetting;
use Modules\ProviderOnboarding\Models\ProviderVerification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Чеки самозанятых («Мой налог», ROADMAP Sprint 8): при оплате заказа —
 * чек от имени исполнителя на НПД, при возврате — аннулирование, сбои
 * отправки — повтор. Канал отправки выбирается в админке; пока есть только
 * тестовый (mock), остальные честно падают с понятной ошибкой.
 */
class NpdReceiptsTest extends TestCase
{
    use RefreshDatabase;

    private User $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = User::factory()->create();
        IntegrationSetting::set('npd_receipts_enabled', '1');
        IntegrationSetting::set('npd_receipts_channel', 'mock');
    }

    private function executor(string $type, string $inn = '500100732259'): User
    {
        $user = User::factory()->create();
        ProviderVerification::create(['user_id' => $user->id, 'inn' => $inn, 'inn_status' => 'active', 'taxpayer_type' => $type]);

        return $user;
    }

    private function booking(User $executor, array $extra = []): Booking
    {
        static $n = 910000;

        return Booking::create(array_merge([
            'consumer_id' => $this->client->id, 'provider_id' => $executor->id,
            'booking_number' => ++$n, 'subtotal' => 2500, 'platform_fees' => 10, 'total' => 2510,
        ], $extra));
    }

    #[Test]
    public function paid_booking_gets_a_receipt_in_executors_name(): void
    {
        $booking = $this->booking($this->executor('self_employed'));

        $booking->update(['payment_status' => PaymentStatus::COMPLETED]);

        $receipt = NpdReceipt::sole();
        $this->assertSame('registered', $receipt->status);
        $this->assertSame('500100732259', $receipt->executor_inn);
        $this->assertSame(2500.0, $receipt->amount, 'без комиссии платформы');
        $this->assertStringStartsWith('TEST-', $receipt->external_id);
    }

    #[Test]
    public function only_npd_executors_and_only_when_enabled(): void
    {
        $this->booking($this->executor('individual_entrepreneur'))->update(['payment_status' => PaymentStatus::COMPLETED]);
        $this->booking($this->executor('ip_on_npd'))->update(['payment_status' => PaymentStatus::COMPLETED]);
        $this->assertSame(1, NpdReceipt::count(), 'ИП на ОСН/УСН — не через «Мой налог», ИП на НПД — да');

        IntegrationSetting::set('npd_receipts_enabled', '0');
        $this->booking($this->executor('self_employed'))->update(['payment_status' => PaymentStatus::COMPLETED]);
        $this->assertSame(1, NpdReceipt::count(), 'выключено в админке');
    }

    #[Test]
    public function one_receipt_per_executor_of_each_sub_booking(): void
    {
        $a = $this->executor('self_employed', '500100732259');
        $b = $this->executor('self_employed', '773370857141');
        $parent = $this->booking($a, ['provider_id' => null]);
        $this->booking($a, ['parent_id' => $parent->id, 'subtotal' => 1000]);
        $this->booking($b, ['parent_id' => $parent->id, 'subtotal' => 1500]);

        $parent->update(['payment_status' => PaymentStatus::COMPLETED]);
        $parent->update(['payment_status' => PaymentStatus::COMPLETED, 'description' => 'повтор']);

        $this->assertSame([1000.0, 1500.0], NpdReceipt::orderBy('amount')->pluck('amount')->all());
    }

    #[Test]
    public function refund_annuls_the_receipt(): void
    {
        $booking = $this->booking($this->executor('self_employed'));
        $booking->update(['payment_status' => PaymentStatus::COMPLETED]);

        $booking->update(['payment_status' => PaymentStatus::REFUNDED]);

        $receipt = NpdReceipt::sole();
        $this->assertSame('cancelled', $receipt->status);
        $this->assertSame('Возврат средств', $receipt->cancel_reason);
    }

    #[Test]
    public function unavailable_channel_keeps_receipt_for_retry(): void
    {
        IntegrationSetting::set('npd_receipts_channel', 'fns_partner');
        $this->booking($this->executor('self_employed'))->update(['payment_status' => PaymentStatus::COMPLETED]);

        $receipt = NpdReceipt::sole();
        $this->assertSame('failed', $receipt->status);
        $this->assertStringContainsString('договор ООО с ФНС', $receipt->last_error);

        IntegrationSetting::set('npd_receipts_channel', 'mock');
        $this->artisan('npd:receipts-retry')->assertSuccessful();

        $this->assertSame('registered', $receipt->fresh()->status);
        $this->assertSame(2, $receipt->fresh()->attempts);
    }

    #[Test]
    public function channel_choice_is_in_admin(): void
    {
        $this->assertArrayHasKey('mock', NpdReceipts::CHANNELS);
        $this->assertDatabaseHas('integration_settings', ['key' => 'npd_receipts_channel', 'group' => 'npd', 'type' => 'select']);
        $this->assertDatabaseHas('integration_settings', ['key' => 'npd_receipts_enabled', 'group' => 'npd', 'type' => 'toggle']);
    }

    #[Test]
    public function client_sees_receipts_of_the_order(): void
    {
        $a = $this->executor('self_employed', '500100732259');
        $parent = $this->booking($a, ['provider_id' => null]);
        $this->booking($a, ['parent_id' => $parent->id, 'subtotal' => 1000]);
        $parent->update(['payment_status' => PaymentStatus::COMPLETED]);

        $receipts = NpdReceipts::forClient($parent->fresh());

        $this->assertCount(1, $receipts);
        $this->assertSame('500100732259', $receipts[0]['executor_inn']);
        $this->assertSame('Выдан', $receipts[0]['status_label']);
        $this->assertStringStartsWith('TEST-', $receipts[0]['number']);
        $this->assertTrue($receipts[0]['is_test'], 'тестовый канал помечен для клиента');

        $api = (new \App\Http\Resources\BookingDetailResource($parent->fresh()))->toArray(request());
        $this->assertSame($receipts, $api['npd_receipts']);
        $this->assertArrayHasKey('executor', $api);
        $this->assertArrayHasKey('is_insured', $api);
    }
}
