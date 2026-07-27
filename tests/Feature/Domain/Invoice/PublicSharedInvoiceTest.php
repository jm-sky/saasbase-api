<?php

namespace Tests\Feature\Domain\Invoice;

use App\Domain\Invoice\Controllers\PublicSharedInvoiceController;
use App\Domain\Invoice\Models\Invoice;
use App\Domain\Invoice\Models\NumberingTemplate;
use App\Domain\ShareToken\Services\ShareTokenService;
use App\Domain\Tenant\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use Tests\Traits\WithAuthenticatedUser;

/**
 * @internal
 */
#[CoversClass(PublicSharedInvoiceController::class)]
class PublicSharedInvoiceTest extends TestCase
{
    use RefreshDatabase;
    use WithAuthenticatedUser;

    public function test_can_view_invoice_via_valid_share_token(): void
    {
        $tenant = Tenant::factory()->create();
        $this->authenticateUser($tenant);

        $numberingTemplate = Tenant::bypassTenant(Tenant::GLOBAL_TENANT_ID, function () use ($tenant) {
            return NumberingTemplate::factory()->create([
                'tenant_id' => $tenant->id,
            ]);
        });

        $invoice = Tenant::bypassTenant($tenant->id, function () use ($tenant, $numberingTemplate) {
            return Invoice::factory()->create([
                'tenant_id' => $tenant->id,
                'numbering_template_id' => $numberingTemplate->id,
            ]);
        });

        $shareToken = app(ShareTokenService::class)->createToken(
            shareableType: Invoice::class,
            shareableId: $invoice->id,
            onlyForAuthenticated: false,
            expiresAt: Carbon::now()->addDay(),
            maxUsage: 5,
        );

        $response = $this->getJson('/api/v1/shared/invoices/'.$shareToken->token);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonPath('data.id', $invoice->id)
            ->assertJsonPath('data.number', $invoice->number);
    }

    public function test_expired_share_token_returns_gone(): void
    {
        $tenant = Tenant::factory()->create();
        $this->authenticateUser($tenant);

        $numberingTemplate = Tenant::bypassTenant(Tenant::GLOBAL_TENANT_ID, function () use ($tenant) {
            return NumberingTemplate::factory()->create([
                'tenant_id' => $tenant->id,
            ]);
        });

        $invoice = Tenant::bypassTenant($tenant->id, function () use ($tenant, $numberingTemplate) {
            return Invoice::factory()->create([
                'tenant_id' => $tenant->id,
                'numbering_template_id' => $numberingTemplate->id,
            ]);
        });

        $shareToken = app(ShareTokenService::class)->createToken(
            shareableType: Invoice::class,
            shareableId: $invoice->id,
            onlyForAuthenticated: false,
            expiresAt: Carbon::now()->subMinute(),
            maxUsage: 5,
        );

        $response = $this->getJson('/api/v1/shared/invoices/'.$shareToken->token);

        $response->assertStatus(Response::HTTP_GONE);
    }
}
