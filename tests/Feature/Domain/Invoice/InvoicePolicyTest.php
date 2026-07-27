<?php

namespace Tests\Feature\Domain\Invoice;

use App\Domain\Auth\JwtHelper;
use App\Domain\Auth\Models\User;
use App\Domain\Invoice\Models\Invoice;
use App\Domain\Invoice\Models\NumberingTemplate;
use App\Domain\Invoice\Policies\InvoicePolicy;
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
#[CoversClass(InvoicePolicy::class)]
class InvoicePolicyTest extends TestCase
{
    use RefreshDatabase;
    use WithAuthenticatedUser;

    private string $baseUrl = '/api/v1/invoices';

    private Tenant $tenant;

    private NumberingTemplate $numberingTemplate;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create();

        Tenant::bypassTenant(Tenant::GLOBAL_TENANT_ID, function () {
            $this->numberingTemplate = NumberingTemplate::factory()->create([
                'tenant_id' => $this->tenant->id,
            ]);
        });
    }

    public function test_owner_or_admin_can_delete_invoice(): void
    {
        $this->authenticateUser($this->tenant);

        $invoice = Tenant::bypassTenant($this->tenant->id, function () {
            return Invoice::factory()->create([
                'tenant_id' => $this->tenant->id,
                'numbering_template_id' => $this->numberingTemplate->id,
            ]);
        });

        $response = $this->deleteJson($this->baseUrl.'/'.$invoice->id);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertSoftDeleted('invoices', ['id' => $invoice->id]);
    }

    public function test_regular_member_cannot_delete_invoice(): void
    {
        $user = User::factory()->create();
        $user->tenants()->attach($this->tenant, ['role' => RoleName::User->value]);
        TenantScopedRoles::assign($user, RoleName::User->value, $this->tenant->id);

        $token = JwtHelper::createTokenWithTenant($user, $this->tenant->id);
        $this->withHeader('Authorization', 'Bearer '.$token);

        $invoice = Tenant::bypassTenant($this->tenant->id, function () {
            return Invoice::factory()->create([
                'tenant_id' => $this->tenant->id,
                'numbering_template_id' => $this->numberingTemplate->id,
            ]);
        });

        $response = $this->deleteJson($this->baseUrl.'/'.$invoice->id);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'deleted_at' => null,
        ]);
    }
}
