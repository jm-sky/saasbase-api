# Backend Templating System - Corrections & Fixes

## 🔧 **Issues Fixed for Codebase Alignment**

This document summarizes the key corrections made to align the templating system with your existing codebase patterns.

## 1. **Route Structure Corrections**

### ❌ Original (Incorrect)
```php
// Wrong: Not following /api/v1/ pattern, missing middleware
Route::prefix('templates')->group(function () {
    Route::get('/invoice', [InvoiceTemplateController::class, 'index']);
    // ...
});
```

### ✅ Fixed (Correct)
```php
// routes/api/templates.php
Route::middleware(['auth:api', 'is_active', 'is_in_tenant'])->group(function () {
    Route::apiResource('invoice-templates', InvoiceTemplateController::class);
    Route::post('invoice-templates/preview', [InvoiceTemplateController::class, 'preview']);
    Route::post('invoices/{invoice}/generate-pdf', [InvoiceGenerationController::class, 'generate']);
});

// Add to routes/api.php:
require __DIR__ . '/api/templates.php';
```

## 2. **Controller Structure Corrections**

### ❌ Original (Incorrect)
```php
namespace App\Http\Controllers\Api\Template;

class InvoiceTemplateController extends Controller
{
    public function index(): JsonResponse
    {
        $userId = Auth::id();
        $templates = $this->templateService->getAvailableTemplates($userId);
        return response()->json([...]);
    }
}
```

### ✅ Fixed (Correct)
```php
namespace App\Domain\Template\Controllers;

use App\Domain\Common\Traits\HasIndexQuery;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class InvoiceTemplateController extends Controller
{
    use HasIndexQuery;
    use AuthorizesRequests;

    protected int $defaultPerPage = 15;

    public function __construct()
    {
        $this->modelClass = InvoiceTemplate::class;
        $this->filters = [...];
        $this->sorts = [...];
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $templates = $this->getIndexPaginator($request);
        return InvoiceTemplateResource::collection($templates['data'])
            ->additional(['meta' => $templates['meta']]);
    }

    public function store(CreateInvoiceTemplateRequest $request): InvoiceTemplateResource
    {
        $dto = InvoiceTemplateDTO::from($request->validated());
        $template = InvoiceTemplate::create((array) $dto);
        return new InvoiceTemplateResource($template);
    }

    public function show(InvoiceTemplate $invoiceTemplate): InvoiceTemplateResource
    {
        $this->authorize('view', $invoiceTemplate);
        return new InvoiceTemplateResource($invoiceTemplate);
    }
}
```

## 3. **Model Structure Corrections**

### ❌ Original (Incorrect)
```php
// Missing BaseModel and BelongsToTenant usage
class InvoiceTemplate extends Model
{
    // ...
}
```

### ✅ Fixed (Correct)
```php
namespace App\Domain\Template\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Tenant\Traits\BelongsToTenant;

/**
 * @property string $id
 * @property string $tenant_id
 * // ... full @property annotations
 */
class InvoiceTemplate extends BaseModel
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'content',
        'preview_data',
        'is_active',
        'is_default',
        'user_id',
        'category',
        'settings',
    ];

    protected $casts = [
        'preview_data' => TemplatePreviewDataCast::class,
        'settings' => TemplateSettingsCast::class,
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'category' => TemplateCategory::class,
        'deleted_at' => 'datetime',
    ];
}
```

## 4. **PDF Generation Corrections**

### ❌ Original (Incorrect)
```php
use Mpdf\Mpdf;

// Using mpdf (not installed in your codebase)
$mpdf = new Mpdf([...]);
$mpdf->WriteHTML($html);
return $mpdf->Output('', 'S');
```

### ✅ Fixed (Correct)
```php
use Barryvdh\DomPDF\Facade\Pdf;

// Using existing dompdf package
private function generatePdfFromHtml(string $html, TemplateOptionsDTO $options): string
{
    $css = $this->generateCSS($options);
    $htmlWithCss = "<style>{$css}</style>" . $html;
    
    $pdf = Pdf::loadHTML($htmlWithCss)
        ->setPaper('A4', 'portrait')
        ->setOptions([
            'isPhpEnabled' => true,
            'isRemoteEnabled' => false,
            'defaultFont' => 'arial',
        ]);
    
    return $pdf->output();
}
```

## 5. **Request Class Corrections**

### ❌ Original (Incorrect)
```php
namespace App\Http\Requests\Template;
use Illuminate\Foundation\Http\FormRequest;

class StoreTemplateRequest extends FormRequest
```

### ✅ Fixed (Correct)
```php
namespace App\Domain\Template\Requests;
use App\Http\Requests\BaseFormRequest;

class CreateInvoiceTemplateRequest extends BaseFormRequest
```

## 6. **Dependency Package Corrections**

### ❌ Original (Incorrect)
```bash
composer require mpdf/mpdf zordius/lightncandy spatie/laravel-medialibrary
```

### ✅ Fixed (Correct)
```bash
composer require zordius/lightncandy
# Note: barryvdh/laravel-dompdf already installed (using instead of mpdf)
# Note: spatie/laravel-medialibrary already installed
```

## 7. **Invoice Domain Integration**

### ❌ Original (Incorrect)
```php
use App\Domain\Financial\DTOs\InvoiceDTO;
use App\Domain\Financial\Models\Invoice;
```

### ✅ Fixed (Correct)
```php
use App\Domain\Invoice\Models\Invoice;

public function generatePdf(Invoice $invoice, ...): string
{
    // Now properly integrated with existing Invoice domain
}
```

## 8. **File Structure Corrections**

### ❌ Original (Incorrect)
```
App/Http/Controllers/Api/Template/
App/Http/Requests/Template/
App/Http/Resources/Template/
```

### ✅ Fixed (Correct)
```
App/Domain/Template/
├── Controllers/
├── Requests/
├── Resources/
├── Models/
├── Services/
├── DTOs/
├── Enums/
└── Exceptions/
```

## 🚀 **Next Steps**

1. **General Templating Service**: Implement core templating functionality with Handlebars
2. **Authorization Policies**: Create policies for InvoiceTemplate model  
3. **Invoice Template Transformer**: Create service to transform Invoice model to template DTOs
4. **Service Provider**: Create TemplateServiceProvider for DI bindings
5. **Tests**: Implement Feature and Unit tests following existing patterns
6. **Migration**: Run the migration to create invoice_templates table
7. **Seeder**: Create default system templates

## ✅ **Alignment Achieved**

- ✅ Domain-driven architecture structure
- ✅ BaseModel and BelongsToTenant trait usage  
- ✅ ULID identifiers (via BaseModel)
- ✅ Proper middleware and route structure
- ✅ HasIndexQuery trait for pagination
- ✅ Authorization via policies
- ✅ Existing package usage (dompdf)
- ✅ BaseFormRequest pattern
- ✅ Consistent naming conventions

The templating system now follows your existing codebase patterns and can be implemented without conflicts. 
