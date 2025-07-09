<?php

namespace Tests\Unit\Domain\Financial\Services;

use App\Domain\Financial\Models\PKWiUClassification;
use App\Domain\Financial\Services\PKWiUService;
use App\Domain\Products\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

/**
 * @internal
 */
#[CoversClass(PKWiUService::class)]
class PKWiUServiceTest extends TestCase
{
    private PKWiUService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PKWiUService();
    }

    public function testValidatesPkwiuCodeFormat(): void
    {
        $this->assertTrue($this->service->isValidCodeFormat('62.01.11.0'));
        $this->assertFalse($this->service->isValidCodeFormat('62.01.11'));
        $this->assertFalse($this->service->isValidCodeFormat('62.01.11.00'));
        $this->assertFalse($this->service->isValidCodeFormat('invalid'));
    }

    public function testChecksIfCodeExists(): void
    {
        PKWiUClassification::factory()->create(['code' => '62.01.11.0']);

        $this->assertTrue($this->service->codeExists('62.01.11.0'));
        $this->assertFalse($this->service->codeExists('99.99.99.9'));
    }

    public function testValidatesCompletePkwiuCode(): void
    {
        PKWiUClassification::factory()->create(['code' => '62.01.11.0']);

        $this->assertTrue($this->service->validateCode('62.01.11.0'));
        $this->assertFalse($this->service->validateCode('invalid'));
        $this->assertFalse($this->service->validateCode('99.99.99.9'));
    }

    public function testSearchesByName(): void
    {
        PKWiUClassification::factory()->create([
            'name' => 'Usługi programowania komputerowego',
            'code' => '62.01.11.0',
        ]);

        /** @var Collection<PKWiUClassification> $results */
        $results = $this->service->searchByName('programowania');

        $this->assertCount(1, $results);
        $this->assertEquals('62.01.11.0', $results->first()->code); // @phpstan-ignore-line
    }

    public function testSearchesByCodePrefix(): void
    {
        PKWiUClassification::factory()->create(['code' => '62.01.11.0']);
        PKWiUClassification::factory()->create(['code' => '62.01.12.0']);
        PKWiUClassification::factory()->create(['code' => '69.10.11.0']);

        $results = $this->service->searchByCode('62.01');

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('code', '62.01.11.0'));
        $this->assertTrue($results->contains('code', '62.01.12.0'));
    }

    public function testGetsHierarchyTree(): void
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

        $tree = $this->service->getHierarchyTree();

        $this->assertCount(1, $tree);
        $this->assertEquals('62.00.00.0', $tree->first()->code); // @phpstan-ignore-line
    }

    public function testGetsCodeSuggestions(): void
    {
        PKWiUClassification::factory()->create([
            'code' => '62.01.11.0',
            'name' => 'Usługi programowania',
        ]);

        $suggestions = $this->service->getCodeSuggestions('62.01');

        $this->assertCount(1, $suggestions);
        $this->assertEquals('62.01.11.0', $suggestions->first()->code); // @phpstan-ignore-line
    }

    public function testAssignsPkwiuToProduct(): void
    {
        PKWiUClassification::factory()->create(['code' => '62.01.11.0']);
        $product = Product::factory()->create();

        $result = $this->service->assignPKWiUToProduct($product->id, '62.01.11.0');

        $this->assertTrue($result);
        $product->refresh();
        $this->assertEquals('62.01.11.0', $product->pkwiu_code);
    }

    public function testCannotAssignInvalidPkwiuToProduct(): void
    {
        $product = Product::factory()->create();

        $result = $this->service->assignPKWiUToProduct($product->id, 'invalid');

        $this->assertFalse($result);
    }

    public function testBulkAssignsPkwiuToProducts(): void
    {
        PKWiUClassification::factory()->create(['code' => '62.01.11.0']);
        PKWiUClassification::factory()->create(['code' => '62.01.12.0']);
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        $assignments = [
            ['product_id' => $product1->id, 'pkwiu_code' => '62.01.11.0'],
            ['product_id' => $product2->id, 'pkwiu_code' => '62.01.12.0'],
        ];

        $successCount = $this->service->bulkAssignPKWiUToProducts($assignments);

        $this->assertEquals(2, $successCount);
    }

    public function testValidatesInvoiceBodyPkwiu(): void
    {
        PKWiUClassification::factory()->create(['code' => '62.01.11.0']);

        $invoiceBody = [
            ['pkwiu_code' => '62.01.11.0'],
            ['pkwiu_code' => 'invalid'],
        ];

        $errors = $this->service->validateInvoiceBodyPKWiU($invoiceBody);

        $this->assertCount(1, $errors);
        $this->assertStringContainsString('Invalid PKWiU code', $errors[0]);
    }

    public function testEnrichesInvoiceItemsWithPkwiu(): void
    {
        PKWiUClassification::factory()->create(['code' => '62.01.11.0']);
        $product = Product::factory()->create(['pkwiu_code' => '62.01.11.0']);

        $invoiceItems = [
            ['product_id' => $product->id, 'name' => 'Software'],
        ];

        $enriched = $this->service->enrichInvoiceItemsWithPKWiU($invoiceItems);

        $this->assertEquals('62.01.11.0', $enriched[0]['pkwiu_code']);
    }

    public function testExtractsPkwiuCodesFromInvoiceBody(): void
    {
        $invoiceBody = [
            ['pkwiu_code' => '62.01.11.0'],
            ['pkwiu_code' => '62.01.12.0'],
            ['pkwiu_code' => '62.01.11.0'], // duplicate
        ];

        $codes = $this->service->extractPKWiUCodesFromInvoiceBody($invoiceBody);

        $this->assertCount(2, $codes);
        $this->assertContains('62.01.11.0', $codes);
        $this->assertContains('62.01.12.0', $codes);
    }

    public function testGetsFullHierarchyPath(): void
    {
        PKWiUClassification::factory()->create([
            'code' => '62.01.11.0',
            'name' => 'Test Classification',
        ]);

        $path = $this->service->getFullPath('62.01.11.0');

        $this->assertEquals('Test Classification', $path);
    }

    public function testGetsParentChain(): void
    {
        $parent = PKWiUClassification::factory()->create([
            'code'        => '62.00.00.0',
            'level'       => 1,
            'parent_code' => null,
        ]);

        $child = PKWiUClassification::factory()->create([
            'code'        => '62.01.00.0',
            'parent_code' => '62.00.00.0',
            'level'       => 2,
        ]);

        $chain = $this->service->getParentChain('62.01.00.0');

        $this->assertCount(1, $chain);
        $this->assertEquals('62.00.00.0', $chain->first()->code); // @phpstan-ignore-line
    }

    public function testGetsLeafNodes(): void
    {
        $parent = PKWiUClassification::factory()->create([
            'code'        => '62.00.00.0',
            'level'       => 1,
            'parent_code' => null,
        ]);

        $leaf = PKWiUClassification::factory()->create([
            'code'        => '62.01.11.0',
            'level'       => 4,
            'parent_code' => '62.00.00.0',
        ]);

        $leafNodes = $this->service->getLeafNodes();

        $this->assertCount(1, $leafNodes);
        $this->assertEquals('62.01.11.0', $leafNodes->first()->code); // @phpstan-ignore-line
    }
}
