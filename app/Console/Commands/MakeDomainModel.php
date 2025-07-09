<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeDomainModel extends Command
{
    protected $signature = 'make:domain-model {name} {domain}';

    protected $description = 'Generate model, DTO, controller, resource, and requests for a domain';

    public function handle()
    {
        $name   = Str::studly($this->argument('name'));
        $domain = Str::studly($this->argument('domain'));

        $this->generateModel($name, $domain);
        $this->generateDTO($name, $domain);
        $this->generateController($name, $domain);
        $this->generateResource($name, $domain);
        $this->generateRequests($name, $domain);

        $this->info("✅ Wygenerowano wszystkie pliki dla domeny '{$domain}' i modelu '{$name}'");
    }

    protected function generateModel($name, $domain)
    {
        $path = app_path("Domain/{$domain}/Models/{$name}.php");

        if (file_exists($path)) {
            $this->warn("⚠️  Model already exists: {$path}");

            return;
        }

        $stub = <<<PHP
<?php

namespace App\\Domain\\{$domain}\\Models;

use Carbon\\Carbon;
use App\\Domain\\Common\\Models\\BaseModel;
use App\\Domain\\Tenant\\Traits\\BelongsToTenant;

/**
 * @property string \$id
 * @property string \$tenant_id
 * @property Carbon \$created_at
 * @property Carbon \$updated_at
 */
class {$name} extends BaseModel
{
    use BelongsToTenant;

    protected \$fillable = [
        'tenant_id',
        // Add your fillable fields here
    ];

    protected \$casts = [
        // Add your field casts here
    ];
}
PHP;

        $this->writeFile($path, $stub);
    }

    protected function generateDTO($name, $domain)
    {
        $path = app_path("Domain/{$domain}/DTOs/{$name}DTO.php");

        if (file_exists($path)) {
            $this->warn("⚠️  DTO already exists: {$path}");

            return;
        }

        $stub = <<<PHP
<?php

namespace App\\Domain\\{$domain}\\DTOs;

final class {$name}DTO extends BaseDataDTO
{
    public function __construct(
        public string \$id,
        public ?Carbon \$createdAt = null,
        // Add your DTO properties here
    ) {
    }

    public static function fromArray(array \$data): static
    {
        return new static(
            id: \$data['id'],
            createdAt: \$data['created_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'         => \$this->id,
            'created_at' => \$this->createdAt?->toIso8601String(),
        ];
    }
}
PHP;

        $this->writeFile($path, $stub);
    }

    protected function generateController($name, $domain)
    {
        $path = app_path("Domain/{$domain}/Controllers/{$name}Controller.php");

        if (file_exists($path)) {
            $this->warn("⚠️  Controller already exists: {$path}");

            return;
        }

        $lowerName = Str::camel($name);

        $stub = <<<PHP
<?php

namespace App\\Domain\\{$domain}\\Controllers;

use App\\Domain\\Common\\Filters\\AdvancedFilter;
use App\\Domain\\Common\\Filters\\ComboSearchFilter;
use App\\Domain\\Common\\Traits\\HasIndexQuery;
use App\\Domain\\{$domain}\\Models\\{$name};
use App\\Domain\\{$domain}\\Requests\\Search{$name}Request;
use App\\Domain\\{$domain}\\Requests\\Store{$name}Request;
use App\\Domain\\{$domain}\\Requests\\Update{$name}Request;
use App\\Domain\\{$domain}\\Resources\\{$name}Resource;
use App\\Http\\Controllers\\Controller;
use Illuminate\\Http\\JsonResponse;
use Illuminate\\Http\\Resources\\Json\\AnonymousResourceCollection;
use Spatie\\QueryBuilder\\AllowedFilter;
use Illuminate\\Http\\Response;

class {$name}Controller extends Controller
{
    use HasIndexQuery;

    protected int \$defaultPerPage = 15;

    public function __construct()
    {
        \$this->modelClass = {$name}::class;
        \$this->defaultWith = [];

        \$this->filters = [
            AllowedFilter::custom('search', new ComboSearchFilter(['name'])), // Update with actual searchable fields
            AllowedFilter::custom('id', new AdvancedFilter()),
            AllowedFilter::custom('name', new AdvancedFilter()), // Update with actual fields
            AllowedFilter::custom('createdAt', new AdvancedFilter(), 'created_at'),
            AllowedFilter::custom('updatedAt', new AdvancedFilter(), 'updated_at'),
        ];

        \$this->sorts = [
            'name', // Update with actual sortable fields
            'createdAt' => 'created_at',
            'updatedAt' => 'updated_at',
        ];

        \$this->defaultSort = '-created_at';
    }

    public function index(Search{$name}Request \$request): JsonResponse
    {
        \$result = \$this->getIndexPaginator(\$request);

        return response()->json([
            'data' => {$name}Resource::collection(\$result['data']),
            'meta' => \$result['meta'],
        ]);
    }

    public function store(Store{$name}Request \$request): JsonResponse
    {
        \${$lowerName} = {$name}::create(\$request->validated());

        return response()->json([
            'data' => new {$name}Resource(\${$lowerName}),
        ], Response::HTTP_CREATED);
    }

    public function show({$name} \${$lowerName}): JsonResponse
    {
        return response()->json([
            'data' => new {$name}Resource(\${$lowerName}),
        ]);
    }

    public function update(Update{$name}Request \$request, {$name} \${$lowerName}): JsonResponse
    {
        \${$lowerName}->update(\$request->validated());

        return response()->json([
            'data' => new {$name}Resource(\${$lowerName}),
        ]);
    }

    public function destroy({$name} \${$lowerName}): JsonResponse
    {
        \${$lowerName}->delete();

        return response()->json(['message' => '{$name} deleted successfully.'], Response::HTTP_NO_CONTENT);
    }
}
PHP;

        $this->writeFile($path, $stub);
    }

    protected function generateResource($name, $domain)
    {
        $path = app_path("Domain/{$domain}/Resources/{$name}Resource.php");

        if (file_exists($path)) {
            $this->warn("⚠️  Resource already exists: {$path}");

            return;
        }

        $stub = <<<PHP
<?php

namespace App\\Domain\\{$domain}\\Resources;

use Illuminate\\Http\\Request;
use Illuminate\\Http\\Resources\\Json\\JsonResource;

/**
 * @mixin \\App\\Domain\\{$domain}\\Models\\{$name}
 */
class {$name}Resource extends JsonResource
{
    public function toArray(Request \$request): array
    {
        return [
            'id' => \$this->id,
            'tenantId' => \$this->tenant_id,
            'createdAt' => \$this->created_at?->toIso8601String(),
            'updatedAt' => \$this->updated_at?->toIso8601String(),
            // Add your resource fields here
        ];
    }
}
PHP;

        $this->writeFile($path, $stub);
    }

    protected function generateRequests($name, $domain)
    {
        $this->generateStoreRequest($name, $domain);
        $this->generateUpdateRequest($name, $domain);
        $this->generateSearchRequest($name, $domain);
    }

    protected function generateStoreRequest($name, $domain)
    {
        $class = "Store{$name}Request";
        $path  = app_path("Domain/{$domain}/Requests/{$class}.php");

        if (file_exists($path)) {
            $this->warn("⚠️  Request already exists: {$path}");

            return;
        }

        $stub = <<<PHP
<?php

namespace App\\Domain\\{$domain}\\Requests;

use App\\Http\\Requests\\BaseFormRequest;

class {$class} extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tenantId' => ['required', 'string', 'ulid'],
            // Add your validation rules here
        ];
    }
}
PHP;

        $this->writeFile($path, $stub);
    }

    protected function generateUpdateRequest($name, $domain)
    {
        $class = "Update{$name}Request";
        $path  = app_path("Domain/{$domain}/Requests/{$class}.php");

        if (file_exists($path)) {
            $this->warn("⚠️  Request already exists: {$path}");

            return;
        }

        $stub = <<<PHP
<?php

namespace App\\Domain\\{$domain}\\Requests;

use App\\Http\\Requests\\BaseFormRequest;

class {$class} extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Add your validation rules here
        ];
    }
}
PHP;

        $this->writeFile($path, $stub);
    }

    protected function generateSearchRequest($name, $domain)
    {
        $class = "Search{$name}Request";
        $path  = app_path("Domain/{$domain}/Requests/{$class}.php");

        if (file_exists($path)) {
            $this->warn("⚠️  Request already exists: {$path}");

            return;
        }

        $stub = <<<PHP
<?php

namespace App\\Domain\\{$domain}\\Requests;

use App\\Domain\\Common\\Rules\\ValidAdvancedFilterRule;
use App\\Http\\Requests\\BaseFormRequest;

class {$class} extends BaseFormRequest
{
    private array \$allowedSortColumns = [
        'name',
        'createdAt',
        'updatedAt',
        // Add your allowed sort columns here
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        \$sortValidationString = \$this->generateSortValidationString();

        return [
            'filter.id'         => ['sometimes', new ValidAdvancedFilterRule('string')],
            'filter.name'       => ['sometimes', new ValidAdvancedFilterRule('string')], // Update with actual fields
            'filter.createdAt'  => ['sometimes', new ValidAdvancedFilterRule('date')],
            'filter.updatedAt'  => ['sometimes', new ValidAdvancedFilterRule('date')],
            'sort'              => ['sometimes', 'string', 'in:' . \$sortValidationString],
        ];
    }

    private function generateSortValidationString(): string
    {
        \$sortOptions = [];

        foreach (\$this->allowedSortColumns as \$column) {
            \$sortOptions[] = \$column;
            \$sortOptions[] = '-' . \$column;
        }

        return implode(',', \$sortOptions);
    }
}
PHP;

        $this->writeFile($path, $stub);
    }

    protected function writeFile($path, $content)
    {
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, $content);
        $this->info("✅ Created: {$path}");
    }
}
