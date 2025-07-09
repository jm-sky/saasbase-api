# PKWiU Classification System Implementation

## Context
Laravel Invoice system with domain-driven architecture to support Polish tax compliance requirements. The system uses ULIDs, multi-tenancy, and follows established patterns with DTOs, Enums, and Services in `app/Domain/Financial/`. We have existing `Invoice` models that store line items as JSON in `invoice.body` field, and `Product` models that need to support PKWiU (Polska Klasyfikacja Wyrobów i Usług) codes required for KSeF (Krajowy System e-Faktur) compliance.

PKWiU is a hierarchical classification system (like 70.22.11.0 for software development services) that must be assigned to every invoice line item for tax reporting. This is mandatory for businesses operating in Poland from 2026. Frontend will handle all translations - backend should store only official Polish names and codes.

## Task: Complete PKWiU Classification System Implementation

**Implement a complete PKWiU classification system that:**
1. Stores official PKWiU hierarchy with original Polish descriptions only
2. Provides search and validation capabilities using official codes
3. Integrates with existing Product model via direct pkwiu_code field
4. Supports invoice items stored as JSON in invoice.body
5. Includes API endpoints for classification management

---

## Database & Models

### Migration: `create_pkwiu_classifications_table`
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pkwiu_classifications', function (Blueprint $table) {
            $table->string('code')->primary(); // e.g., "70.22.11.0"
            $table->string('parent_code')->nullable()->index();
            $table->string('name'); // Official Polish name only
            $table->text('description')->nullable(); // Official Polish description
            $table->integer('level'); // 1-4 hierarchy levels
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->foreign('parent_code')->references('code')->on('pkwiu_classifications');
            $table->index(['level', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pkwiu_classifications');
    }
};
```

### Migration: `add_pkwiu_code_to_products_table`
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('pkwiu_code')->nullable()->after('description');
            $table->foreign('pkwiu_code')->references('code')->on('pkwiu_classifications');
            $table->index('pkwiu_code');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['pkwiu_code']);
            $table->dropIndex(['pkwiu_code']);
            $table->dropColumn('pkwiu_code');
        });
    }
};
```

---

## Domain Structure

### Model: `app/Domain/Financial/Models/PKWiUClassification.php`
```php
<?php

namespace App\Domain\Financial\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Collection;

class PKWiUClassification extends Model
{
    protected $table = 'pkwiu_classifications';
    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'code',
        'parent_code', 
        'name',
        'description',
        'level',
        'is_active'
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
        'level' => 'integer'
    ];
    
    // Hierarchical relationships
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_code', 'code');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_code', 'code');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'pkwiu_code', 'code');
    }
    
    // Scopes for filtering
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByLevel($query, int $level)
    {
        return $query->where('level', $level);
    }

    public function scopeRootCategories($query)
    {
        return $query->whereNull('parent_code');
    }
    
    // Helper methods
    public function getFullHierarchyPath(): string
    {
        $path = [$this->name];
        $current = $this->parent;
        
        while ($current) {
            array_unshift($path, $current->name);
            $current = $current->parent;
        }
        
        return implode(' > ', $path);
    }

    public function isLeafNode(): bool
    {
        return $this->children()->count() === 0;
    }

    public function getAncestors(): Collection
    {
        $ancestors = collect();
        $current = $this->parent;
        
        while ($current) {
            $ancestors->push($current);
            $current = $current->parent;
        }
        
        return $ancestors;
    }

    public function getDescendants(): Collection
    {
        $descendants = collect();
        
        foreach ($this->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->getDescendants());
        }
        
        return $descendants;
    }
}
```

### DTO: `app/Domain/Financial/DTOs/PKWiUClassificationDTO.php`
```php
<?php

namespace App\Domain\Financial\DTOs;

use App\Domain\Financial\Models\PKWiUClassification;

class PKWiUClassificationDTO
{
    public function __construct(
        public readonly string $code,
        public readonly ?string $parentCode,
        public readonly string $name,
        public readonly ?string $description,
        public readonly int $level,
        public readonly bool $isActive,
        public readonly ?array $children = null,
        public readonly ?string $hierarchyPath = null
    ) {}
    
    public static function fromModel(PKWiUClassification $classification): self
    {
        return new self(
            code: $classification->code,
            parentCode: $classification->parent_code,
            name: $classification->name,
            description: $classification->description,
            level: $classification->level,
            isActive: $classification->is_active,
            hierarchyPath: $classification->getFullHierarchyPath()
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            code: $data['code'],
            parentCode: $data['parent_code'] ?? null,
            name: $data['name'],
            description: $data['description'] ?? null,
            level: $data['level'],
            isActive: $data['is_active'] ?? true,
            children: $data['children'] ?? null,
            hierarchyPath: $data['hierarchy_path'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'parent_code' => $this->parentCode,
            'name' => $this->name,
            'description' => $this->description,
            'level' => $this->level,
            'is_active' => $this->isActive,
            'children' => $this->children,
            'hierarchy_path' => $this->hierarchyPath
        ];
    }
}
```

### Service: `app/Domain/Financial/Services/PKWiUService.php`
```php
<?php

namespace App\Domain\Financial\Services;

use App\Domain\Financial\Models\PKWiUClassification;
use App\Domain\Financial\Models\Product;
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
        return preg_match('/^[0-9]{2}\.[0-9]{2}\.[0-9]{2}\.[0-9]$/', $code) === 1;
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
            ->get();
    }

    public function searchByCode(string $codePrefix): Collection
    {
        return PKWiUClassification::active()
            ->where('code', 'LIKE', "{$codePrefix}%")
            ->orderBy('code')
            ->get();
    }

    public function getHierarchyTree(?string $parentCode = null): Collection
    {
        $query = PKWiUClassification::active()
            ->with('children');

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
            ->get();
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
                $successCount++;
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
            ->toArray();
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
        return $classification ? $classification->getAncestors() : collect();
    }

    public function getLeafNodes(?string $parentCode = null): Collection
    {
        $query = PKWiUClassification::active()
            ->whereDoesntHave('children');

        if ($parentCode) {
            $query->where('parent_code', $parentCode);
        }

        return $query->get();
    }
}
```

---

## API Implementation

### Request Classes: `app/Domain/Financial/Requests/`

#### `PKWiUIndexRequest.php`
```php
<?php

namespace App\Domain\Financial\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PKWiUIndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'search' => 'sometimes|string|max:255',
            'level' => 'sometimes|integer|min:1|max:4',
            'parent_code' => 'sometimes|string|exists:pkwiu_classifications,code',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ];
    }
}
```

#### `PKWiUSearchRequest.php`
```php
<?php

namespace App\Domain\Financial\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PKWiUSearchRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'query' => 'required|string|min:2|max:255',
            'limit' => 'sometimes|integer|min:1|max:100',
        ];
    }
}
```

#### `PKWiUValidateRequest.php`
```php
<?php

namespace App\Domain\Financial\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PKWiUValidateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'code' => 'required|string|regex:/^[0-9]{2}\.[0-9]{2}\.[0-9]{2}\.[0-9]$/',
        ];
    }
}
```

#### `PKWiUValidateInvoiceRequest.php`
```php
<?php

namespace App\Domain\Financial\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PKWiUValidateInvoiceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'invoice_body' => 'required|array',
            'invoice_body.*.pkwiu_code' => 'required|string|regex:/^[0-9]{2}\.[0-9]{2}\.[0-9]{2}\.[0-9]$/',
        ];
    }
}
```

### Controller: `app/Domain/Financial/Controllers/PKWiUController.php`
```php
<?php

namespace App\Domain\Financial\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\Financial\Services\PKWiUService;
use App\Domain\Financial\Resources\PKWiUClassificationResource;
use App\Domain\Financial\Requests\{
    PKWiUIndexRequest,
    PKWiUSearchRequest,
    PKWiUValidateRequest,
    PKWiUValidateInvoiceRequest
};
use Illuminate\Http\JsonResponse;

class PKWiUController extends Controller
{
    public function __construct(
        private PKWiUService $pkwiuService
    ) {}

    public function index(PKWiUIndexRequest $request): JsonResponse
    {
        $query = PKWiUClassification::query()->active();

        if ($request->has('search')) {
            $results = $this->pkwiuService->searchByName($request->search, $request->per_page ?? 50);
        } else {
            if ($request->has('level')) {
                $query->byLevel($request->level);
            }
            
            if ($request->has('parent_code')) {
                $query->where('parent_code', $request->parent_code);
            }
            
            $results = $query->paginate($request->per_page ?? 50);
        }

        return response()->json([
            'data' => PKWiUClassificationResource::collection($results),
            'meta' => $results instanceof \Illuminate\Pagination\LengthAwarePaginator ? [
                'total' => $results->total(),
                'per_page' => $results->perPage(),
                'current_page' => $results->currentPage(),
            ] : null
        ]);
    }

    public function show(string $code): JsonResponse
    {
        $classification = PKWiUClassification::with('children', 'parent')->find($code);
        
        if (!$classification) {
            return response()->json(['message' => 'Classification not found'], 404);
        }

        return response()->json([
            'data' => new PKWiUClassificationResource($classification)
        ]);
    }

    public function tree(PKWiUTreeRequest $request): JsonResponse
    {
        $tree = $this->pkwiuService->getHierarchyTree($request->parent_code);
        
        return response()->json([
            'data' => PKWiUClassificationResource::collection($tree)
        ]);
    }

    public function search(PKWiUSearchRequest $request): JsonResponse
    {
        $results = $this->pkwiuService->searchByName(
            $request->query,
            $request->limit ?? 50
        );

        return response()->json([
            'data' => PKWiUClassificationResource::collection($results)
        ]);
    }

    public function validate(PKWiUValidateRequest $request): JsonResponse
    {
        $isValid = $this->pkwiuService->validateCode($request->code);
        
        return response()->json([
            'valid' => $isValid,
            'code' => $request->code,
            'message' => $isValid ? 'Valid PKWiU code' : 'Invalid PKWiU code'
        ]);
    }

    public function suggest(PKWiUSuggestRequest $request): JsonResponse
    {
        $suggestions = $this->pkwiuService->getCodeSuggestions($request->partial);
        
        return response()->json([
            'data' => PKWiUClassificationResource::collection($suggestions)
        ]);
    }

    public function validateInvoiceBody(PKWiUValidateInvoiceRequest $request): JsonResponse
    {
        $errors = $this->pkwiuService->validateInvoiceBodyPKWiU($request->invoice_body);
        
        return response()->json([
            'valid' => empty($errors),
            'errors' => $errors
        ]);
    }
}
```

### Resource: `app/Domain/Financial/Resources/PKWiUClassificationResource.php`
```php
<?php

namespace App\Domain\Financial\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PKWiUClassificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'code' => $this->code,
            'parentCode' => $this->parent_code,
            'name' => $this->name,
            'description' => $this->description,
            'level' => $this->level,
            'isActive' => $this->is_active,
            'hierarchyPath' => $this->getFullHierarchyPath(),
            'isLeaf' => $this->isLeafNode(),
            'children' => PKWiUClassificationResource::collection($this->whenLoaded('children')),
            'parent' => new PKWiUClassificationResource($this->whenLoaded('parent')),
        ];
    }
}
```

---

## API Endpoints & Routes: `routes/api.php`

```php
// PKWiU Classification Routes
Route::prefix('pkwiu')->group(function () {
    Route::get('/', [PKWiUController::class, 'index']);
    Route::get('/tree', [PKWiUController::class, 'tree']);
    Route::get('/search', [PKWiUController::class, 'search']);
    Route::get('/suggest', [PKWiUController::class, 'suggest']);
    Route::post('/validate', [PKWiUController::class, 'validate']);
    Route::post('/validate-invoice', [PKWiUController::class, 'validateInvoiceBody']);
    Route::get('/{code}', [PKWiUController::class, 'show']);
});

// Product PKWiU Routes
Route::put('/products/{id}/pkwiu', [ProductController::class, 'assignPKWiU']);
Route::get('/products/{id}/pkwiu', [ProductController::class, 'getPKWiU']);
```

**Endpoint Documentation:**
- `GET /api/pkwiu` - List/search classifications with filtering
- `GET /api/pkwiu/tree` - Hierarchical tree view (lazy loading support)
- `GET /api/pkwiu/{code}` - Get specific classification with hierarchy
- `POST /api/pkwiu/validate` - Validate PKWiU code format and existence
- `GET /api/pkwiu/search` - Full-text search with autocomplete
- `GET /api/pkwiu/suggest` - Get suggestions based on partial input
- `PUT /api/products/{id}/pkwiu` - Assign PKWiU code to product
- `POST /api/pkwiu/validate-invoice` - Validate PKWiU codes in invoice.body JSON

---

## Testing Requirements

### Feature Tests: `tests/Feature/Domain/Financial/PKWiU/`

#### `PKWiUControllerTest.php`
```php
<?php

namespace Tests\Feature\Domain\Financial\PKWiU;

use Tests\TestCase;
use App\Domain\Financial\Models\PKWiUClassification;

class PKWiUControllerTest extends TestCase
{
    public function test_can_list_pkwiu_classifications(): void
    {
        PKWiUClassification::factory()->count(5)->create();
        
        $response = $this->getJson('/api/pkwiu');
        
        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'code',
                        'name',
                        'level',
                        'hierarchyPath'
                    ]
                ]
            ]);
    }

    public function test_can_search_pkwiu_by_name(): void
    {
        PKWiUClassification::factory()->create([
            'name' => 'Usługi programowania',
            'code' => '70.22.11.0'
        ]);
        
        $response = $this->getJson('/api/pkwiu/search?query=programowania');
        
        $response->assertOk()
            ->assertJsonFragment(['code' => '70.22.11.0']);
    }

    public function test_can_validate_pkwiu_code(): void
    {
        PKWiUClassification::factory()->create(['code' => '70.22.11.0']);
        
        $response = $this->postJson('/api/pkwiu/validate', [
            'code' => '70.22.11.0'
        ]);
        
        $response->assertOk()
            ->assertJson(['valid' => true]);
    }

    public function test_can_get_hierarchy_tree(): void
    {
        $parent = PKWiUClassification::factory()->create([
            'code' => '70.00.00.0',
            'level' => 1
        ]);
        
        PKWiUClassification::factory()->create([
            'code' => '70.22.00.0',
            'parent_code' => '70.00.00.0',
            'level' => 2
        ]);
        
        $response = $this->getJson('/api/pkwiu/tree');
        
        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'code',
                        'children'
                    ]
                ]
            ]);
    }

    public function test_can_validate_invoice_body_pkwiu(): void
    {
        PKWiUClassification::factory()->create(['code' => '70.22.11.0']);
        
        $invoiceBody = [
            [
                'name' => 'Software Development',
                'pkwiu_code' => '70.22.11.0',
                'quantity' => 1,
                'unit_price' => 1000
            ]
        ];
        
        $response = $this->postJson('/api/pkwiu/validate-invoice', [
            'invoice_body' => $invoiceBody
        ]);
        
        $response->assertOk()
            ->assertJson(['valid' => true]);
    }
}
```

### Unit Tests: `tests/Unit/Domain/Financial/Services/`

#### `PKWiUServiceTest.php`
```php
<?php

namespace Tests\Unit\Domain\Financial\Services;

use Tests\TestCase;
use App\Domain\Financial\Services\PKWiUService;
use App\Domain\Financial\Models\PKWiUClassification;

class PKWiUServiceTest extends TestCase
{
    private PKWiUService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PKWiUService();
    }

    public function test_validates_pkwiu_code_format(): void
    {
        $this->assertTrue($this->service->isValidCodeFormat('70.22.11.0'));
        $this->assertFalse($this->service->isValidCodeFormat('70.22.11'));
        $this->assertFalse($this->service->isValidCodeFormat('70.22.11.00'));
        $this->assertFalse($this->service->isValidCodeFormat('invalid'));
    }

    public function test_searches_by_name(): void
    {
        PKWiUClassification::factory()->create([
            'name' => 'Usługi programowania komputerowego',
            'code' => '70.22.11.0'
        ]);
        
        $results = $this->service->searchByName('programowania');
        
        $this->assertCount(1, $results);
        $this->assertEquals('70.22.11.0', $results->first()->code);
    }

    public function test_extracts_pkwiu_codes_from_invoice_body(): void
    {
        $invoiceBody = [
            ['pkwiu_code' => '70.22.11.0'],
            ['pkwiu_code' => '70.22.12.0'],
            ['pkwiu_code' => '70.22.11.0'], // duplicate
        ];
        
        $codes = $this->service->extractPKWiUCodesFromInvoiceBody($invoiceBody);
        
        $this->assertCount(2, $codes);
        $this->assertContains('70.22.11.0', $codes);
        $this->assertContains('70.22.12.0', $codes);
    }

    public function test_enriches_invoice_items_with_pkwiu(): void
    {
        $product = Product::factory()->create(['pkwiu_code' => '70.22.11.0']);
        
        $invoiceItems = [
            ['product_id' => $product->id, 'name' => 'Software']
        ];
        
        $enriched = $this->service->enrichInvoiceItemsWithPKWiU($invoiceItems);
        
        $this->assertEquals('70.22.11.0', $enriched[0]['pkwiu_code']);
    }
}
```

---

## Data Seeding

### Seeder: `database/seeders/PKWiUClassificationSeeder.php`
```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\Financial\Models\PKWiUClassification;

class PKWiUClassificationSeeder extends Seeder
{
    public function run(): void
    {
        $classifications = [
            // Level 1 - Sections
            [
                'code' => '70.00.00.0',
                'parent_code' => null,
                'name' => 'Usługi związane z architekturą i inżynierią; techniczne usługi badawcze i analityczne',
                'description' => 'Działalność architektoniczna i inżynieryjna oraz związane z nią doradztwo techniczne',
                'level' => 1,
                'is_active' => true
            ],
            
            // Level 2 - Divisions
            [
                'code' => '70.20.00.0',
                'parent_code' => '70.00.00.0',
                'name' => 'Usługi doradztwa związane z zarządzaniem',
                'description' => 'Doradztwo w zakresie zarządzania przedsiębiorstwem',
                'level' => 2,
                'is_active' => true
            ],
            [
                'code' => '70.22.00.0',
                'parent_code' => '70.00.00.0',
                'name' => 'Usługi doradztwa w zakresie działalności gospodarczej i zarządzania',
                'description' => 'Usługi doradcze w zakresie prowadzenia działalności gospodarczej',
                'level' => 2,
                'is_active' => true
            ],
            
            // Level 3 - Groups
            [
                'code' => '70.22.10.0',
                'parent_code' => '70.22.00.0',
                'name' => 'Usługi doradztwa w zakresie zarządzania przedsiębiorstwem',
                'description' => 'Doradztwo strategiczne i operacyjne dla przedsiębiorstw',
                'level' => 3,
                'is_active' => true
            ],
            [
                'code' => '70.22.11.0',
                'parent_code' => '70.22.10.0',
                'name' => 'Usługi doradztwa w zakresie zarządzania finansami',
                'description' => 'Doradztwo finansowe i kontroling',
                'level' => 4,
                'is_active' => true
            ],
            [
                'code' => '70.22.12.0',
                'parent_code' => '70.22.10.0',
                'name' => 'Usługi doradztwa w zakresie zarządzania zasobami ludzkimi',
                'description' => 'Doradztwo HR i zarządzanie personelem',
                'level' => 4,
                'is_active' => true
            ],
            
            // IT Services
            [
                'code' => '62.00.00.0',
                'parent_code' => null,
                'name' => 'Usługi związane z oprogramowaniem komputerowym',
                'description' => 'Programowanie, doradztwo informatyczne i działalność powiązana',
                'level' => 1,
                'is_active' => true
            ],
            [
                'code' => '62.01.00.0',
                'parent_code' => '62.00.00.0',
                'name' => 'Usługi programowania komputerowego',
                'description' => 'Tworzenie oprogramowania komputerowego',
                'level' => 2,
                'is_active' => true
            ],
            [
                'code' => '62.01.10.0',
                'parent_code' => '62.01.00.0',
                'name' => 'Usługi programowania aplikacji',
                'description' => 'Projektowanie i tworzenie aplikacji',
                'level' => 3,
                'is_active' => true
            ],
            [
                'code' => '62.01.11.0',
                'parent_code' => '62.01.10.0',
                'name' => 'Usługi programowania aplikacji internetowych',
                'description' => 'Tworzenie aplikacji webowych i mobilnych',
                'level' => 4,
                'is_active' => true
            ],
            [
                'code' => '62.01.12.0',
                'parent_code' => '62.01.10.0',
                'name' => 'Usługi programowania aplikacji desktopowych',
                'description' => 'Tworzenie aplikacji desktopowych',
                'level' => 4,
                'is_active' => true
            ]
        ];

        foreach ($classifications as $classification) {
            PKWiUClassification::create($classification);
        }
    }
}
```

---

## Integration Points

### Extend Product Model: `app/Domain/Financial/Models/Product.php`
```php
// Add to existing Product model
public function pkwiuClassification(): BelongsTo
{
    return $this->belongsTo(PKWiUClassification::class, 'pkwiu_code', 'code');
}

// Add to fillable array
protected $fillable = [
    // ... existing fields
    'pkwiu_code',
];
```

### Invoice Body JSON Structure
```json
{
  "invoice_body": [
    {
      "product_id": "01HXYZ123456789ABCDEF",
      "name": "Software Development Services",
      "description": "Custom application development",
      "quantity": 40,
      "unit": "hours",
      "unit_price": 150.00,
      "total_price": 6000.00,
      "pkwiu_code": "62.01.11.0",
      "vat_rate": 23
    }
  ]
}
```

### Invoice Service Integration: `app/Domain/Financial/Services/InvoiceService.php`
```php
// Add methods to existing InvoiceService
public function validatePKWiUCompliance(Invoice $invoice): array
{
    $invoiceBody = json_decode($invoice->body, true);
    return $this->pkwiuService->validateInvoiceBodyPKWiU($invoiceBody);
}

public function autoAssignPKWiUCodes(Invoice $invoice): bool
{
    $invoiceBody = json_decode($invoice->body, true);
    $enrichedBody = $this->pkwiuService->enrichInvoiceItemsWithPKWiU($invoiceBody);
    
    $invoice->update(['body' => json_encode($enrichedBody)]);
    return true;
}

public function enrichInvoiceBodyWithPKWiU(array $invoiceBody): array
{
    return $this->pkwiuService->enrichInvoiceItemsWithPKWiU($invoiceBody);
}
```

---

## Factories for Testing

### Factory: `database/factories/PKWiUClassificationFactory.php`
```php
<?php

namespace Database\Factories;

use App\Domain\Financial\Models\PKWiUClassification;
use Illuminate\Database\Eloquent\Factories\Factory;

class PKWiUClassificationFactory extends Factory
{
    protected $model = PKWiUClassification::class;

    public function definition(): array
    {
        return [
            'code' => $this->faker->regexify('[0-9]{2}\.[0-9]{2}\.[0-9]{2}\.[0-9]'),
            'parent_code' => null,
            'name' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'level' => $this->faker->numberBetween(1, 4),
            'is_active' => true,
        ];
    }

    public function level(int $level): static
    {
        return $this->state(fn (array $attributes) => [
            'level' => $level,
        ]);
    }

    public function withParent(string $parentCode): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_code' => $parentCode,
        ]);
    }
}
```

---

## Performance Considerations

1. **Database Indexes:**
   - Primary key on `code` field
   - Index on `parent_code` for hierarchy queries
   - Composite index on `level` and `is_active`
   - Index on `pkwiu_code` in products table

2. **Caching Strategy:**
   - Cache frequently accessed hierarchy trees
   - Cache product PKWiU lookups
   - Cache search results for common queries

3. **Query Optimization:**
   - Use eager loading for parent/children relationships
   - Implement lazy loading for large hierarchical datasets
   - Consider materialized path pattern for complex hierarchy queries

4. **API Performance:**
   - Implement pagination for list endpoints
   - Use response caching for static hierarchies
   - Optimize search queries with full-text indexes

---

## Compliance & Business Rules

### PKWiU Code Format Validation
- Format: `XX.XX.XX.X` (e.g., `70.22.11.0`)
- Must exist in official PKWiU classification
- Required for all invoice line items from 2026

### Data Source Requirements
- Use only official GUS (Central Statistical Office) classifications
- Store original Polish names and descriptions
- Maintain version tracking for classification updates
- Support for future PKWiU standard changes

### KSeF Integration Requirements
- Every invoice line item must have valid PKWiU code
- Codes must be from active classifications only
- Support validation before KSeF submission
- Error reporting for missing or invalid codes

### Multi-tenancy Considerations
- Products can have different PKWiU codes per tenant
- Tenant-specific product mappings
- Shared PKWiU classification data across tenants
- Audit trail for PKWiU code changes

---

## Additional Configuration

### JSON Schema for Invoice Body Validation
```json
{
  "$schema": "http://json-schema.org/draft-07/schema#",
  "type": "array",
  "items": {
    "type": "object",
    "properties": {
      "product_id": {
        "type": "string",
        "pattern": "^[0-9A-HJKMNP-TV-Z]{26}$"
      },
      "name": {
        "type": "string",
        "minLength": 1,
        "maxLength": 255
      },
      "pkwiu_code": {
        "type": "string",
        "pattern": "^[0-9]{2}\\.[0-9]{2}\\.[0-9]{2}\\.[0-9]$"
      },
      "quantity": {
        "type": "number",
        "minimum": 0
      },
      "unit_price": {
        "type": "number",
        "minimum": 0
      },
      "total_price": {
        "type": "number",
        "minimum": 0
      },
      "vat_rate": {
        "type": "number",
        "minimum": 0,
        "maximum": 100
      }
    },
    "required": ["name", "pkwiu_code", "quantity", "unit_price", "total_price"]
  }
}
```

### Environment Configuration
```env
# PKWiU Configuration
PKWIU_CACHE_TTL=3600
PKWIU_SEARCH_LIMIT=50
PKWIU_VALIDATION_STRICT=true
```

This completes the comprehensive PKWiU Classification System implementation specification. The system provides full hierarchical classification management, product integration, invoice validation, and API endpoints for frontend integration while maintaining compliance with Polish tax requirements.
