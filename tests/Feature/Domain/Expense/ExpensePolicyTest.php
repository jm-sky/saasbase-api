<?php

namespace Tests\Feature\Domain\Expense;

use App\Domain\Auth\JwtHelper;
use App\Domain\Auth\Models\User;
use App\Domain\Expense\Models\Expense;
use App\Domain\Expense\Policies\ExpensePolicy;
use App\Domain\Financial\Enums\InvoiceStatus;
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
#[CoversClass(ExpensePolicy::class)]
class ExpensePolicyTest extends TestCase
{
    use RefreshDatabase;
    use WithAuthenticatedUser;

    private string $baseUrl = '/api/v1/expenses';

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create();
    }

    public function test_owner_or_admin_can_delete_expense(): void
    {
        $this->authenticateUser($this->tenant);

        $expense = Tenant::bypassTenant($this->tenant->id, function () {
            return Expense::factory()->create([
                'tenant_id' => $this->tenant->id,
            ]);
        });

        $response = $this->deleteJson($this->baseUrl.'/'.$expense->id);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertSoftDeleted('expenses', ['id' => $expense->id]);
    }

    public function test_regular_member_cannot_delete_expense(): void
    {
        $user = User::factory()->create();
        $user->tenants()->attach($this->tenant, ['role' => RoleName::User->value]);
        TenantScopedRoles::assign($user, RoleName::User->value, $this->tenant->id);

        $token = JwtHelper::createTokenWithTenant($user, $this->tenant->id);
        $this->withHeader('Authorization', 'Bearer '.$token);

        $expense = Tenant::bypassTenant($this->tenant->id, function () {
            return Expense::factory()->create([
                'tenant_id' => $this->tenant->id,
            ]);
        });

        $response = $this->deleteJson($this->baseUrl.'/'.$expense->id);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'deleted_at' => null,
        ]);
    }

    public function test_tenant_member_can_pass_allocation_authorize_gate(): void
    {
        $this->authenticateUser($this->tenant);

        $expense = Tenant::bypassTenant($this->tenant->id, function () {
            return Expense::factory()->create([
                'tenant_id' => $this->tenant->id,
                'status' => InvoiceStatus::PROCESSING,
            ]);
        });

        // Before ExpensePolicy existed, authorize('update') always 403'd.
        // Clearing allocations is a thin update-gated endpoint — success or
        // an empty-state response both prove the gate opened.
        $response = $this->deleteJson($this->baseUrl.'/'.$expense->id.'/allocations/clear');

        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    public function test_tenant_member_can_pass_approval_start_authorize_gate(): void
    {
        $this->authenticateUser($this->tenant);

        $expense = Tenant::bypassTenant($this->tenant->id, function () {
            return Expense::factory()->create([
                'tenant_id' => $this->tenant->id,
                'status' => InvoiceStatus::PROCESSING,
            ]);
        });

        $response = $this->postJson($this->baseUrl.'/'.$expense->id.'/approval/start');

        // Gate must open (not 403). 422 is acceptable when no matching
        // workflow exists — that is business logic past authorization.
        $this->assertNotEquals(Response::HTTP_FORBIDDEN, $response->status());
        $this->assertContains($response->status(), [
            Response::HTTP_CREATED,
            Response::HTTP_OK,
            Response::HTTP_UNPROCESSABLE_ENTITY,
        ]);
    }

    public function test_member_of_other_tenant_cannot_clear_allocations(): void
    {
        $this->authenticateUser($this->tenant);

        $otherTenant = Tenant::factory()->create();
        $expense = Tenant::bypassTenant($otherTenant->id, function () use ($otherTenant) {
            return Expense::factory()->create([
                'tenant_id' => $otherTenant->id,
                'status' => InvoiceStatus::PROCESSING,
            ]);
        });

        $response = $this->deleteJson($this->baseUrl.'/'.$expense->id.'/allocations/clear');

        // Tenant scope typically 404s before policy; either is fine as long as
        // the foreign expense is not mutated.
        $this->assertContains($response->status(), [
            Response::HTTP_NOT_FOUND,
            Response::HTTP_FORBIDDEN,
        ]);
    }
}
