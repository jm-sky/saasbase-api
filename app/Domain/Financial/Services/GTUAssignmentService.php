<?php

namespace App\Domain\Financial\Services;

use App\Domain\Auth\Models\User;
use App\Domain\Financial\DTOs\InvoiceLineDTO;
use App\Domain\Financial\Enums\GTUCodeEnum;
use App\Domain\Financial\Models\GtuCode;
use App\Domain\Invoice\Models\Invoice;
use App\Domain\Products\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class GTUAssignmentService
{
    public function autoAssignGTUCodes(InvoiceLineDTO $line, ?Product $product = null): array
    {
        $gtuCodes = [];

        // If product is provided, use its GTU codes
        if ($product) {
            $gtuCodes = array_merge($gtuCodes, $product->getGtuCodes());
        }

        // Check for GTU_07 - high value items (>= 50,000 PLN)
        if ($line->totalGross->isGreaterThanOrEqualTo(GTUCodeEnum::THRESHOLD_50_000)) {
            $gtuCodes[] = GTUCodeEnum::GTU_07->value;
        }

        // Remove duplicates and return
        return array_unique($gtuCodes);
    }

    public function assignGTUCode(InvoiceLineDTO $line, string $gtuCode): InvoiceLineDTO
    {
        return $line->withGtuCode($gtuCode);
    }

    public function removeGTUCode(InvoiceLineDTO $line, string $gtuCode): InvoiceLineDTO
    {
        return $line->withoutGtuCode($gtuCode);
    }

    public function validateGTUAssignment(InvoiceLineDTO $line, string $gtuCode): bool
    {
        $gtuCodeModel = GtuCode::query()
            ->where('code', $gtuCode)
            ->where('is_active', true)
            ->first()
        ;

        if (!$gtuCodeModel) {
            return false;
        }

        // Check if GTU code is effective
        if (!$this->isGTUCodeEffective($gtuCodeModel)) {
            return false;
        }

        // Validate amount threshold for GTU_07
        if (GTUCodeEnum::GTU_07->value === $gtuCode) {
            return $this->validateAmountThreshold($line, $gtuCodeModel);
        }

        return true;
    }

    public function validateAmountThreshold(InvoiceLineDTO $line, GtuCode $gtuCode): bool
    {
        if (!$gtuCode->amount_threshold_pln) {
            return true;
        }

        return $line->totalGross->isGreaterThanOrEqualTo($gtuCode->amount_threshold_pln);
    }

    public function validateApplicabilityConditions(InvoiceLineDTO $line, GtuCode $gtuCode): bool
    {
        // This can be extended based on specific business rules
        return true;
    }

    public function assignGTUToProduct(Product $product, string $gtuCode, User $user): Product
    {
        $product->addGtuCode($gtuCode);
        $product->save();

        return $product;
    }

    public function getProductGTUCodes(Product $product): array
    {
        return $product->getGtuCodes();
    }

    public function bulkAssignByCategory(string $category, string $gtuCode, User $user): int
    {
        // This would need to be implemented based on your product categorization
        // For now, return 0 as placeholder
        return 0;
    }

    public function processInvoiceGTUAssignments(Invoice $invoice): Invoice
    {
        $body         = $invoice->body;
        $updatedLines = [];

        foreach ($body->lines as $line) {
            $product = null;

            if ($line->productId) {
                $product = Product::find($line->productId);
            }

            // Auto-assign GTU codes
            $autoAssignedCodes = $this->autoAssignGTUCodes($line, $product);

            // Merge with existing codes
            $existingCodes = $line->getGtuCodes();
            $allCodes      = array_unique(array_merge($existingCodes, $autoAssignedCodes));

            // Create updated line with GTU codes
            $updatedLine = new InvoiceLineDTO(
                id: $line->id,
                description: $line->description,
                quantity: $line->quantity,
                unitPrice: $line->unitPrice,
                vatRate: $line->vatRate,
                totalNet: $line->totalNet,
                totalVat: $line->totalVat,
                totalGross: $line->totalGross,
                productId: $line->productId,
                gtuCodes: $allCodes,
            );

            $updatedLines[] = $updatedLine;
        }

        // Update invoice body with new lines
        $updatedBody = new \App\Domain\Financial\DTOs\InvoiceBodyDTO(
            lines: $updatedLines,
            vatSummary: $body->vatSummary,
            exchange: $body->exchange,
            description: $body->description,
        );

        $invoice->body = $updatedBody;
        $invoice->save();

        return $invoice;
    }

    public function validateInvoiceGTUCompliance(Invoice $invoice): bool
    {
        foreach ($invoice->body->lines as $line) {
            foreach ($line->getGtuCodes() as $gtuCode) {
                if (!$this->validateGTUAssignment($line, $gtuCode)) {
                    return false;
                }
            }
        }

        return true;
    }

    public function detectGTUByProductCategory(Product $product): array
    {
        return [];

        // This would be implemented based on your product categorization
        // For now, return empty array as placeholder
    }

    public function detectGTUByAmount(InvoiceLineDTO $line): array
    {
        $gtuCodes = [];

        // GTU_07 for high value items
        if ($line->totalGross->isGreaterThanOrEqualTo(50000)) {
            $gtuCodes[] = 'GTU_07';
        }

        return $gtuCodes;
    }

    public function detectGTUByKeywords(string $description): array
    {
        $gtuCodes    = [];
        $description = strtolower($description);

        // Simple keyword detection - this can be enhanced
        $keywords = [
            'GTU_01' => ['alkohol', 'piwo', 'wino', 'wódka', 'whisky'],
            'GTU_02' => ['tytoń', 'papieros', 'e-papieros'],
            'GTU_03' => ['paliwo', 'benzyna', 'diesel', 'olej napędowy'],
            'GTU_04' => ['samochód', 'pojazd', 'auto', 'motocykl'],
            'GTU_05' => ['elektronika', 'telefon', 'laptop', 'komputer'],
            'GTU_06' => ['części samochodowe', 'opony', 'akumulator'],
            'GTU_08' => ['złoto', 'srebro', 'platyna', 'metal szlachetny'],
            'GTU_09' => ['lek', 'lekarstwo', 'medyczny', 'farmaceutyczny'],
            'GTU_10' => ['budynek', 'mieszkanie', 'dom', 'grunt'],
            'GTU_11' => ['gaz', 'gazowy'],
            'GTU_12' => ['energia', 'elektryczny', 'prąd'],
            'GTU_13' => ['telekomunikacja', 'internet', 'telefon'],
        ];

        foreach ($keywords as $gtuCode => $keywordList) {
            foreach ($keywordList as $keyword) {
                if (str_contains($description, $keyword)) {
                    $gtuCodes[] = $gtuCode;
                    break;
                }
            }
        }

        return array_unique($gtuCodes);
    }

    public function getGTUStatistics(Carbon $from, Carbon $to): array
    {
        // This would be implemented to return statistics about GTU usage
        // For now, return empty array as placeholder
        return [];
    }

    public function getUnassignedHighValueItems(Carbon $from, Carbon $to): Collection
    {
        // This would be implemented to find high-value items without GTU codes
        // For now, return empty collection as placeholder
        return collect();
    }

    private function isGTUCodeEffective(GtuCode $gtuCode): bool
    {
        $now = Carbon::now();

        if ($gtuCode->effective_from && $now->isBefore($gtuCode->effective_from)) {
            return false;
        }

        if ($gtuCode->effective_to && $now->isAfter($gtuCode->effective_to)) {
            return false;
        }

        return true;
    }
}
