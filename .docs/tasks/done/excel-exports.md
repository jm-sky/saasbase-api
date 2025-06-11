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

--

# Detailed Implementation Plan for Excel Export System

## 1. Directory Structure
```
app/
├── Domain/
│   └── Export/
│       ├── Exports/
│       │   ├── BaseExport.php
│       │   └── [Model]Export.php (e.g., TasksExport.php)
│       ├── DTOs/
│       │   └── ExportConfigDTO.php
│       ├── Services/
│       │   └── ExportService.php
│       └── Providers/
│           └── ExportServiceProvider.php
```

## 2. Implementation Steps

### Step 1: Setup Dependencies
1. Install required packages:
```bash
composer require maatwebsite/excel spatie/laravel-query-builder
```

### Step 2: Create Base Export Infrastructure
1. Create `ExportConfigDTO.php`:
```php
namespace App\Domain\Export\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;

class ExportConfigDTO extends BaseDataDTO
{
    public function __construct(
        public readonly array $filters = [],
        public readonly array $columns = [],
        public readonly array $formatting = [
            'date' => 'Y-m-d',
            'datetime' => 'Y-m-d H:i',
            'currency' => 'PLN',
        ]
    ) {}
}
```

2. Create `BaseExport.php` (as shown in the task description, but with these additions):
- Add type hints and return types
- Add PHPDoc blocks
- Add validation for column names
- Add support for custom column transformers
- Add support for column merging

### Step 3: Create Export Service
1. Create `ExportService.php`:
```php
namespace App\Domain\Export\Services;

use App\Domain\Export\DTOs\ExportConfigDTO;
use Maatwebsite\Excel\Facades\Excel;

class ExportService
{
    public function download(string $exportClass, ExportConfigDTO $config, string $filename)
    {
        return Excel::download(
            new $exportClass(
                filters: $config->filters,
                columns: $config->columns,
                formatting: $config->formatting
            ),
            $filename
        );
    }
}
```

### Step 4: Create Example Model Export
1. Create `TasksExport.php` (as shown in the task description)
2. Add validation for allowed columns
3. Add support for nested relationships
4. Add proper type hints and return types

### Step 5: Create Controller Integration
1. Create `ExportController.php`:
```php
namespace App\Http\Controllers\Api;

use App\Domain\Export\DTOs\ExportConfigDTO;
use App\Domain\Export\Services\ExportService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ExportController extends Controller
{
    public function __construct(
        private readonly ExportService $exportService
    ) {}

    public function export(Request $request, string $type)
    {
        $config = new ExportConfigDTO(
            filters: $request->all(),
            columns: $request->get('columns', []),
            formatting: $request->get('formatting', [])
        );

        return $this->exportService->download(
            exportClass: $this->getExportClass($type),
            config: $config,
            filename: "{$type}.xlsx"
        );
    }

    private function getExportClass(string $type): string
    {
        return match($type) {
            'tasks' => TasksExport::class,
            default => throw new \InvalidArgumentException("Unsupported export type: {$type}")
        };
    }
}
```

### Step 6: Add Tests
1. Create `ExportServiceTest.php`
2. Create `TasksExportTest.php`
3. Create `ExportControllerTest.php`

### Step 7: Add Documentation
1. Add PHPDoc blocks to all classes
2. Create README.md in the Export domain directory
3. Add example usage in the documentation

## 3. Future Enhancements
1. Add support for queued exports
2. Add export presets per user
3. Add export history
4. Add export templates
5. Add support for custom column transformers
6. Add support for column merging
7. Add support for custom styling

## 4. Testing Strategy
1. Unit tests for:
   - BaseExport class
   - ExportService
   - ExportConfigDTO
2. Integration tests for:
   - Model exports
   - Controller endpoints
3. Feature tests for:
   - Complete export flow
   - Error handling
   - Validation
