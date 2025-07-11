<?php

namespace Tests\Feature\Domain\Financial;

use App\Domain\Auth\Models\User;
use App\Domain\Expense\Models\Expense;
use App\Domain\Financial\Controllers\FinancialReportController;
use App\Domain\Financial\Enums\InvoiceStatus;
use App\Domain\Invoice\Models\Invoice;
use App\Domain\Invoice\Models\NumberingTemplate;
use App\Domain\Tenant\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;
use Tests\Traits\WithAuthenticatedUser;

/**
 * @internal
 */
#[CoversClass(FinancialReportController::class)]
class FinancialReportControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithAuthenticatedUser;

    private User $user;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        $this->user   = User::factory()->create();
    }

    public function testGetBalanceWidgetData()
    {
        Tenant::bypassTenant($this->tenant->id, function () {
            $numberingTemplate = NumberingTemplate::factory()->create([
                'tenant_id' => $this->tenant->id,
            ]);

            // Create test invoices and expenses for current month
            Invoice::factory()->create([
                'tenant_id'             => $this->tenant->id,
                'status'                => InvoiceStatus::COMPLETED,
                'issue_date'            => Carbon::now()->startOfMonth(),
                'total_gross'           => 1000.00,
                'numbering_template_id' => $numberingTemplate->id,
            ]);

            Expense::factory()->create([
                'tenant_id'      => $this->tenant->id,
                'status'         => InvoiceStatus::COMPLETED,
                'issue_date'     => Carbon::now()->startOfMonth(),
                'total_gross'    => 300.00,
            ]);
        });

        $this->authenticateUser($this->tenant, $this->user);

        $response = $this->get('/api/v1/financial-reports/balance-widget');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'month' => [
                        'current',
                        'previous',
                        'changePercent',
                    ],
                    'year' => [
                        'current',
                        'previous',
                        'changePercent',
                    ],
                ],
            ])
        ;
    }

    public function testGetRevenueWidgetData()
    {
        $this->authenticateUser($this->tenant, $this->user);

        $response = $this->get('/api/v1/financial-reports/revenue-widget');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'month' => [
                        'current',
                        'changePercent',
                    ],
                    'year' => [
                        'current',
                        'changePercent',
                    ],
                ],
            ])
        ;
    }

    public function testGetExpensesWidgetData()
    {
        $this->authenticateUser($this->tenant, $this->user);

        $response = $this->get('/api/v1/financial-reports/expenses-widget');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'month' => [
                        'current',
                        'previous',
                        'changePercent',
                    ],
                    'year' => [
                        'current',
                        'previous',
                        'changePercent',
                    ],
                ],
            ])
        ;
    }

    public function testGetOverviewWidgetData()
    {
        $this->authenticateUser($this->tenant, $this->user);

        $response = $this->get('/api/v1/financial-reports/overview-widget');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'year',
                    'months' => [
                        '*' => [
                            'month',
                            'revenue',
                            'expenses',
                            'balance',
                        ],
                    ],
                ],
            ])
        ;
    }

    public function requiresAuthentication()
    {
        // Create a new test without authentication
        $response = $this->getJson('/api/v1/financial-reports/balance-widget');

        $response->assertStatus(401);
    }
}
