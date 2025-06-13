<?php

namespace App\Domain\Export\Exports;

use App\Domain\Expense\Models\Expense;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\AllowedFilter;

class ExpensesExport extends BaseExport
{
    protected array $dateColumns = ['created_at', 'updated_at'];

    protected array $currencyColumns = ['price'];

    protected array $amountColumns = [];

    public function baseQuery(): Builder
    {
        return Expense::query();
    }

    protected function allowedFilters(): array
    {
        return [
            AllowedFilter::custom('search', new \App\Domain\Common\Filters\ComboSearchFilter(['number', 'buyer', 'seller', 'total_net', 'total_tax', 'total_gross', 'currency'])),
            AllowedFilter::custom('number', new \App\Domain\Common\Filters\AdvancedFilter()),
            AllowedFilter::custom('total_net', new \App\Domain\Common\Filters\AdvancedFilter()),
            AllowedFilter::custom('total_tax', new \App\Domain\Common\Filters\AdvancedFilter()),
            AllowedFilter::custom('total_gross', new \App\Domain\Common\Filters\AdvancedFilter()),
            AllowedFilter::custom('currency', new \App\Domain\Common\Filters\AdvancedFilter()),
            AllowedFilter::custom('buyer', new \App\Domain\Common\Filters\AdvancedFilter()),
            AllowedFilter::custom('seller', new \App\Domain\Common\Filters\AdvancedFilter()),
            AllowedFilter::custom('createdAt', new \App\Domain\Common\Filters\AdvancedFilter(), 'created_at'),
            AllowedFilter::custom('updatedAt', new \App\Domain\Common\Filters\AdvancedFilter(), 'updated_at'),
        ];
    }

    protected function allowedIncludes(): array
    {
        return [];
    }
}
