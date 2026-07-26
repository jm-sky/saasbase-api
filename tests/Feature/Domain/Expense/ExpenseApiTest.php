<?php

namespace Tests\Feature\Domain\Expense;

use App\Domain\Auth\Models\User;
use App\Domain\Expense\Controllers\ExpenseController;
use App\Domain\Expense\Models\Expense;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Response;
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
        $this->app['auth']->forgetGuards();

        $response = $this->getJson($this->baseUrl);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }
}
