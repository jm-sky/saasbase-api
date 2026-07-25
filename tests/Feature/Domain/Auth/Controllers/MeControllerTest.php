<?php

namespace Tests\Feature\Domain\Auth\Controllers;

use App\Domain\Auth\Controllers\MeController;
use App\Domain\Rights\Enums\RoleName;
use App\Domain\Rights\Support\TenantScopedRoles;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use Tests\Traits\WithAuthenticatedUser;

/**
 * @internal
 */
#[CoversClass(MeController::class)]
class MeControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithAuthenticatedUser;

    public function test_me_returns_tenant_scoped_roles_for_current_tenant(): void
    {
        $tenant = Tenant::factory()->create();
        $this->authenticateUser($tenant);

        $response = $this->getJson('/api/v1/me');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonPath('data.roles', [RoleName::Admin->value]);
    }

    public function test_me_returns_empty_roles_without_tenant_context(): void
    {
        $this->authenticateUser();

        $response = $this->getJson('/api/v1/me');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonPath('data.roles', [])
            ->assertJsonPath('data.permissions', []);
    }

    public function test_me_does_not_leak_roles_from_another_tenant(): void
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();
        $user = $this->authenticateUser($tenantA);

        $user->tenants()->attach($tenantB, ['role' => RoleName::User->value]);
        TenantScopedRoles::assign($user, RoleName::Owner->value, $tenantB->id);

        $response = $this->getJson('/api/v1/me');

        $response->assertStatus(Response::HTTP_OK);
        $roles = $response->json('data.roles');
        $this->assertIsArray($roles);
        $this->assertContains(RoleName::Admin->value, $roles);
        $this->assertNotContains(RoleName::Owner->value, $roles);
    }
}
