<?php

namespace Tests\Feature\Domain\Common\Filters;

use App\Domain\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversNothing;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
#[CoversNothing]
class AdvancedFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->markTestSkipped('Skipping this test for now');

        User::factory()->create([
            'first_name' => 'John',
            'last_name'  => 'Doe',
            'email'      => 'john@example.com',
            'status'     => null,
            'birth_date' => '1980-01-01',
            'is_admin'   => false,
        ]);

        User::factory()->create([
            'first_name' => 'Jane',
            'last_name'  => 'Smith',
            'email'      => '',
            'status'     => 'active',
            'birth_date' => '1990-05-05',
            'is_admin'   => true,
        ]);
    }

    public function testNullOperatorFiltersCorrectly()
    {
        $response = $this->json('GET', '/api/users', [
            'filter' => [
                'status' => ['null' => 1],
            ],
        ]);

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('John', $response->json('data')[0]['first_name']);
    }

    public function testNullishOperatorFiltersCorrectly()
    {
        $response = $this->json('GET', '/api/users', [
            'filter' => [
                'email' => ['nullish' => 1],
            ],
        ]);

        $response->assertOk();
        $this->assertCount(2, $response->json('data'));
    }

    public function testStartsWithOperator()
    {
        $response = $this->json('GET', '/api/users', [
            'filter' => [
                'first_name' => ['startsWith' => 'Jane'],
            ],
        ]);

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('Jane', $response->json('data')[0]['first_name']);
    }

    public function testBetweenOperator()
    {
        User::factory()->create(['birth_date' => '2000-01-01']);
        User::factory()->create(['birth_date' => '2005-05-05']);

        $response = $this->json('GET', '/api/users', [
            'filter' => [
                'birth_date' => ['between' => '1990-01-01,2005-12-31'],
            ],
        ]);

        $response->assertOk();
        $this->assertGreaterThanOrEqual(2, count($response->json('data')));
    }

    public function testDefaultLikeOperatorForStrings()
    {
        $response = $this->json('GET', '/api/users', [
            'filter' => [
                'last_name' => 'Doe',
            ],
        ]);

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('John', $response->json('data')[0]['first_name']);
    }

    public function testEqOperatorForNumbers()
    {
        $id = User::first()->id;

        $response = $this->json('GET', '/api/users', [
            'filter' => [
                'id' => ['eq' => $id],
            ],
        ]);

        $response->assertOk();
        $this->assertEquals('John', $response->json('data')[0]['first_name']);
    }
}
