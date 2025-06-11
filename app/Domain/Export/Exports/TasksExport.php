<?php

namespace App\Domain\Export\Exports;

use App\Domain\Projects\Models\Task;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\AllowedFilter;

class TasksExport extends BaseExport
{
    protected array $dateColumns = ['due_date'];

    protected array $dateTimeColumns = ['created_at'];

    protected array $currencyColumns = ['budget'];

    protected array $amountColumns = ['estimated_hours'];

    public function baseQuery(): Builder
    {
        return Task::query()->with(['assignee', 'project']);
    }

    protected function allowedFilters(): array
    {
        return [
            AllowedFilter::exact('project_id'),
            AllowedFilter::partial('title'),
        ];
    }

    protected function allowedIncludes(): array
    {
        return ['assignee', 'project'];
    }
}
