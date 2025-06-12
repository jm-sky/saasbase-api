<?php

namespace Tests\Feature\Domain\Invoice;

use App\Domain\Auth\Models\User;
use App\Domain\Invoice\Controllers\InvoiceController;
use App\Domain\Invoice\Models\Invoice;
use App\Domain\Invoice\Models\NumberingTemplate;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use Tests\Traits\WithAuthenticatedUser;

/**
 * @internal
 */
#[CoversClass(InvoiceController::class)]
class InvoiceApiTest extends TestCase
{
    use RefreshDatabase;
    use WithAuthenticatedUser;

    private string $baseUrl = '/api/v1/invoices';

    private Tenant $tenant;

    private User $user;

    private NumberingTemplate $numberingTemplate;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create();
        $this->user   = $this->authenticateUser($this->tenant);

        Tenant::bypassTenant(Tenant::GLOBAL_TENANT_ID, function () {
            $this->numberingTemplate = NumberingTemplate::factory()->create([
                'tenant_id' => $this->tenant->id,
            ]);
        });
    }

    public function testCanShowInvoice(): void
    {
        $invoice = Tenant::bypassTenant($this->tenant->id, function () {
            return Invoice::factory()->create([
                'tenant_id'             => $this->tenant->id,
                'numbering_template_id' => $this->numberingTemplate->id,
            ]);
        });
        $response = $this->getJson($this->baseUrl . '/' . $invoice->id);
        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => [
                    'id'       => $invoice->id,
                    'tenantId' => $this->tenant->id,
                ],
            ])
        ;
    }

    public function testCanDeleteInvoice(): void
    {
        $invoice = Tenant::bypassTenant($this->tenant->id, function () {
            return Invoice::factory()->create([
                'tenant_id'             => $this->tenant->id,
                'numbering_template_id' => $this->numberingTemplate->id,
            ]);
        });
        $response = $this->deleteJson($this->baseUrl . '/' . $invoice->id);
        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertSoftDeleted('invoices', ['id' => $invoice->id]);
    }

    public function testReturns404ForNonexistentInvoice(): void
    {
        $response = $this->getJson($this->baseUrl . '/nonexistent-id');
        $response->assertStatus(Response::HTTP_NOT_FOUND);
        $response = $this->deleteJson($this->baseUrl . '/nonexistent-id');
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
