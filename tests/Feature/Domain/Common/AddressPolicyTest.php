<?php

namespace Tests\Feature\Domain\Common;

use App\Domain\Auth\JwtHelper;
use App\Domain\Auth\Models\User;
use App\Domain\Common\Enums\AddressType;
use App\Domain\Common\Models\Address;
use App\Domain\Common\Policies\AddressPolicy;
use App\Domain\Rights\Enums\RoleName;
use App\Domain\Rights\Support\TenantScopedRoles;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use Tests\Traits\WithAuthenticatedUser;
use Tests\Traits\WithCountries;

/**
 * @internal
 */
#[CoversClass(AddressPolicy::class)]
class AddressPolicyTest extends TestCase
{
    use RefreshDatabase;
    use WithAuthenticatedUser;
    use WithCountries;

    private Tenant $tenant;

    private string $baseUrl;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedCountries();
        $this->tenant = Tenant::factory()->create();
        $this->baseUrl = '/api/v1/tenants/'.$this->tenant->id.'/addresses';
    }

    public function test_owner_or_admin_can_create_tenant_address(): void
    {
        $this->authenticateUser($this->tenant);

        $response = $this->postJson($this->baseUrl, $this->addressPayload());

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertDatabaseHas('addresses', [
            'tenant_id' => $this->tenant->id,
            'city' => 'Warszawa',
            'addressable_id' => $this->tenant->id,
            'addressable_type' => Tenant::class,
        ]);
    }

    public function test_regular_member_cannot_create_tenant_address(): void
    {
        $this->authenticateAsMember();

        $response = $this->postJson($this->baseUrl, $this->addressPayload());

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_owner_or_admin_can_delete_tenant_address(): void
    {
        $this->authenticateUser($this->tenant);

        $address = $this->createTenantAddress();

        $response = $this->deleteJson($this->baseUrl.'/'.$address->id);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing('addresses', ['id' => $address->id]);
    }

    public function test_regular_member_cannot_delete_tenant_address(): void
    {
        $this->authenticateUser($this->tenant);
        $address = $this->createTenantAddress();

        $this->authenticateAsMember();

        $response = $this->deleteJson($this->baseUrl.'/'.$address->id);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
        $this->assertDatabaseHas('addresses', ['id' => $address->id]);
    }

    public function test_member_can_view_tenant_addresses(): void
    {
        $this->authenticateUser($this->tenant);
        $this->createTenantAddress();

        $this->authenticateAsMember();

        $response = $this->getJson($this->baseUrl);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonCount(1, 'data');
    }

    private function addressPayload(): array
    {
        return [
            'country' => self::DEFAULT_COUNTRY,
            'city' => 'Warszawa',
            'street' => 'Marszałkowska',
            'type' => AddressType::REGISTERED_OFFICE->value,
            'isDefault' => true,
        ];
    }

    private function createTenantAddress(): Address
    {
        return Tenant::bypassTenant($this->tenant->id, function () {
            return $this->tenant->addresses()->create([
                'tenant_id' => $this->tenant->id,
                'country' => self::DEFAULT_COUNTRY,
                'city' => 'Kraków',
                'type' => AddressType::REGISTERED_OFFICE,
                'is_default' => false,
            ]);
        });
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
