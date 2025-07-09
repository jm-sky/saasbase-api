<?php

namespace Tests\Feature\GTU;

use App\Domain\Financial\DTOs\InvoiceLineDTO;
use App\Domain\Financial\DTOs\VatRateDTO;
use App\Domain\Financial\Enums\VatRateType;
use App\Domain\Financial\Services\GTUAssignmentService;
use Brick\Math\BigDecimal;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

/**
 * @internal
 *
 * @covers \App\Domain\Financial\Services\GTUAssignmentService
 */
#[CoversClass(GTUAssignmentService::class)]
class GTUAssignmentTest extends TestCase
{
    private GTUAssignmentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GTUAssignmentService();
    }

    public function testAutoAssignGtuCodesForHighValueItem()
    {
        // Create test invoice line with high value (>50,000 PLN)
        $line = new InvoiceLineDTO(
            id: 'test-line-1',
            description: 'Expensive item',
            quantity: BigDecimal::of(1),
            unitPrice: BigDecimal::of(60000),
            vatRate: new VatRateDTO(
                id: 'vat-1',
                name: '23%',
                rate: BigDecimal::of(0.23)->toFloat(),
                type: VatRateType::PERCENTAGE,
            ),
            totalNet: BigDecimal::of(60000),
            totalVat: BigDecimal::of(13800),
            totalGross: BigDecimal::of(73800),
            productId: null,
            gtuCodes: null
        );

        $result = $this->service->autoAssignGTUCodes($line);

        $this->assertContains('GTU_07', $result);
    }

    public function testAutoAssignGtuCodesFromProduct()
    {
        // This test would require database setup, so it's a placeholder
        $this->markTestSkipped('Requires database setup');
    }

    public function testAssignGtuCodeToInvoiceLine()
    {
        $line = new InvoiceLineDTO(
            id: 'test-line-1',
            description: 'Test item',
            quantity: BigDecimal::of(1),
            unitPrice: BigDecimal::of(100),
            vatRate: new VatRateDTO(
                id: 'vat-1',
                name: '23%',
                rate: BigDecimal::of(0.23)->toFloat(),
                type: VatRateType::PERCENTAGE,
            ),
            totalNet: BigDecimal::of(100),
            totalVat: BigDecimal::of(23),
            totalGross: BigDecimal::of(123),
            productId: null,
            gtuCodes: null
        );

        $updatedLine = $this->service->assignGTUCode($line, 'GTU_01');

        $this->assertTrue($updatedLine->hasGtuCode('GTU_01'));
        $this->assertContains('GTU_01', $updatedLine->getGtuCodes());
    }

    public function testRemoveGtuCodeFromInvoiceLine()
    {
        $line = new InvoiceLineDTO(
            id: 'test-line-1',
            description: 'Test item',
            quantity: BigDecimal::of(1),
            unitPrice: BigDecimal::of(100),
            vatRate: new VatRateDTO(
                id: 'vat-1',
                name: '23%',
                rate: BigDecimal::of(0.23)->toFloat(),
                type: VatRateType::PERCENTAGE,
            ),
            totalNet: BigDecimal::of(100),
            totalVat: BigDecimal::of(23),
            totalGross: BigDecimal::of(123),
            productId: null,
            gtuCodes: ['GTU_01', 'GTU_02']
        );

        $updatedLine = $this->service->removeGTUCode($line, 'GTU_01');

        $this->assertFalse($updatedLine->hasGtuCode('GTU_01'));
        $this->assertTrue($updatedLine->hasGtuCode('GTU_02'));
        $this->assertNotContains('GTU_01', $updatedLine->getGtuCodes());
    }

    public function testDetectGtuByAmount()
    {
        $highValueLine = new InvoiceLineDTO(
            id: 'test-line-1',
            description: 'High value item',
            quantity: BigDecimal::of(1),
            unitPrice: BigDecimal::of(60000),
            vatRate: new VatRateDTO(
                id: 'vat-1',
                name: '23%',
                rate: BigDecimal::of(0.23)->toFloat(),
                type: VatRateType::PERCENTAGE,
            ),
            totalNet: BigDecimal::of(60000),
            totalVat: BigDecimal::of(13800),
            totalGross: BigDecimal::of(73800),
            productId: null,
            gtuCodes: null
        );

        $result = $this->service->detectGTUByAmount($highValueLine);
        $this->assertContains('GTU_07', $result);

        $lowValueLine = new InvoiceLineDTO(
            id: 'test-line-2',
            description: 'Low value item',
            quantity: BigDecimal::of(1),
            unitPrice: BigDecimal::of(100),
            vatRate: new VatRateDTO(
                id: 'vat-1',
                name: '23%',
                rate: BigDecimal::of(0.23)->toFloat(),
                type: VatRateType::PERCENTAGE,
            ),
            totalNet: BigDecimal::of(100),
            totalVat: BigDecimal::of(23),
            totalGross: BigDecimal::of(123),
            productId: null,
            gtuCodes: null
        );

        $result = $this->service->detectGTUByAmount($lowValueLine);
        $this->assertEmpty($result);
    }

    public function testDetectGtuByKeywords()
    {
        $alcoholDescription = 'Piwo żywieckie 500ml';
        $result             = $this->service->detectGTUByKeywords($alcoholDescription);
        $this->assertContains('GTU_01', $result);

        $tobaccoDescription = 'Papierosy marlboro';
        $result             = $this->service->detectGTUByKeywords($tobaccoDescription);
        $this->assertContains('GTU_02', $result);

        $fuelDescription = 'Benzyna 95';
        $result          = $this->service->detectGTUByKeywords($fuelDescription);
        $this->assertContains('GTU_03', $result);

        $normalDescription = 'Zwykły produkt';
        $result            = $this->service->detectGTUByKeywords($normalDescription);
        $this->assertEmpty($result);
    }

    public function testInvoiceLineDtoGtuMethods()
    {
        $line = new InvoiceLineDTO(
            id: 'test-line-1',
            description: 'Test item',
            quantity: BigDecimal::of(1),
            unitPrice: BigDecimal::of(100),
            vatRate: new VatRateDTO(
                id: 'vat-1',
                name: '23%',
                rate: BigDecimal::of(0.23)->toFloat(),
                type: VatRateType::PERCENTAGE,
            ),
            totalNet: BigDecimal::of(100),
            totalVat: BigDecimal::of(23),
            totalGross: BigDecimal::of(123),
            productId: null,
            gtuCodes: ['GTU_01']
        );

        $this->assertTrue($line->hasGtuCode('GTU_01'));
        $this->assertFalse($line->hasGtuCode('GTU_02'));
        $this->assertEquals(['GTU_01'], $line->getGtuCodes());

        $updatedLine = $line->withGtuCode('GTU_02');
        $this->assertTrue($updatedLine->hasGtuCode('GTU_01'));
        $this->assertTrue($updatedLine->hasGtuCode('GTU_02'));

        $updatedLine = $line->withoutGtuCode('GTU_01');
        $this->assertFalse($updatedLine->hasGtuCode('GTU_01'));
        $this->assertEmpty($updatedLine->getGtuCodes());
    }
}
