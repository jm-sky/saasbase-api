<?php

namespace Tests\Feature\Domain\Exchanges;

use App\Domain\Exchanges\Controllers\ExchangeController;
use App\Domain\Exchanges\Models\Exchange;
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
#[CoversClass(ExchangeController::class)]
class ExchangeControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithAuthenticatedUser;

    private string $baseUrl = '/api/v1/exchanges';

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create();
        $this->authenticateUser($this->tenant);
    }

    public function testCanListExchanges(): void
    {
        $this->markTestSkipped('Need to fix exchange listing functionality');

        Exchange::factory()->count(3)->create();

        $response = $this->getJson($this->baseUrl);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'currency',
                        'createdAt',
                        'updatedAt',
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

    public function testCanFilterExchangesByCurrency(): void
    {
        $this->markTestSkipped('Need to fix exchange filtering functionality');

        Exchange::factory()->create(['currency' => 'USD']);
        Exchange::factory()->create(['currency' => 'EUR']);
        Exchange::factory()->create(['currency' => 'PLN']);

        $response = $this->getJson($this->baseUrl . '?filter[currency]=USD');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.currency', 'USD')
        ;
    }

    public function testCanShowExchange(): void
    {
        $this->markTestSkipped('Need to fix exchange retrieval functionality');

        $exchange = Exchange::factory()->create();

        $response = $this->getJson($this->baseUrl . '/' . $exchange->id);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'currency',
                    'createdAt',
                    'updatedAt',
                ],
            ])
            ->assertJsonPath('data.id', $exchange->id)
        ;
    }

    public function testCanGetExchangeRates(): void
    {
        $this->markTestSkipped('Need to fix exchange rates functionality');

        $exchange = Exchange::factory()->create();
        ExchangeRate::factory()->count(3)->create(['exchange_id' => $exchange->id]);

        $response = $this->getJson($this->baseUrl . '/' . $exchange->id . '/rates');

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'exchangeId',
                        'date',
                        'rate',
                        'table',
                        'source',
                        'createdAt',
                    ],
                ],
            ])
        ;
    }

    public function testCanFilterExchangeRatesByDate(): void
    {
        $this->markTestSkipped('Need to fix exchange rates date filtering');

        $exchange = Exchange::factory()->create();
        $date     = now()->toDateString();
        ExchangeRate::factory()->create([
            'exchange_id' => $exchange->id,
            'date'        => $date,
        ]);
        ExchangeRate::factory()->create([
            'exchange_id' => $exchange->id,
            'date'        => now()->subDay()->toDateString(),
        ]);

        $response = $this->getJson($this->baseUrl . '/' . $exchange->id . '/rates?date=' . $date);

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.date', $date)
        ;
    }
}
