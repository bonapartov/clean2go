<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Call;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Phpblaze\Bladelib\Middles\A3;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Звонки клиент ↔ исполнитель через свой LiveKit (App\Support\Calls,
 * CallController). Звонить можно только участнику общего заказа; токен —
 * JWT HS256 на секрете сервера LiveKit; ключи не уходят в /api/settings.
 */
class CallsTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'test-livekit-secret-0123456789abcdef';

    private User $client;

    private User $executor;

    private Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(A3::class);
        config(['services.firebase.service_account_path' => sys_get_temp_dir().'/clean2go-test-no-such-key.json']);
        $this->seed([
            \Database\Seeders\CurrencySeeder::class,
            \Database\Seeders\SystemLangSeeder::class,
            \Database\Seeders\SettingSeeder::class,
        ]);
        $this->configure(['status' => true, 'livekit_url' => 'ws://lk.test:7880', 'api_key' => 'c2gtest', 'api_secret' => self::SECRET]);

        $this->client = User::factory()->create(['name' => 'Клиент']);
        $this->executor = User::factory()->create(['name' => 'Мастер']);
        $this->booking = Booking::create(['consumer_id' => $this->client->id, 'provider_id' => $this->executor->id, 'booking_number' => 900100]);
    }

    private function configure(array $calls): void
    {
        $setting = Setting::first();
        $values = $setting->values;
        $values['calls'] = array_merge($values['calls'] ?? [], $calls);
        $setting->update(['values' => $values]);
    }

    private function dial(User $from, User $to, array $extra = [])
    {
        return $this->actingAs($from, 'sanctum')->postJson('/api/calls', ['callee_id' => $to->id] + $extra);
    }

    /** Проверка JWT LiveKit: подпись секретом сервера и права на комнату. */
    private function assertLiveKitToken(string $jwt, string $room, User $user): void
    {
        [$h, $p, $sig] = explode('.', $jwt);
        $expected = rtrim(strtr(base64_encode(hash_hmac('sha256', "$h.$p", self::SECRET, true)), '+/', '-_'), '=');
        $this->assertSame($expected, $sig, 'подпись секретом LiveKit');
        $claims = json_decode(base64_decode(strtr($p, '-_', '+/')), true);
        $this->assertSame('c2gtest', $claims['iss']);
        $this->assertSame('user_'.$user->id, $claims['sub']);
        $this->assertSame($room, $claims['video']['room']);
        $this->assertTrue($claims['video']['roomJoin']);
        $this->assertGreaterThan(time(), $claims['exp']);
    }

    #[Test]
    public function client_calls_executor_of_their_booking(): void
    {
        $response = $this->dial($this->client, $this->executor, ['type' => 'video'])->assertOk();

        $call = Call::firstOrFail();
        $this->assertSame('ringing', $call->status);
        $this->assertSame('video', $call->type);
        $this->assertSame($this->booking->id, $call->booking_id);
        $response->assertJsonPath('data.livekit_url', 'ws://lk.test:7880');
        $this->assertLiveKitToken($response->json('data.token'), $call->room, $this->client);
    }

    #[Test]
    public function strangers_cannot_call(): void
    {
        $stranger = User::factory()->create();

        $this->dial($stranger, $this->executor)->assertForbidden();
        $this->dial($this->client, $this->client)->assertForbidden();
        $this->assertSame(0, Call::count());
    }

    #[Test]
    public function serviceman_of_the_booking_can_be_called(): void
    {
        $serviceman = User::factory()->create();
        $this->booking->servicemen()->attach($serviceman->id);

        $this->dial($this->client, $serviceman)->assertOk();
    }

    #[Test]
    public function disabled_in_admin(): void
    {
        $this->configure(['status' => false]);

        $this->dial($this->client, $this->executor)->assertStatus(503);
    }

    #[Test]
    public function callee_accepts_and_gets_own_token(): void
    {
        $id = $this->dial($this->client, $this->executor)->json('data.id');

        $this->actingAs($this->client, 'sanctum')->postJson("/api/calls/$id/accept")->assertForbidden();

        $accepted = $this->actingAs($this->executor, 'sanctum')->postJson("/api/calls/$id/accept")->assertOk();
        $accepted->assertJsonPath('data.status', 'accepted');
        $this->assertLiveKitToken($accepted->json('data.token'), Call::find($id)->room, $this->executor);

        $this->actingAs($this->client, 'sanctum')->getJson("/api/calls/$id")->assertJsonPath('data.status', 'accepted');
        $this->actingAs($this->executor, 'sanctum')->postJson("/api/calls/$id/end")->assertJsonPath('data.status', 'ended');
    }

    #[Test]
    public function decline_cancel_and_missed(): void
    {
        $id = $this->dial($this->client, $this->executor)->json('data.id');
        $this->actingAs($this->executor, 'sanctum')->postJson("/api/calls/$id/decline")->assertJsonPath('data.status', 'declined');

        $id = $this->dial($this->client, $this->executor)->json('data.id');
        $this->actingAs($this->client, 'sanctum')->postJson("/api/calls/$id/end")->assertJsonPath('data.status', 'cancelled');

        $id = $this->dial($this->client, $this->executor)->json('data.id');
        Call::whereKey($id)->update(['created_at' => now()->subMinutes(2)]);
        $this->actingAs($this->executor, 'sanctum')->getJson("/api/calls/$id")->assertJsonPath('data.status', 'missed');
        $this->actingAs($this->executor, 'sanctum')->postJson("/api/calls/$id/accept")->assertStatus(409);
    }

    #[Test]
    public function busy_callee(): void
    {
        $this->dial($this->client, $this->executor)->assertOk();
        $other = User::factory()->create();
        Booking::create(['consumer_id' => $other->id, 'provider_id' => $this->executor->id, 'booking_number' => 900101]);

        $this->dial($other, $this->executor)->assertStatus(409);
    }

    #[Test]
    public function incoming_call_for_callee(): void
    {
        $id = $this->dial($this->client, $this->executor)->json('data.id');

        $this->actingAs($this->executor, 'sanctum')->getJson('/api/calls/incoming')
            ->assertJsonPath('data.id', $id)
            ->assertJsonPath('data.caller.name', 'Клиент')
            ->assertJsonMissingPath('data.token');
        $this->actingAs($this->client, 'sanctum')->getJson('/api/calls/incoming')->assertJsonPath('data', null);
    }

    #[Test]
    public function freelancer_answers_calls_to_own_shadow_serviceman(): void
    {
        // Самозанятый и его теневой мастер — один человек и один телефон
        $shadow = User::factory()->create(['provider_id' => $this->executor->id, 'is_shadow' => true]);
        $this->booking->servicemen()->attach($shadow->id);
        $id = $this->dial($this->client, $shadow)->assertOk()->json('data.id');

        $this->actingAs($this->executor, 'sanctum')->getJson('/api/calls/incoming')->assertJsonPath('data.id', $id);
        $this->actingAs($this->executor, 'sanctum')->postJson("/api/calls/$id/accept")->assertJsonPath('data.status', 'accepted');

        $stranger = User::factory()->create();
        $this->actingAs($stranger, 'sanctum')->getJson("/api/calls/$id")->assertForbidden();
    }

    #[Test]
    public function server_keys_stay_private(): void
    {
        $body = $this->getJson('/api/settings')->assertOk()->assertJsonPath('values.calls.status', true)->getContent();

        $this->assertStringNotContainsString(self::SECRET, $body);
        $this->assertStringNotContainsString('c2gtest', $body);
        $this->assertStringNotContainsString('lk.test', $body);
    }
}
