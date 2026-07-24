<?php

namespace Tests\Feature\Domain\Financial;

use App\Domain\Auth\Models\User;
use App\Domain\Financial\Controllers\PKWiUClassificationController;
use App\Domain\Financial\Models\PKWiUClassification;
use App\Domain\Products\Models\Product;
use App\Domain\Tenant\Models\Tenant;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;
use Tests\Traits\WithAuthenticatedUser;

/**
 * @internal
 */
#[CoversClass(PKWiUClassificationController::class)]
class PKWiUClassificationTest extends TestCase
{
    use WithAuthenticatedUser;

    protected User $user;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->tenant = Tenant::factory()->create();

        $this->authenticateUser($this->tenant, $this->user);
    }

    public function test_can_list_pkwiu_classifications(): void
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
            ]);
    }

    public function test_can_search_pkwiu_by_name(): void
    {
        PKWiUClassification::factory()->create([
            'name' => 'Usługi programowania',
            'code' => '62.01.11.0',
        ]);

        $response = $this->getJson('/api/v1/pkwiu/search?query=programowania');

        $response->assertOk()
            ->assertJsonFragment(['code' => '62.01.11.0']);
    }

    public function test_can_validate_pkwiu_code(): void
    {
        PKWiUClassification::factory()->create(['code' => '62.01.11.0']);

        $response = $this->postJson('/api/v1/pkwiu/validate', [
            'code' => '62.01.11.0',
        ]);

        $response->assertOk()
            ->assertJson(['valid' => true]);
    }

    public function test_can_get_hierarchy_tree(): void
    {
        $parent = PKWiUClassification::factory()->create([
            'code' => '62.00.00.0',
            'level' => 1,
            'parent_code' => null,
        ]);

        PKWiUClassification::factory()->create([
            'code' => '62.01.00.0',
            'parent_code' => '62.00.00.0',
            'level' => 2,
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
            ]);
    }

    public function test_can_validate_invoice_body_pkwiu(): void
    {
        PKWiUClassification::factory()->create(['code' => '62.01.11.0']);

        $invoiceBody = [
            [
                'name' => 'Software Development',
                'pkwiu_code' => '62.01.11.0',
                'quantity' => 1,
                'unit_price' => 1000,
            ],
        ];

        $response = $this->postJson('/api/v1/pkwiu/validate-invoice', [
            'invoice_body' => $invoiceBody,
        ]);

        $response->assertOk()
            ->assertJson(['valid' => true]);
    }

    public function test_can_get_single_pkwiu_classification(): void
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
            ]);
    }

    public function test_can_get_code_suggestions(): void
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
            ]);
    }

    public function test_product_can_have_pkwiu_code(): void
    {
        $classification = PKWiUClassification::factory()->create([
            'code' => '62.01.11.0',
        ]);

        $product = Tenant::bypassTenant($this->tenant->id, function () {
            return Product::factory()->create([
                'tenant_id' => $this->user->tenant_id,
                'pkwiu_code' => '62.01.11.0',
            ]);
        });

        $this->assertNotNull($product->pkwiuClassification);
        $this->assertEquals('62.01.11.0', $product->pkwiuClassification->code);
    }
}
