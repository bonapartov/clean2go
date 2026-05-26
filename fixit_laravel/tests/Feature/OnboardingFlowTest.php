<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Modules\ProviderOnboarding\Jobs\VerifyPassportJob;
use Modules\ProviderOnboarding\Models\IntegrationSetting;
use Modules\ProviderOnboarding\Models\ProviderVerification;
use PHPUnit\Framework\Attributes\Test;
use Phpblaze\Bladelib\Middles\A3;
use Tests\TestCase;

class OnboardingFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Queue::fake();
        config(['provider-onboarding.mock_mode' => true]);

        // Отключаем лицензионный middleware (phpblaze/bladelib A3 вставляет себя в группу 'api')
        $this->withoutMiddleware(A3::class);

        // Блокируем все внешние HTTP-запросы в тестах
        Http::fake([
            'statusnpd.nalog.ru/*' => Http::response(['status' => 'ACTIVE'], 200),
            'suggestions.dadata.ru/*' => Http::response(['suggestions' => []], 200),
            'fcm.googleapis.com/*' => Http::response(['name' => 'projects/test/messages/123'], 200),
        ]);
    }

    private function makeProvider(): User
    {
        return User::factory()->create(['onboarding_completed' => false, 'onboarding_step' => 0]);
    }

    // ─── Шаг 1: ИНН ───────────────────────────────────────────────────────────

    #[Test]
    public function step1_self_employed_inn_returns_correct_type(): void
    {
        $user = $this->makeProvider();

        $response = $this->actingAs($user)->postJson('/api/onboarding/inn', ['inn' => '123456789015']);

        $response->assertOk()->assertJsonFragment(['taxpayer_type' => 'self_employed']);
        $this->assertDatabaseHas('provider_verifications', ['user_id' => $user->id, 'taxpayer_type' => 'self_employed']);
    }

    #[Test]
    public function step1_ip_inn_returns_individual_entrepreneur(): void
    {
        $user = $this->makeProvider();

        $response = $this->actingAs($user)->postJson('/api/onboarding/inn', ['inn' => '123456789011']);

        $response->assertOk()->assertJsonFragment(['taxpayer_type' => 'individual_entrepreneur']);
    }

    #[Test]
    public function step1_ip_on_npd_returns_correct_type(): void
    {
        $user = $this->makeProvider();

        $response = $this->actingAs($user)->postJson('/api/onboarding/inn', ['inn' => '123456789014']);

        $response->assertOk()->assertJsonFragment(['taxpayer_type' => 'ip_on_npd']);
    }

    #[Test]
    public function step1_legal_entity_inn_10_digits_returns_legal_entity(): void
    {
        $user = $this->makeProvider();

        $response = $this->actingAs($user)->postJson('/api/onboarding/inn', ['inn' => '7707083893']);

        $response->assertOk()->assertJsonFragment(['taxpayer_type' => 'legal_entity']);
    }

    #[Test]
    public function step1_invalid_inn_returns_422(): void
    {
        $user = $this->makeProvider();

        $this->actingAs($user)->postJson('/api/onboarding/inn', ['inn' => '123456789010'])
            ->assertStatus(422);
    }

    #[Test]
    public function step1_inn_rate_limited_after_5_attempts(): void
    {
        $user = $this->makeProvider();

        for ($i = 0; $i < 5; $i++) {
            $this->actingAs($user)->postJson('/api/onboarding/inn', ['inn' => '123456789015']);
        }

        $this->actingAs($user)->postJson('/api/onboarding/inn', ['inn' => '123456789015'])
            ->assertStatus(429);
    }

    // ─── Шаг 3: паспорт ───────────────────────────────────────────────────────

    #[Test]
    public function step3_passport_upload_dispatches_job(): void
    {
        $user = $this->makeProvider();
        ProviderVerification::factory()->create(['user_id' => $user->id, 'taxpayer_type' => 'self_employed']);

        $response = $this->actingAs($user)->postJson('/api/onboarding/passport', [
            'series'      => '4510',
            'number'      => '123456',
            'issued_by'   => 'УФМС России',
            'issued_date' => '2015-01-15',
            'dept_code'   => '772-001',
            'photo'       => UploadedFile::fake()->image('passport.jpg'),
            'selfie'      => UploadedFile::fake()->image('selfie.jpg'),
        ]);

        $response->assertOk();
        Queue::assertPushed(VerifyPassportJob::class);
        $this->assertDatabaseHas('provider_verifications', [
            'user_id'         => $user->id,
            'passport_status' => 'pending',
        ]);
    }

    #[Test]
    public function passport_status_returns_current_status(): void
    {
        $user = $this->makeProvider();
        ProviderVerification::factory()->create([
            'user_id'         => $user->id,
            'passport_status' => 'manual_review',
        ]);

        $this->actingAs($user)->getJson('/api/onboarding/passport/status')
            ->assertOk()
            ->assertJsonFragment(['passport_status' => 'manual_review']);
    }

    // ─── Шаг 5: договор ───────────────────────────────────────────────────────

    #[Test]
    public function step5_contract_sign_requires_valid_sms_code(): void
    {
        $user = $this->makeProvider();
        ProviderVerification::factory()->create([
            'user_id'              => $user->id,
            'taxpayer_type'        => 'self_employed',
            'contract_path'        => 'contracts/test.pdf',
            'contract_sms_code'    => bcrypt('123456'),
            'contract_sms_sent_at' => now(),
        ]);

        $this->actingAs($user)->postJson('/api/onboarding/contract/sign', ['code' => '000000'])
            ->assertStatus(422)
            ->assertJsonFragment(['error' => 'wrong_code']);

        $this->actingAs($user)->postJson('/api/onboarding/contract/sign', ['code' => '123456'])
            ->assertOk();
    }

    #[Test]
    public function step5_expired_sms_code_rejected(): void
    {
        $user = $this->makeProvider();
        ProviderVerification::factory()->create([
            'user_id'              => $user->id,
            'taxpayer_type'        => 'self_employed',
            'contract_path'        => 'contracts/test.pdf',
            'contract_sms_code'    => bcrypt('123456'),
            'contract_sms_sent_at' => now()->subMinutes(20),
        ]);

        $this->actingAs($user)->postJson('/api/onboarding/contract/sign', ['code' => '123456'])
            ->assertStatus(422)
            ->assertJsonFragment(['error' => 'code_expired']);
    }

    // ─── ГПХ-флоу ─────────────────────────────────────────────────────────────

    #[Test]
    public function gph_flow_freezes_payments_on_npd_lost(): void
    {
        // Мокаем NpdVerificationService — возвращаем inactive без реального HTTP
        $this->instance(
            \Modules\ProviderOnboarding\Services\NpdVerificationService::class,
            new class extends \Modules\ProviderOnboarding\Services\NpdVerificationService {
                public function check(string $inn): array {
                    return ['status' => 'inactive', 'raw' => ['status' => 'INACTIVE']];
                }
            }
        );

        $user = $this->makeProvider();
        ProviderVerification::factory()->create([
            'user_id'       => $user->id,
            'inn'           => '123456789015',
            'taxpayer_type' => 'self_employed',
            'contract_type' => 'self_employed',
            'npd_status'    => 'active',
        ]);

        $this->actingAs($user)->postJson('/api/onboarding/npd-lost')
            ->assertOk()
            ->assertJsonFragment(['payments_frozen' => true]);

        $this->assertDatabaseHas('provider_verifications', [
            'user_id'         => $user->id,
            'payments_frozen' => 1,
        ]);
    }

    // ─── ООО Шаг 2В ───────────────────────────────────────────────────────────

    #[Test]
    public function step2v_ooo_documents_accepted_for_legal_entity(): void
    {
        $user = $this->makeProvider();
        ProviderVerification::factory()->create([
            'user_id'       => $user->id,
            'taxpayer_type' => 'legal_entity',
        ]);

        $response = $this->actingAs($user)->postJson('/api/onboarding/ooo-documents', [
            'director_name' => 'Иванов Иван Иванович',
            'bik'           => '044525225',
            'bank_account'  => '40702810000000000001',
        ]);

        $response->assertOk()->assertJsonFragment(['next_step' => 'pending_manual']);
        $this->assertDatabaseHas('provider_verifications', [
            'user_id'          => $user->id,
            'onboarding_status' => 'pending_manual',
        ]);
    }

    #[Test]
    public function step2v_ooo_documents_rejected_for_non_legal_entity(): void
    {
        $user = $this->makeProvider();
        ProviderVerification::factory()->create([
            'user_id'       => $user->id,
            'taxpayer_type' => 'self_employed',
        ]);

        $this->actingAs($user)->postJson('/api/onboarding/ooo-documents', [
            'director_name' => 'Иванов Иван Иванович',
            'bik'           => '044525225',
            'bank_account'  => '40702810000000000001',
        ])->assertStatus(422)->assertJsonFragment(['error' => 'wrong_taxpayer_type']);
    }

    // ─── Обратная совместимость: статус ───────────────────────────────────────

    #[Test]
    public function status_endpoint_returns_full_state(): void
    {
        $user = $this->makeProvider();
        ProviderVerification::factory()->create([
            'user_id'          => $user->id,
            'taxpayer_type'    => 'self_employed',
            'onboarding_status' => 'in_progress',
            'passport_status'  => 'pending',
            'npd_status'       => 'active',
            'payments_frozen'  => false,
        ]);

        $this->actingAs($user)->getJson('/api/onboarding/status')
            ->assertOk()
            ->assertJsonStructure([
                'onboarding_completed', 'onboarding_step', 'taxpayer_type',
                'onboarding_status', 'passport_status', 'npd_status', 'payments_frozen',
            ]);
    }
}
