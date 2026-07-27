<?php

namespace Tests\Feature\Domain\Expense;

use App\Domain\Auth\Models\User;
use App\Domain\Expense\Controllers\ExpenseController;
use App\Domain\Expense\Models\Expense;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Response;
use Tests\Support\ExpenseApiPayloadFactory;
use Tests\TestCase;
use Tests\Traits\WithAuthenticatedUser;

/**
 * @internal
 */
#[CoversClass(ExpenseController::class)]
class ExpenseApiTest extends TestCase
{
    use RefreshDatabase;
    use WithAuthenticatedUser;

    private string $baseUrl = '/api/v1/expenses';

    private Tenant $tenant;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create();
        $this->user = $this->authenticateUser($this->tenant);
    }

    public function test_can_list_expenses_for_current_tenant_only(): void
    {
        Tenant::bypassTenant($this->tenant->id, function () {
            Expense::factory()->count(3)->create([
                'tenant_id' => $this->tenant->id,
            ]);
        });

        $otherTenant = Tenant::factory()->create();
        Tenant::bypassTenant($otherTenant->id, function () use ($otherTenant) {
            Expense::factory()->count(2)->create([
                'tenant_id' => $otherTenant->id,
            ]);
        });

        $response = $this->getJson($this->baseUrl);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'tenantId',
                        'number',
                        'status',
                        'createdAt',
                    ],
                ],
                'meta' => [
                    'currentPage',
                    'lastPage',
                    'perPage',
                    'total',
                ],
            ]);
    }

    public function test_can_show_expense(): void
    {
        $expense = Tenant::bypassTenant($this->tenant->id, function () {
            return Expense::factory()->create([
                'tenant_id' => $this->tenant->id,
            ]);
        });

        $response = $this->getJson($this->baseUrl.'/'.$expense->id);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => [
                    'id' => $expense->id,
                    'tenantId' => $this->tenant->id,
                ],
            ]);
    }

    public function test_cannot_show_expense_from_other_tenant(): void
    {
        $otherTenant = Tenant::factory()->create();
        $expense = Tenant::bypassTenant($otherTenant->id, function () use ($otherTenant) {
            return Expense::factory()->create([
                'tenant_id' => $otherTenant->id,
            ]);
        });

        $response = $this->getJson($this->baseUrl.'/'.$expense->id);

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_can_create_expense(): void
    {
        $payload = ExpenseApiPayloadFactory::make([
            'number' => 'EXP-CREATE-001',
        ]);

        $response = $this->postJson($this->baseUrl, $payload);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonPath('data.number', 'EXP-CREATE-001')
            ->assertJsonPath('data.tenantId', $this->tenant->id);

        $this->assertDatabaseHas('expenses', [
            'tenant_id' => $this->tenant->id,
            'number' => 'EXP-CREATE-001',
        ]);
    }

    public function test_can_update_expense(): void
    {
        $expense = Tenant::bypassTenant($this->tenant->id, function () {
            return Expense::factory()->create([
                'tenant_id' => $this->tenant->id,
            ]);
        });

        $response = $this->putJson($this->baseUrl.'/'.$expense->id, [
            'number' => 'EXP-UPDATED-001',
            'totalNet' => 200,
            'totalTax' => 46,
            'totalGross' => 246,
        ]);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonPath('number', 'EXP-UPDATED-001');

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'number' => 'EXP-UPDATED-001',
        ]);
    }

    public function test_cannot_update_expense_from_other_tenant(): void
    {
        $otherTenant = Tenant::factory()->create();
        $expense = Tenant::bypassTenant($otherTenant->id, function () use ($otherTenant) {
            return Expense::factory()->create([
                'tenant_id' => $otherTenant->id,
            ]);
        });

        $response = $this->putJson($this->baseUrl.'/'.$expense->id, [
            'number' => 'EXP-HACKED',
        ]);

        $response->assertStatus(Response::HTTP_NOT_FOUND);
        $this->assertDatabaseMissing('expenses', [
            'id' => $expense->id,
            'number' => 'EXP-HACKED',
        ]);
    }

    public function test_unauthenticated_user_cannot_create_expense(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer invalid-token')
            ->postJson($this->baseUrl, ExpenseApiPayloadFactory::make());

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_can_delete_expense(): void
    {
        $expense = Tenant::bypassTenant($this->tenant->id, function () {
            return Expense::factory()->create([
                'tenant_id' => $this->tenant->id,
            ]);
        });

        $response = $this->deleteJson($this->baseUrl.'/'.$expense->id);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertSoftDeleted('expenses', ['id' => $expense->id]);
    }

    public function test_returns404_for_nonexistent_expense(): void
    {
        $response = $this->getJson($this->baseUrl.'/01ARZ3NDEKTSV4RRFFQ69G5FAV');
        $response->assertStatus(Response::HTTP_NOT_FOUND);

        $response = $this->deleteJson($this->baseUrl.'/01ARZ3NDEKTSV4RRFFQ69G5FAV');
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_unauthenticated_user_cannot_list_expenses(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer invalid-token')
            ->getJson($this->baseUrl);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }
}
