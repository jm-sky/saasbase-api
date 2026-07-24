<?php

namespace App\Domain\Export\Exports;

use App\Domain\Common\Filters\AdvancedFilter;
use App\Domain\Common\Filters\ComboSearchFilter;
use App\Domain\Expense\Models\Expense;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\AllowedFilter;

class ExpensesExport extends BaseExport
{
    protected array $dateColumns = ['created_at', 'updated_at'];

    protected array $currencyColumns = ['price'];

    protected array $amountColumns = [];

    protected array $columns = [
        'id',
        'number',
        'type',
        'status',
        'approval_status',
        'issue_date',
        'total_net',
        'total_tax',
        'total_gross',
        'currency',
        'created_at',
        'updated_at',
    ];

    public function baseQuery(): Builder
    {
        return Expense::query();
    }

    protected function allowedFilters(): array
    {
        return [
            AllowedFilter::custom('search', new ComboSearchFilter(['number', 'buyer', 'seller', 'total_net', 'total_tax', 'total_gross', 'currency'])),
            AllowedFilter::custom('number', new AdvancedFilter),
            AllowedFilter::custom('total_net', new AdvancedFilter),
            AllowedFilter::custom('total_tax', new AdvancedFilter),
            AllowedFilter::custom('total_gross', new AdvancedFilter),
            AllowedFilter::custom('currency', new AdvancedFilter),
            AllowedFilter::custom('buyer', new AdvancedFilter),
            AllowedFilter::custom('seller', new AdvancedFilter),
            AllowedFilter::custom('createdAt', new AdvancedFilter, 'created_at'),
            AllowedFilter::custom('updatedAt', new AdvancedFilter, 'updated_at'),
        ];
    }

    protected function allowedIncludes(): array
    {
        return [];
    }
}
