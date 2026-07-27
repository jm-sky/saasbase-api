<?php

namespace Tests\Feature\Domain\Common;

use App\Domain\Auth\JwtHelper;
use App\Domain\Auth\Models\User;
use App\Domain\Common\Models\BankAccount;
use App\Domain\Common\Policies\BankAccountPolicy;
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
#[CoversClass(BankAccountPolicy::class)]
class BankAccountPolicyTest extends TestCase
{
    use RefreshDatabase;
    use WithAuthenticatedUser;

    private Tenant $tenant;

    private string $baseUrl;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create();
        $this->baseUrl = '/api/v1/tenants/'.$this->tenant->id.'/bank-accounts';
    }

    public function test_owner_or_admin_can_create_tenant_bank_account(): void
    {
        $this->authenticateUser($this->tenant);

        $response = $this->postJson($this->baseUrl, $this->bankAccountPayload());

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertDatabaseHas('bank_accounts', [
            'tenant_id' => $this->tenant->id,
            'iban' => 'PL61109010140000071219812874',
            'bankable_id' => $this->tenant->id,
            'bankable_type' => Tenant::class,
        ]);
    }

    public function test_regular_member_cannot_create_tenant_bank_account(): void
    {
        $this->authenticateAsMember();

        $response = $this->postJson($this->baseUrl, $this->bankAccountPayload());

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_owner_or_admin_can_delete_tenant_bank_account(): void
    {
        $this->authenticateUser($this->tenant);

        $account = $this->createTenantBankAccount();

        $response = $this->deleteJson($this->baseUrl.'/'.$account->id);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing('bank_accounts', ['id' => $account->id]);
    }

    public function test_regular_member_cannot_delete_tenant_bank_account(): void
    {
        $this->authenticateUser($this->tenant);
        $account = $this->createTenantBankAccount();

        $this->authenticateAsMember();

        $response = $this->deleteJson($this->baseUrl.'/'.$account->id);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
        $this->assertDatabaseHas('bank_accounts', ['id' => $account->id]);
    }

    public function test_member_can_view_tenant_bank_accounts(): void
    {
        $this->authenticateUser($this->tenant);
        $this->createTenantBankAccount();

        $this->authenticateAsMember();

        $response = $this->getJson($this->baseUrl);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonCount(1, 'data');
    }

    private function bankAccountPayload(): array
    {
        return [
            'iban' => 'PL61109010140000071219812874',
            'swift' => 'WBKPPLPP',
            'bankName' => 'Test Bank',
            'currency' => 'PLN',
            'isDefault' => true,
        ];
    }

    private function createTenantBankAccount(): BankAccount
    {
        return Tenant::bypassTenant($this->tenant->id, function () {
            return $this->tenant->bankAccounts()->create([
                'tenant_id' => $this->tenant->id,
                'iban' => 'PL61109010140000071219812874',
                'currency' => 'PLN',
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
