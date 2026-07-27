<?php

namespace Tests\Feature\Domain\Tenant;

use App\Domain\Auth\JwtHelper;
use App\Domain\Auth\Models\User;
use App\Domain\Rights\Enums\RoleName;
use App\Domain\Rights\Support\TenantScopedRoles;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Policies\TenantPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use Tests\Traits\WithAuthenticatedUser;

/**
 * @internal
 */
#[CoversClass(TenantPolicy::class)]
class TenantPolicyTest extends TestCase
{
    use RefreshDatabase;
    use WithAuthenticatedUser;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create();
    }

    public function test_member_can_view_own_tenant(): void
    {
        $this->authenticateAsMember();

        $response = $this->getJson('/api/v1/tenants/'.$this->tenant->id);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonPath('data.id', $this->tenant->id);
    }

    public function test_owner_or_admin_can_update_tenant(): void
    {
        $this->authenticateUser($this->tenant);

        $response = $this->putJson('/api/v1/tenants/'.$this->tenant->id, [
            'name' => 'Updated Tenant Name',
            'slug' => $this->tenant->slug,
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $this->assertDatabaseHas('tenants', [
            'id' => $this->tenant->id,
            'name' => 'Updated Tenant Name',
        ]);
    }

    public function test_regular_member_cannot_update_tenant(): void
    {
        $this->authenticateAsMember();

        $response = $this->putJson('/api/v1/tenants/'.$this->tenant->id, [
            'name' => 'Should Not Stick',
            'slug' => $this->tenant->slug,
        ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
        $this->assertDatabaseMissing('tenants', [
            'id' => $this->tenant->id,
            'name' => 'Should Not Stick',
        ]);
    }

    public function test_regular_member_cannot_delete_tenant(): void
    {
        $this->authenticateAsMember();

        $response = $this->deleteJson('/api/v1/tenants/'.$this->tenant->id);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
        $this->assertDatabaseHas('tenants', ['id' => $this->tenant->id]);
    }

    private function authenticateAsMember(): User
    {
        $user = User::factory()->create();
        $user->tenants()->attach($this->tenant, ['role' => RoleName::User->value]);
        TenantScopedRoles::assign($user, RoleName::User->value, $this->tenant->id);

        $token = JwtHelper::createTokenWithTenant($user, $this->tenant->id);
        $this->withHeader('Authorization', 'Bearer '.$token);

        return $user;
    }
}
