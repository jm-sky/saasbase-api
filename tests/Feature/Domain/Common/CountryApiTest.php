<?php

namespace Tests\Feature\Domain\Common;

use App\Domain\Common\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CountryApiTest extends TestCase
{
    use RefreshDatabase;

    private string $baseUrl = '/api/v1/countries';

    public function test_can_list_countries(): void
    {
        $countries = Country::factory()->count(3)->create();

        $response = $this->getJson($this->baseUrl);

        $response->assertStatus(200)
            ->assertJsonCount(3)
            ->assertJsonStructure([
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
                    'deletedAt'
                ]
            ]);
    }

    public function test_can_create_country(): void
    {
        $countryData = [
            'name' => 'Test Country',
            'code' => 'TC',
            'code3' => 'TCY',
            'numeric_code' => '999',
            'phone_code' => '123',
            'capital' => 'Test City',
            'currency' => 'Test Currency',
            'currency_code' => 'TCR',
            'currency_symbol' => '$',
            'tld' => '.tc',
            'native' => 'Test Native',
            'region' => 'Test Region',
            'subregion' => 'Test Subregion',
            'emoji' => '🏳️',
            'emojiU' => 'U+1F3F3'
        ];

        $response = $this->postJson($this->baseUrl, $countryData);

        $response->assertStatus(201)
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
                'deletedAt'
            ]);

        $this->assertDatabaseHas('countries', $countryData);
    }

    public function test_cannot_create_country_with_duplicate_code(): void
    {
        $existingCountry = Country::factory()->create();

        $countryData = [
            'name' => 'Test Country',
            'code' => $existingCountry->code,
            'code3' => 'TCY',
            'numeric_code' => '999',
            'phone_code' => '123'
        ];

        $response = $this->postJson($this->baseUrl, $countryData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
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
                'deletedAt'
            ])
            ->assertJson([
                'id' => $country->id,
                'name' => $country->name,
                'code' => $country->code
            ]);
    }

    public function test_can_update_country(): void
    {
        $country = Country::factory()->create();
        $updateData = [
            'name' => 'Updated Country',
            'code' => 'UC',
            'code3' => 'UPC',
            'numeric_code' => '998',
            'phone_code' => '321'
        ];

        $response = $this->putJson($this->baseUrl . '/' . $country->id, $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'name' => $updateData['name'],
                'code' => $updateData['code'],
                'code3' => $updateData['code3']
            ]);

        $this->assertDatabaseHas('countries', $updateData);
    }

    public function test_can_delete_country(): void
    {
        $country = Country::factory()->create();

        $response = $this->deleteJson($this->baseUrl . '/' . $country->id);

        $response->assertStatus(204);
        $this->assertSoftDeleted('countries', ['id' => $country->id]);
    }

    public function test_returns_404_for_nonexistent_country(): void
    {
        $response = $this->getJson($this->baseUrl . '/nonexistent-id');

        $response->assertStatus(404);
    }
}
