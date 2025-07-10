<?php

namespace Tests\Feature\Domain\Invoice;

use App\Domain\Auth\Models\User;
use App\Domain\Invoice\Controllers\NumberingTemplateController;
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
#[CoversClass(NumberingTemplateController::class)]
class NumberingTemplateApiTest extends TestCase
{
    use RefreshDatabase;
    use WithAuthenticatedUser;

    private string $baseUrl = '/api/v1/numbering-templates';

    private Tenant $tenant;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create();
        $this->user   = $this->authenticateUser($this->tenant);
    }

    public function testCanUpdateNumberingTemplate(): void
    {
        $template = Tenant::bypassTenant($this->tenant->id, function () {
            return NumberingTemplate::factory()->create(['tenant_id' => $this->tenant->id]);
        });
        $updateData = [
            'name'       => 'Updated Template',
            'nextNumber' => 42,
        ];
        $response = $this->putJson($this->baseUrl . '/' . $template->id, $updateData);
        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => [
                    'id'         => $template->id,
                    'name'       => 'Updated Template',
                    'nextNumber' => 42,
                ],
            ])
        ;
        $this->assertDatabaseHas('numbering_templates', [
            'id'          => $template->id,
            'name'        => 'Updated Template',
            'next_number' => 42,
        ]);
    }

    public function testCanDeleteNumberingTemplate(): void
    {
        $template = Tenant::bypassTenant($this->tenant->id, function () {
            return NumberingTemplate::factory()->create(['tenant_id' => $this->tenant->id]);
        });
        $response = $this->deleteJson($this->baseUrl . '/' . $template->id);
        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertSoftDeleted('numbering_templates', ['id' => $template->id]);
    }

    public function testCanSetDefaultNumberingTemplate(): void
    {
        $templates = Tenant::bypassTenant($this->tenant->id, function () {
            return NumberingTemplate::factory()->count(2)->create(['tenant_id' => $this->tenant->id, 'is_default' => false]);
        });
        $template = $templates->first();
        $response = $this->postJson($this->baseUrl . '/' . $template->id . '/set-default');
        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => [
                    'id'        => $template->id,
                    'isDefault' => true,
                ],
            ])
        ;
        $this->assertDatabaseHas('numbering_templates', [
            'id'         => $template->id,
            'is_default' => true,
        ]);
        $this->assertDatabaseHas('numbering_templates', [
            'id'         => $templates->last()->id,
            'is_default' => false,
        ]);
    }
}
