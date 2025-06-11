<?php

namespace App\Domain\Export\Exports;

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
            AllowedFilter::custom('search', new \App\Domain\Common\Filters\ComboSearchFilter(['name', 'vatId', 'taxId', 'regon', 'email', 'phone', 'description'])),
            AllowedFilter::custom('id', new \App\Domain\Common\Filters\AdvancedFilter()),
            AllowedFilter::custom('name', new \App\Domain\Common\Filters\AdvancedFilter()),
            AllowedFilter::custom('taxId', new \App\Domain\Common\Filters\AdvancedFilter(), 'tax_id'),
            AllowedFilter::custom('vatId', new \App\Domain\Common\Filters\AdvancedFilter(), 'vat_id'),
            AllowedFilter::custom('regon', new \App\Domain\Common\Filters\AdvancedFilter(), 'regon'),
            AllowedFilter::custom('email', new \App\Domain\Common\Filters\AdvancedFilter()),
            AllowedFilter::custom('phone', new \App\Domain\Common\Filters\AdvancedFilter()),
            AllowedFilter::custom('website', new \App\Domain\Common\Filters\AdvancedFilter()),
            AllowedFilter::custom('country', new \App\Domain\Common\Filters\AdvancedFilter()),
            AllowedFilter::custom('description', new \App\Domain\Common\Filters\AdvancedFilter()),
            AllowedFilter::custom('isActive', new \App\Domain\Common\Filters\AdvancedFilter(['is_active' => 'boolean']), 'is_active'),
            AllowedFilter::custom('createdAt', new \App\Domain\Common\Filters\AdvancedFilter(), 'created_at'),
            AllowedFilter::custom('updatedAt', new \App\Domain\Common\Filters\AdvancedFilter(), 'updated_at'),
        ];
    }

    protected function allowedIncludes(): array
    {
        return ['tags', 'bankAccounts', 'addresses', 'contacts'];
    }
}
