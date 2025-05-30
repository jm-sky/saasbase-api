<?php

namespace Tests\Feature\Domain\Common\Filters;

use App\Domain\Contractors\Models\Contractor;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversNothing;
use Tests\TestCase;
use Tests\Traits\WithAuthenticatedUser;

/**
 * @internal
 *
 * @coversNothing
 */
#[CoversNothing]
class AdvancedFilterTest extends TestCase
{
    use RefreshDatabase;
    use WithAuthenticatedUser;

    protected Tenant $tenant;

    protected string $baseUrl = '/api/v1/contractors';

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create();

        $this->authenticateUser($this->tenant);

        Tenant::bypassTenant($this->tenant->id, function () {
            Contractor::factory()->create([
                'name'       => 'John Doe Company',
                'email'      => null,
                'website'    => null,
                'is_buyer'   => true,
                'created_at' => '2000-01-01',
            ]);

            Contractor::factory()->create([
                'name'       => 'Jane Smith Company',
                'email'      => '',
                'website'    => 'https://jane-smith-company.com',
                'is_buyer'   => false,
                'created_at' => '2005-05-05',
            ]);
        });
    }

    public function testNullOperatorFiltersCorrectly()
    {
        $response = $this->json('GET', $this->baseUrl, [
            'filter' => [
                'website' => ['null' => true],
            ],
        ]);

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('John Doe Company', $response->json('data')[0]['name']);
    }

    public function testNullishOperatorFiltersCorrectly()
    {
        $response = $this->json('GET', $this->baseUrl, [
            'filter' => [
                'email' => ['nullish' => true],
            ],
        ]);

        $response->assertOk();
        $this->assertCount(2, $response->json('data'));
    }

    public function testStartsWithOperator()
    {
        $response = $this->json('GET', $this->baseUrl, [
            'filter' => [
                'name' => ['startsWith' => 'Jane'],
            ],
        ]);

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('Jane Smith Company', $response->json('data')[0]['name']);
    }

    public function testBetweenOperator()
    {
        $response = $this->json('GET', $this->baseUrl, [
            'filter' => [
                'createdAt' => ['between' => '1990-01-01,2005-12-31'],
            ],
        ]);

        $response->assertOk();
        $this->assertGreaterThanOrEqual(2, count($response->json('data')));
    }

    public function testDefaultLikeOperatorForStrings()
    {
        $response = $this->json('GET', $this->baseUrl, [
            'filter' => [
                'name' => 'Doe',
            ],
        ]);

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('John Doe Company', $response->json('data')[0]['name']);
    }

    public function testEqOperatorForNumbers()
    {
        $id = Contractor::forTenant($this->tenant->id)->first()->id;

        $response = $this->json('GET', $this->baseUrl, [
            'filter' => [
                'id' => ['eq' => $id],
            ],
        ]);

        $response->assertOk();

        $this->assertEquals('John Doe Company', $response->json('data')[0]['name']);
    }
}
