<?php

namespace Tests\Feature\Domain\Invoice;

use App\Domain\Auth\JwtHelper;
use App\Domain\Auth\Models\User;
use App\Domain\Invoice\Models\Invoice;
use App\Domain\Invoice\Models\NumberingTemplate;
use App\Domain\Invoice\Policies\InvoicePolicy;
use App\Domain\Rights\Enums\RoleName;
use App\Domain\Rights\Support\TenantScopedRoles;
use App\Domain\ShareToken\Models\ShareToken;
use App\Domain\ShareToken\Services\ShareTokenService;
use App\Domain\Tenant\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use Tests\Traits\WithAuthenticatedUser;

/**
 * Share-token endpoints authorize through InvoicePolicy (view/update),
 * not a dedicated ShareToken policy — these tests lock that gate.
 *
 * @internal
 */
#[CoversClass(InvoicePolicy::class)]
class InvoiceShareTokenPolicyTest extends TestCase
{
    use RefreshDatabase;
    use WithAuthenticatedUser;

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

    public function test_tenant_member_can_create_share_token(): void
    {
        $this->authenticateUser($this->tenant);
        $invoice = $this->createInvoice();

        $response = $this->postJson($this->shareTokensUrl($invoice), [
            'expiresAt' => Carbon::now()->addDay()->toIso8601String(),
            'onlyForAuthenticated' => false,
            'maxUsage' => 3,
        ]);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure(['data' => ['id', 'token']]);
        $this->assertDatabaseHas('share_tokens', [
            'shareable_type' => Invoice::class,
            'shareable_id' => $invoice->id,
        ]);
    }

    public function test_destroy_revokes_token_not_invoice(): void
    {
        $this->authenticateUser($this->tenant);
        $invoice = $this->createInvoice();

        $shareToken = app(ShareTokenService::class)->createToken(
            shareableType: Invoice::class,
            shareableId: $invoice->id,
            onlyForAuthenticated: false,
            expiresAt: Carbon::now()->addDay(),
            maxUsage: 5,
        );

        $response = $this->deleteJson($this->shareTokensUrl($invoice).'/'.$shareToken->id);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing('share_tokens', ['id' => $shareToken->id]);
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'deleted_at' => null,
        ]);
    }

    public function test_member_of_other_tenant_cannot_create_share_token(): void
    {
        $invoice = $this->createInvoice();

        $otherTenant = Tenant::factory()->create();
        $user = User::factory()->create();
        $user->tenants()->attach($otherTenant, ['role' => RoleName::Admin->value]);
        TenantScopedRoles::assign($user, RoleName::Admin->value, $otherTenant->id);
        $this->withHeader('Authorization', 'Bearer '.JwtHelper::createTokenWithTenant($user, $otherTenant->id));

        $response = $this->postJson($this->shareTokensUrl($invoice), [
            'expiresAt' => Carbon::now()->addDay()->toIso8601String(),
            'onlyForAuthenticated' => false,
            'maxUsage' => 1,
        ]);

        $this->assertContains($response->status(), [
            Response::HTTP_NOT_FOUND,
            Response::HTTP_FORBIDDEN,
        ]);
        $this->assertSame(0, ShareToken::query()->where('shareable_id', $invoice->id)->count());
    }

    private function createInvoice(): Invoice
    {
        return Tenant::bypassTenant($this->tenant->id, function () {
            return Invoice::factory()->create([
                'tenant_id' => $this->tenant->id,
                'numbering_template_id' => $this->numberingTemplate->id,
            ]);
        });
    }

    private function shareTokensUrl(Invoice $invoice): string
    {
        return '/api/v1/invoices/'.$invoice->id.'/share-tokens';
    }
}
