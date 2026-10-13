<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\ExecutorDisclosure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\ProviderOnboarding\Models\ProviderVerification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Исполнитель до оплаты: ФИО или название, статус, ИНН (агентская модель
 * B2C, ROADMAP Sprint 6). Данные берутся только из проверки ИНН при
 * онбординге; без неё — verified = false и без ИНН.
 */
class ExecutorDisclosureTest extends TestCase
{
    use RefreshDatabase;

    private function verify(User $user, array $attributes): void
    {
        ProviderVerification::create(array_merge([
            'user_id' => $user->id,
            'inn_status' => 'active',
        ], $attributes));
    }

    #[Test]
    public function self_employed_shows_own_name_status_and_inn(): void
    {
        $user = User::factory()->create(['name' => 'Иван Петров']);
        $this->verify($user, ['inn' => '500100732259', 'taxpayer_type' => 'self_employed']);

        $info = ExecutorDisclosure::forProvider($user);

        $this->assertTrue($info['verified']);
        $this->assertSame('Иван Петров', $info['name']);
        $this->assertSame('Самозанятый (НПД)', $info['status_label']);
        $this->assertSame('500100732259', $info['inn']);
    }

    #[Test]
    public function legal_entity_shows_registered_name(): void
    {
        $user = User::factory()->create(['name' => 'Чистый дом']);
        $this->verify($user, [
            'inn' => '7707083893',
            'ogrn' => '1027700132195',
            'taxpayer_type' => 'legal_entity',
            'legal_name' => 'ООО «Чистый дом»',
        ]);

        $info = ExecutorDisclosure::forProvider($user);

        $this->assertSame('ООО «Чистый дом»', $info['name']);
        $this->assertSame('Юридическое лицо', $info['status_label']);
        $this->assertSame('1027700132195', $info['ogrn']);
    }

    #[Test]
    public function unverified_provider_has_no_inn(): void
    {
        $noCheck = User::factory()->create(['name' => 'Без проверки']);
        $failed = User::factory()->create(['name' => 'Не прошёл']);
        $this->verify($failed, ['inn' => '500100732259', 'inn_status' => 'not_found', 'taxpayer_type' => 'self_employed']);

        foreach ([$noCheck, $failed] as $user) {
            $info = ExecutorDisclosure::forProvider($user);
            $this->assertFalse($info['verified']);
            $this->assertNull($info['inn']);
            $this->assertNull($info['status_label']);
            $this->assertSame($user->name, $info['name']);
        }
    }

    #[Test]
    public function checkout_providers_are_listed_once_in_order(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();

        $items = [
            'services' => [['provider_id' => $b->id], ['provider_id' => $a->id], ['provider_id' => $b->id]],
            'services_package' => [['services' => [['provider_id' => $a->id], ['provider_id' => null]]]],
        ];

        $executors = ExecutorDisclosure::forProviderIds(ExecutorDisclosure::providerIdsFromCheckout($items));

        $this->assertSame([$b->id, $a->id], array_column($executors, 'provider_id'));
    }

    #[Test]
    public function payment_page_block_renders_inn(): void
    {
        $user = User::factory()->create(['name' => 'Иван Петров']);
        $this->verify($user, ['inn' => '500100732259', 'taxpayer_type' => 'self_employed']);
        $unverified = User::factory()->create(['name' => 'Новичок']);

        $html = view('frontend.inc.executor-disclosure', [
            'executors' => ExecutorDisclosure::forProviderIds([$user->id, $unverified->id]),
        ])->render();

        $this->assertStringContainsString('Иван Петров', $html);
        $this->assertStringContainsString('500100732259', $html);
        $this->assertStringContainsString('Самозанятый (НПД)', $html);
        $this->assertStringContainsString('Данные исполнителя ещё не проверены', $html);
    }
}
