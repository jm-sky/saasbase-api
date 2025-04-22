<?php

namespace Tests\Feature\Domain\Common\Controllers;

use App\Domain\Common\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversNothing;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use Tests\Traits\WithAuthenticatedUser;

/**
 * @internal
 *
 * @coversNothing
 */
class CountryControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithAuthenticatedUser;

    private string $baseUrl = '/api/v1/countries';

    protected function setUp(): void
    {
        parent::setUp();
        $tenant = \App\Domain\Tenant\Models\Tenant::factory()->create();
        $this->authenticateUser($tenant);
    }

    public function testCanListCountries(): void
    {
        Country::factory()->count(3)->create();

        $response = $this->getJson($this->baseUrl);

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'code',
                        'code3',
                        'numericCode',
                        'phoneCode',
                        'capital',
                        'currency',
                        'currencyCode',
                        'currencySymbol',
                        'tld',
                        'native',
                        'region',
                        'subregion',
                        'emoji',
                        'emojiU',
                        'createdAt',
                        'updatedAt',
                    ],
                ],
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ],
            ])
        ;
    }

    public function testCanFilterCountriesByName(): void
    {
        Country::factory()->create(['name' => 'Poland']);
        Country::factory()->create(['name' => 'Portugal']);
        Country::factory()->create(['name' => 'Germany']);

        $response = $this->getJson($this->baseUrl . '?filter[name]=Pol');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Poland')
        ;
    }

    public function testCanFilterCountriesByCode(): void
    {
        Country::factory()->create(['code' => 'PL', 'name' => 'Poland']);
        Country::factory()->create(['code' => 'PT', 'name' => 'Portugal']);
        Country::factory()->create(['code' => 'DE', 'name' => 'Germany']);

        $response = $this->getJson($this->baseUrl . '?filter[code]=PL');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Poland')
        ;
    }

    public function testCanFilterCountriesByRegion(): void
    {
        Country::factory()->create(['region' => 'Europe', 'name' => 'Poland']);
        Country::factory()->create(['region' => 'Europe', 'name' => 'Germany']);
        Country::factory()->create(['region' => 'Asia', 'name' => 'Japan']);

        $response = $this->getJson($this->baseUrl . '?filter[region]=Europe');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'Germany')
            ->assertJsonPath('data.1.name', 'Poland')
        ;
    }

    public function testCanSortCountries(): void
    {
        Country::factory()->create(['name' => 'Poland']);
        Country::factory()->create(['name' => 'Germany']);
        Country::factory()->create(['name' => 'Austria']);

        $response = $this->getJson($this->baseUrl . '?sort=name');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonPath('data.0.name', 'Austria')
            ->assertJsonPath('data.1.name', 'Germany')
            ->assertJsonPath('data.2.name', 'Poland')
        ;

        $response = $this->getJson($this->baseUrl . '?sort=-name');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonPath('data.0.name', 'Poland')
            ->assertJsonPath('data.1.name', 'Germany')
            ->assertJsonPath('data.2.name', 'Austria')
        ;
    }

    public function testValidatesSortParameter(): void
    {
        $response = $this->getJson($this->baseUrl . '?sort=invalid');

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['sort'])
        ;
    }

    public function testValidatesDateRangeFilter(): void
    {
        $response = $this->getJson($this->baseUrl . '?filter[createdAt][from]=invalid');

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['filter.createdAt.from'])
        ;

        $response = $this->getJson($this->baseUrl . '?filter[createdAt][to]=2023-01-01&filter[createdAt][from]=2023-01-02');

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['filter.createdAt.to'])
        ;
    }
}
