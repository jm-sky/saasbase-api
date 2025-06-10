<?php

namespace Tests\Feature\Domain\Invoice;

use App\Domain\Auth\Models\User;
use App\Domain\Invoice\Models\Invoice;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use Tests\Traits\WithAuthenticatedUser;

/**
 * @internal
 *
 * @coversNothing
 */
class InvoiceApiTest extends TestCase
{
    use RefreshDatabase;
    use WithAuthenticatedUser;

    private string $baseUrl = '/api/v1/invoices';

    private Tenant $tenant;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create();
        $this->user   = $this->authenticateUser($this->tenant);
    }

    public function testCanListTenantAndGlobalInvoices(): void
    {
        // Tenant-specific invoices
        $tenantInvoices = Tenant::bypassTenant($this->tenant->id, function () {
            return Invoice::factory()->count(2)->create([
                'tenant_id' => $this->tenant->id,
            ]);
        });
        // Global invoices
        $globalInvoices = Tenant::bypassTenant(Tenant::GLOBAL_TENANT_ID, function () {
            return Invoice::factory()->count(1)->create([
                'tenant_id' => Tenant::GLOBAL_TENANT_ID,
            ]);
        });
        $response = $this->getJson($this->baseUrl);
        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'tenantId', 'type', 'status', 'number', 'numberingTemplateId', 'totalNet', 'totalTax', 'totalGross', 'currency', 'exchangeRate', 'seller', 'buyer', 'data', 'payment', 'options', 'issueDate', 'createdAt', 'updatedAt', 'numberingTemplate',
                    ],
                ],
                'meta' => [
                    'currentPage', 'lastPage', 'perPage', 'total',
                ],
            ])
            ->assertJsonCount(3, 'data')
        ;
    }

    public function testCanShowInvoice(): void
    {
        $invoice = Tenant::bypassTenant($this->tenant->id, function () {
            return Invoice::factory()->create(['tenant_id' => $this->tenant->id]);
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
            return Invoice::factory()->create(['tenant_id' => $this->tenant->id]);
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
