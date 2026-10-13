<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Phpblaze\Bladelib\Middles\A3;
use Phpblaze\Bladelib\Middles\B1;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * До 2026-10-13 страницы «Верификация РФ» (заявки исполнителей с паспортами,
 * одобрение, ключи интеграций) и три маршрута купонов проверяли только вход:
 * их мог открыть исполнитель, мастер и даже клиент.
 */
class BackendAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([A3::class, B1::class]);
        foreach ([RoleEnum::ADMIN, RoleEnum::PROVIDER, RoleEnum::SERVICEMAN, RoleEnum::CONSUMER] as $role) {
            Role::create(['name' => $role, 'guard_name' => 'web']);
        }
        Permission::create(['name' => 'backend.coupon.edit', 'guard_name' => 'web']);
        Permission::create(['name' => 'backend.coupon.destroy', 'guard_name' => 'web']);
    }

    private function as(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    #[Test]
    public function only_admin_reaches_onboarding_admin(): void
    {
        foreach ([RoleEnum::PROVIDER, RoleEnum::SERVICEMAN] as $role) {
            $user = $this->as($role);
            $this->actingAs($user)->get('/backend/verifications')->assertForbidden();
            $this->actingAs($user)->get('/backend/onboarding/settings')->assertForbidden();
            $this->actingAs($user)->post('/backend/verifications/1/approve')->assertForbidden();
            $this->actingAs($user)->post('/backend/onboarding/settings', ['dadata_api_key' => 'x'])->assertForbidden();
        }

        // Клиента route.access уводит из /backend на сайт
        $this->actingAs($this->as(RoleEnum::CONSUMER))->get('/backend/verifications')->assertRedirect();

        $this->assertNotContains(
            $this->actingAs($this->as(RoleEnum::ADMIN))->get('/backend/verifications')->getStatusCode(),
            [302, 401, 403]
        );
    }

    #[Test]
    public function coupon_status_and_delete_need_permission(): void
    {
        $provider = $this->as(RoleEnum::PROVIDER);

        $this->actingAs($provider)->post('/backend/coupon-status', ['id' => 1])->assertForbidden();
        $this->actingAs($provider)->put('/coupon/status/1')->assertForbidden();
        $this->actingAs($provider)->delete('/delete-coupons', ['ids' => [1]])->assertForbidden();
    }
}
