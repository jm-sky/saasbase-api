<?php

namespace App\Domain\Export\Exports;

use App\Domain\Common\Filters\AdvancedFilter;
use App\Domain\Common\Filters\ComboSearchFilter;
use App\Domain\Products\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\AllowedFilter;

class ProductsExport extends BaseExport
{
    protected array $dateColumns = ['created_at', 'updated_at'];

    protected array $currencyColumns = ['price'];

    protected array $amountColumns = [];

    protected array $columns = [
        'id',
        'name',
        'description',
        'type.value',
        'unit.name',
        'price_net',
        'vat_rate.name',
        'tags.name',
        'created_at',
        'updated_at',
    ];

    public function baseQuery(): Builder
    {
        return Product::query()->with(['unit', 'vatRate', 'tags']);
    }

    protected function allowedFilters(): array
    {
        return [
            AllowedFilter::custom('search', new ComboSearchFilter(['name', 'description'])),
            AllowedFilter::custom('name', new AdvancedFilter),
            AllowedFilter::custom('description', new AdvancedFilter),
            AllowedFilter::custom('unitId', new AdvancedFilter, 'unit_id'),
            AllowedFilter::custom('vatRateId', new AdvancedFilter, 'vat_rate_id'),
            AllowedFilter::custom('createdAt', new AdvancedFilter, 'created_at'),
            AllowedFilter::custom('updatedAt', new AdvancedFilter, 'updated_at'),
        ];
    }

    protected function allowedIncludes(): array
    {
        return ['unit', 'vatRate', 'tags'];
    }
}
