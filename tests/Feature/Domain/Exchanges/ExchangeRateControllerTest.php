<?php

namespace Tests\Feature\Domain\Exchanges;

use App\Domain\Exchanges\Controllers\ExchangeRateController;
use App\Domain\Exchanges\Models\Currency;
use App\Domain\Exchanges\Models\ExchangeRate;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use Tests\Traits\WithAuthenticatedUser;

/**
 * @internal
 */
#[CoversClass(ExchangeRateController::class)]
class ExchangeRateControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithAuthenticatedUser;

    private string $baseUrl = '/api/v1/exchange-rates';

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create();
        $this->authenticateUser($this->tenant);
    }

    public function testCanListExchangeRates(): void
    {
        Currency::factory()->create(['code' => 'USD']);
        Currency::factory()->create(['code' => 'EUR']);
        Currency::factory()->create(['code' => 'GBP']);

        ExchangeRate::factory()->create(['currency' => 'USD']);
        ExchangeRate::factory()->create(['currency' => 'EUR']);
        ExchangeRate::factory()->create(['currency' => 'GBP']);

        $response = $this->getJson($this->baseUrl);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'date',
                        'rate',
                        'table',
                        'source',
                        'createdAt',
                    ],
                ],
                'meta' => [
                    'currentPage',
                    'lastPage',
                    'perPage',
                    'total',
                ],
            ])
        ;
    }

    public function testCanFilterExchangeRatesByCurrency(): void
    {
        Currency::factory()->create(['code' => 'USD']);
        Currency::factory()->create(['code' => 'EUR']);
        Currency::factory()->create(['code' => 'PLN']);

        ExchangeRate::factory()->create(['currency' => 'USD']);
        ExchangeRate::factory()->create(['currency' => 'EUR']);
        ExchangeRate::factory()->create(['currency' => 'PLN']);

        $response = $this->getJson($this->baseUrl . '?filter[currency]=USD');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.currency', null) // currency is not exposed in resource, so just check count
        ;
    }

    public function testCanShowExchangeRate(): void
    {
        $exchangeRate = ExchangeRate::factory()->create();

        $response = $this->getJson($this->baseUrl . '/' . $exchangeRate->id);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'date',
                    'rate',
                    'table',
                    'source',
                    'createdAt',
                ],
            ])
            ->assertJsonPath('data.id', $exchangeRate->id)
        ;
    }

    public function testCanFilterExchangeRatesByDate(): void
    {
        $date = now()->toDateString();

        Currency::factory()->create(['code' => 'PLN']);
        Currency::factory()->create(['code' => 'JPY']);
        Currency::factory()->create(['code' => 'USD']);

        ExchangeRate::factory()->create([
            'currency' => 'JPY',
            'date'     => $date,
        ]);

        ExchangeRate::factory()->create([
            'currency' => 'USD',
            'date'     => now()->subDay()->toDateString(),
        ]);

        $response = $this->getJson($this->baseUrl . '?filter[date]=' . $date);

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.date', $date)
        ;
    }
}
