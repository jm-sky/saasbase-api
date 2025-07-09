<?php

namespace App\Domain\Financial\Services;

use App\Domain\Financial\Models\PKWiUClassification;
use App\Domain\Products\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class PKWiUService
{
    // Core validation methods
    public function validateCode(string $code): bool
    {
        return $this->isValidCodeFormat($code) && $this->codeExists($code);
    }

    public function isValidCodeFormat(string $code): bool
    {
        return 1 === preg_match('/^[0-9]{2}\.[0-9]{2}\.[0-9]{2}\.[0-9]$/', $code);
    }

    public function codeExists(string $code): bool
    {
        return PKWiUClassification::where('code', $code)->exists();
    }

    // Search and discovery
    public function searchByName(string $query, int $limit = 50): Collection
    {
        return PKWiUClassification::active()
            ->where('name', 'LIKE', "%{$query}%")
            ->orWhere('description', 'LIKE', "%{$query}%")
            ->limit($limit)
            ->get()
        ;
    }

    public function searchByCode(string $codePrefix): Collection
    {
        return PKWiUClassification::active()
            ->where('code', 'LIKE', "{$codePrefix}%")
            ->orderBy('code')
            ->get()
        ;
    }

    public function getHierarchyTree(?string $parentCode = null): Collection
    {
        $query = PKWiUClassification::active()
            ->with('children')
        ;

        if ($parentCode) {
            $query->where('parent_code', $parentCode);
        } else {
            $query->rootCategories();
        }

        return $query->orderBy('code')->get();
    }

    public function getCodeSuggestions(string $partial): Collection
    {
        return PKWiUClassification::active()
            ->where('code', 'LIKE', "{$partial}%")
            ->orWhere('name', 'LIKE', "%{$partial}%")
            ->limit(10)
            ->get()
        ;
    }

    // Product integration
    public function assignPKWiUToProduct(string $productId, string $pkwiuCode): bool
    {
        if (!$this->validateCode($pkwiuCode)) {
            return false;
        }

        $product = Product::find($productId);

        if (!$product) {
            return false;
        }

        $product->update(['pkwiu_code' => $pkwiuCode]);

        return true;
    }

    public function bulkAssignPKWiUToProducts(array $assignments): int
    {
        $successCount = 0;

        foreach ($assignments as $assignment) {
            if ($this->assignPKWiUToProduct($assignment['product_id'], $assignment['pkwiu_code'])) {
                ++$successCount;
            }
        }

        return $successCount;
    }

    // Invoice JSON integration
    public function validateInvoiceBodyPKWiU(array $invoiceBody): array
    {
        $errors = [];

        foreach ($invoiceBody as $index => $item) {
            if (!isset($item['pkwiu_code'])) {
                $errors[] = "Item {$index}: PKWiU code is required";
                continue;
            }

            if (!$this->validateCode($item['pkwiu_code'])) {
                $errors[] = "Item {$index}: Invalid PKWiU code '{$item['pkwiu_code']}'";
            }
        }

        return $errors;
    }

    public function enrichInvoiceItemsWithPKWiU(array $invoiceItems): array
    {
        foreach ($invoiceItems as &$item) {
            if (!isset($item['pkwiu_code']) && isset($item['product_id'])) {
                $product = Product::find($item['product_id']);

                if ($product && $product->pkwiu_code) {
                    $item['pkwiu_code'] = $product->pkwiu_code;
                }
            }
        }

        return $invoiceItems;
    }

    public function extractPKWiUCodesFromInvoiceBody(array $invoiceBody): array
    {
        return collect($invoiceBody)
            ->pluck('pkwiu_code')
            ->filter()
            ->unique()
            ->values()
            ->toArray()
        ;
    }

    // Hierarchy navigation
    public function getFullPath(string $code): string
    {
        $classification = PKWiUClassification::find($code);

        return $classification ? $classification->getFullHierarchyPath() : '';
    }

    public function getParentChain(string $code): Collection
    {
        $classification = PKWiUClassification::find($code);

        return $classification ? $classification->getAncestors() : new Collection();
    }

    public function getLeafNodes(?string $parentCode = null): Collection
    {
        $query = PKWiUClassification::active()
            ->whereDoesntHave('children')
        ;

        if ($parentCode) {
            $query->where('parent_code', $parentCode);
        }

        return $query->get();
    }
}
