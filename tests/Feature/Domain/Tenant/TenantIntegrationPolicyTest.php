<?php

namespace Tests\Feature\Domain\Tenant;

use App\Domain\Auth\JwtHelper;
use App\Domain\Auth\Models\User;
use App\Domain\Rights\Enums\RoleName;
use App\Domain\Rights\Support\TenantScopedRoles;
use App\Domain\Tenant\Enums\TenantIntegrationMode;
use App\Domain\Tenant\Enums\TenantIntegrationType;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Models\TenantIntegration;
use App\Domain\Tenant\Policies\TenantIntegrationPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use Tests\Traits\WithAuthenticatedUser;

/**
 * @internal
 */
#[CoversClass(TenantIntegrationPolicy::class)]
class TenantIntegrationPolicyTest extends TestCase
{
    use RefreshDatabase;
    use WithAuthenticatedUser;

    private Tenant $tenant;

    private string $baseUrl;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create();
        $this->baseUrl = '/api/v1/tenants/'.$this->tenant->id.'/integrations';
    }

    public function test_owner_or_admin_can_create_integration(): void
    {
        $this->authenticateUser($this->tenant);

        $response = $this->postJson($this->baseUrl, [
            'type' => TenantIntegrationType::RegonApi->value,
            'mode' => TenantIntegrationMode::Shared->value,
            'enabled' => true,
        ]);

        // JsonResource defaults to 200; policy gate + persistence are what matter.
        $response->assertSuccessful();
        $this->assertDatabaseHas('tenant_integrations', [
            'tenant_id' => $this->tenant->id,
            'type' => TenantIntegrationType::RegonApi->value,
        ]);
    }

    public function test_regular_member_cannot_create_integration(): void
    {
        $this->authenticateAsMember();

        $response = $this->postJson($this->baseUrl, [
            'type' => TenantIntegrationType::RegonApi->value,
            'mode' => TenantIntegrationMode::Shared->value,
            'enabled' => true,
        ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
        $this->assertDatabaseMissing('tenant_integrations', [
            'tenant_id' => $this->tenant->id,
            'type' => TenantIntegrationType::RegonApi->value,
        ]);
    }

    public function test_owner_or_admin_can_delete_integration(): void
    {
        $this->authenticateUser($this->tenant);

        $create = $this->postJson($this->baseUrl, [
            'type' => TenantIntegrationType::S3->value,
            'mode' => TenantIntegrationMode::Shared->value,
            'enabled' => true,
        ]);
        $create->assertSuccessful();
        $integrationId = $create->json('data.id');
        $this->assertNotEmpty($integrationId);

        $response = $this->deleteJson($this->baseUrl.'/'.$integrationId);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing('tenant_integrations', ['id' => $integrationId]);
    }

    public function test_regular_member_cannot_delete_integration(): void
    {
        $integration = TenantIntegration::query()->create([
            'tenant_id' => $this->tenant->id,
            'type' => TenantIntegrationType::Jira->value,
            'mode' => TenantIntegrationMode::Shared->value,
            'enabled' => true,
        ]);

        $this->authenticateAsMember();

        $response = $this->deleteJson($this->baseUrl.'/'.$integration->id);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
        $this->assertDatabaseHas('tenant_integrations', ['id' => $integration->id]);
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
