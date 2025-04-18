<?php

namespace Tests\Feature\Domain\Common;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CountryApiTest extends TestCase
{
    use RefreshDatabase;

    private string $baseUrl = '/api/v1/countries';
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_can_list_countries(): void
    {
        $countries = Country::factory()->count(3)->create();

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
                    ]
                ]
            ]);
    }

    public function test_can_show_country(): void
    {
        $country = Country::factory()->create();

        $response = $this->getJson($this->baseUrl . '/' . $country->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
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
            ])
            ->assertJson([
                'id' => $country->id,
                'name' => $country->name,
                'code' => $country->code
            ]);
    }

    public function test_returns_404_for_nonexistent_country(): void
    {
        $response = $this->getJson($this->baseUrl . '/nonexistent-id');

        $response->assertStatus(404);
    }
}
