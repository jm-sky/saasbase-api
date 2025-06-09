# Excel Export for Laravel SaaSBase – Task Description for AI Agent (with PHP examples)

## Goal
Implement a reusable Excel export system for a SaaS application built with Laravel. The export should:
- Use Spatie Query Builder for filtering
- Allow selecting export columns (all, visible only, or user-defined)
- Apply proper data formatting (dates, datetime, currency, amount)
- Format header rows (e.g., bold styling)
- Support abstraction via a base `BaseExport` class

## Technologies:
- Laravel
- maatwebsite/excel (Laravel Excel)
- Spatie Laravel Query Builder

## Structure Overview

### Abstract Export: `BaseExport.php`

```php
use Maatwebsite\Excel\Concerns\{
    FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Support\Str;

abstract class BaseExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected array $filters = [];
    protected array $columns = [];
    protected array $formatting = [];

    protected array $dateColumns = [];
    protected array $dateTimeColumns = [];
    protected array $currencyColumns = [];
    protected array $amountColumns = [];

    public function __construct(array $filters = [], array $columns = [], array $formatting = [])
    {
        $this->filters = $filters;
        $this->columns = $columns;
        $this->formatting = $formatting;
    }

    abstract public function baseQuery(): Builder;

    public function query()
    {
        return QueryBuilder::for($this->baseQuery())
            ->allowedFilters($this->allowedFilters())
            ->allowedIncludes($this->allowedIncludes());
    }

    protected function allowedFilters(): array
    {
        return [];
    }

    protected function allowedIncludes(): array
    {
        return [];
    }

    public function headings(): array
    {
        return collect($this->columns)->map(
            fn($col) => Str::headline(last(explode('.', $col)))
        )->toArray();
    }

    public function map($row): array
    {
        return collect($this->columns)->map(function ($col) use ($row) {
            $value = data_get($row, $col);

            if (in_array($col, $this->dateColumns)) {
                return optional(\Carbon\Carbon::parse($value))->format($this->formatting['date'] ?? 'Y-m-d');
            }

            if (in_array($col, $this->dateTimeColumns)) {
                return optional(\Carbon\Carbon::parse($value))->format($this->formatting['datetime'] ?? 'Y-m-d H:i');
            }

            if (in_array($col, $this->currencyColumns)) {
                return number_format((float) $value, 2, ',', ' ') . ' ' . ($this->formatting['currency'] ?? 'PLN');
            }

            if (in_array($col, $this->amountColumns)) {
                return number_format((float) $value, 2, ',', ' ');
            }

            return $value;
        })->toArray();
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
```

---

### Example Implementation: `TasksExport.php`

```php
use App\Models\Task;
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
```

---

### Example Usage in Controller

```php
use App\Exports\TasksExport;
use Maatwebsite\Excel\Facades\Excel;

public function export(Request $request)
{
    return Excel::download(
        new TasksExport(
            filters: $request->all(),
            columns: $request->get('columns', ['id', 'title', 'assignee.name', 'due_date', 'budget', 'estimated_hours', 'created_at']),
            formatting: [
                'date' => 'd.m.Y',
                'datetime' => 'd.m.Y H:i',
                'currency' => 'PLN',
            ]
        ),
        'tasks.xlsx'
    );
}
```

---

## Result
This setup allows flexible and reusable export logic across different models. It supports user-defined filters, custom column selection, date/money formatting, and styled Excel output. Can be extended further with:
- Column header overrides
- Column transformers
- Column merging (e.g. full address from multiple fields)
- Queued/background exports
- Per-user saved export presets