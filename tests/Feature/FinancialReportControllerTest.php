<?php

namespace Tests\Feature;

use App\Domain\Auth\Models\User;
use App\Domain\Expense\Models\Expense;
use App\Domain\Financial\Enums\InvoiceStatus;
use App\Domain\Invoice\Models\Invoice;
use App\Domain\Tenant\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class FinancialReportControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        $this->user   = User::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $this->actingAs($this->user, 'api');
    }

    /** @test */
    public function canGetBalanceWidgetData()
    {
        // Create test invoices and expenses for current month
        Invoice::factory()->create([
            'tenant_id'      => $this->tenant->id,
            'status'         => InvoiceStatus::COMPLETED,
            'issue_date'     => Carbon::now()->startOfMonth(),
            'total_gross'    => 1000.00,
        ]);

        Expense::factory()->create([
            'tenant_id'      => $this->tenant->id,
            'status'         => InvoiceStatus::COMPLETED,
            'issue_date'     => Carbon::now()->startOfMonth(),
            'total_gross'    => 300.00,
        ]);

        $response = $this->get('/api/v1/financial-reports/balance-widget');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'currentMonth' => [
                        'balance',
                        'changePercent',
                    ],
                    'currentYear' => [
                        'balance',
                        'changePercent',
                    ],
                ],
            ])
        ;
    }

    /** @test */
    public function canGetRevenueWidgetData()
    {
        $response = $this->get('/api/v1/financial-reports/revenue-widget');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'currentMonth' => [
                        'revenue',
                        'changePercent',
                    ],
                    'currentYear' => [
                        'revenue',
                        'changePercent',
                    ],
                ],
            ])
        ;
    }

    /** @test */
    public function canGetExpensesWidgetData()
    {
        $response = $this->get('/api/v1/financial-reports/expenses-widget');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'currentMonth' => [
                        'expenses',
                        'changePercent',
                    ],
                    'currentYear' => [
                        'expenses',
                        'changePercent',
                    ],
                ],
            ])
        ;
    }

    /** @test */
    public function canGetOverviewWidgetData()
    {
        $response = $this->get('/api/v1/financial-reports/overview-widget');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'year',
                    'months' => [
                        '*' => [
                            'month',
                            'monthName',
                            'revenue',
                            'expenses',
                            'balance',
                        ],
                    ],
                ],
            ])
        ;
    }

    /** @test */
    public function requiresAuthentication()
    {
        // Create a new test without authentication
        $response = $this->withHeaders([])
            ->get('/api/v1/financial-reports/balance-widget')
        ;

        $response->assertStatus(401);
    }
}
