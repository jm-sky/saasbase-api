<?php

namespace Tests\Feature\Domain\Financial;

use App\Domain\Financial\Models\PKWiUClassification;
use App\Domain\Products\Models\Product;
use Tests\TestCase;
use Tests\WithAuthenticatedUser;

/**
 * @internal
 *
 * @coversNothing
 */
class PKWiUClassificationTest extends TestCase
{
    use WithAuthenticatedUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAuthenticatedUser();
    }

    public function testCanListPkwiuClassifications(): void
    {
        PKWiUClassification::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/pkwiu');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'code',
                        'name',
                        'level',
                        'hierarchyPath',
                    ],
                ],
            ])
        ;
    }

    public function testCanSearchPkwiuByName(): void
    {
        PKWiUClassification::factory()->create([
            'name' => 'Usługi programowania',
            'code' => '62.01.11.0',
        ]);

        $response = $this->getJson('/api/v1/pkwiu/search?query=programowania');

        $response->assertOk()
            ->assertJsonFragment(['code' => '62.01.11.0'])
        ;
    }

    public function testCanValidatePkwiuCode(): void
    {
        PKWiUClassification::factory()->create(['code' => '62.01.11.0']);

        $response = $this->postJson('/api/v1/pkwiu/validate', [
            'code' => '62.01.11.0',
        ]);

        $response->assertOk()
            ->assertJson(['valid' => true])
        ;
    }

    public function testCanGetHierarchyTree(): void
    {
        $parent = PKWiUClassification::factory()->create([
            'code'        => '62.00.00.0',
            'level'       => 1,
            'parent_code' => null,
        ]);

        PKWiUClassification::factory()->create([
            'code'        => '62.01.00.0',
            'parent_code' => '62.00.00.0',
            'level'       => 2,
        ]);

        $response = $this->getJson('/api/v1/pkwiu/tree');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'code',
                        'children',
                    ],
                ],
            ])
        ;
    }

    public function testCanValidateInvoiceBodyPkwiu(): void
    {
        PKWiUClassification::factory()->create(['code' => '62.01.11.0']);

        $invoiceBody = [
            [
                'name'       => 'Software Development',
                'pkwiu_code' => '62.01.11.0',
                'quantity'   => 1,
                'unit_price' => 1000,
            ],
        ];

        $response = $this->postJson('/api/v1/pkwiu/validate-invoice', [
            'invoice_body' => $invoiceBody,
        ]);

        $response->assertOk()
            ->assertJson(['valid' => true])
        ;
    }

    public function testCanGetSinglePkwiuClassification(): void
    {
        $classification = PKWiUClassification::factory()->create([
            'code' => '62.01.11.0',
            'name' => 'Usługi programowania aplikacji internetowych',
        ]);

        $response = $this->getJson('/api/v1/pkwiu/62.01.11.0');

        $response->assertOk()
            ->assertJsonFragment([
                'code' => '62.01.11.0',
                'name' => 'Usługi programowania aplikacji internetowych',
            ])
        ;
    }

    public function testCanGetCodeSuggestions(): void
    {
        PKWiUClassification::factory()->create([
            'code' => '62.01.11.0',
            'name' => 'Usługi programowania',
        ]);

        $response = $this->getJson('/api/v1/pkwiu/suggest?partial=62.01');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'code',
                        'name',
                    ],
                ],
            ])
        ;
    }

    public function testProductCanHavePkwiuCode(): void
    {
        $classification = PKWiUClassification::factory()->create([
            'code' => '62.01.11.0',
        ]);

        $product = Product::factory()->create([
            'tenant_id'  => $this->user->tenant_id,
            'pkwiu_code' => '62.01.11.0',
        ]);

        $this->assertNotNull($product->pkwiuClassification);
        $this->assertEquals('62.01.11.0', $product->pkwiuClassification->code);
    }
}
