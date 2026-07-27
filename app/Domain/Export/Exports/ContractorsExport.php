<?php

namespace App\Domain\Export\Exports;

use App\Domain\Common\Filters\AdvancedFilter;
use App\Domain\Common\Filters\ComboSearchFilter;
use App\Domain\Contractors\Models\Contractor;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\AllowedFilter;

class ContractorsExport extends BaseExport
{
    protected array $dateColumns = ['created_at', 'updated_at'];

    protected array $currencyColumns = [];

    protected array $amountColumns = [];

    protected array $columns = [
        'id',
        'name',
        'vat_id',
        'tax_id',
        'regon',
        'email',
        'phone',
        'website',
        'country',
        'description',
        'is_active',
        'created_at',
        'updated_at',
    ];

    public function baseQuery(): Builder
    {
        return Contractor::query()->with(['tags']);
    }

    protected function allowedFilters(): array
    {
        return [
            AllowedFilter::custom('search', new ComboSearchFilter(['name', 'vatId', 'taxId', 'regon', 'email', 'phone', 'description'])),
            AllowedFilter::custom('id', new AdvancedFilter),
            AllowedFilter::custom('name', new AdvancedFilter),
            AllowedFilter::custom('taxId', new AdvancedFilter, 'tax_id'),
            AllowedFilter::custom('vatId', new AdvancedFilter, 'vat_id'),
            AllowedFilter::custom('regon', new AdvancedFilter, 'regon'),
            AllowedFilter::custom('email', new AdvancedFilter),
            AllowedFilter::custom('phone', new AdvancedFilter),
            AllowedFilter::custom('website', new AdvancedFilter),
            AllowedFilter::custom('country', new AdvancedFilter),
            AllowedFilter::custom('description', new AdvancedFilter),
            AllowedFilter::custom('isActive', new AdvancedFilter(['is_active' => 'boolean']), 'is_active'),
            AllowedFilter::custom('createdAt', new AdvancedFilter, 'created_at'),
            AllowedFilter::custom('updatedAt', new AdvancedFilter, 'updated_at'),
        ];
    }

    protected function allowedIncludes(): array
    {
        return ['tags', 'bankAccounts', 'addresses', 'contacts'];
    }
}
