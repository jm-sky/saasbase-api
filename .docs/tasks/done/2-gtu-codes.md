# Domain 2: GTU Codes Implementation

## Context
I'm extending my existing Laravel Invoice system for Polish tax compliance. GTU (Grupa Towarów i Usług) codes are 13 specific tax classification codes (GTU_01 through GTU_13) required by Polish tax authorities to identify special types of transactions like alcoholic beverages, tobacco, vehicles, etc. These codes must be assigned to applicable invoice items for KSeF submission and JPK-VAT reporting.

The system already has `Invoice` model with invoice items stored as JSON in the `body` field (using `InvoiceLineDTO`) and `Product` model with PKWiU classification. The architecture uses domain-driven design in `app/Domain/Financial/`. I need to implement GTU code support that integrates seamlessly with the existing invoice workflow. Backend stores only official Polish names - frontend handles translations.

## Task: Complete GTU Codes Implementation

**Implement a comprehensive GTU code system that:**
1. Defines all 13 official GTU codes with official Polish descriptions and thresholds
2. Provides automatic detection and assignment based on product categories and amounts
3. Supports manual override and validation with audit trails
4. Integrates with invoice creation and KSeF export workflows
5. Includes proper compliance reporting and monitoring

## Required Deliverables:

### Database & Models:

**Migration: `create_gtu_codes_table`**
```php
Schema::create('gtu_codes', function (Blueprint $table) {
    $table->string('code', 10)->primary(); // GTU_01, GTU_02, etc.
    $table->string('name'); // Official Polish name only
    $table->text('description'); // Official Polish description
    $table->decimal('amount_threshold_pln', 12, 2)->nullable(); // For amount-based GTU codes
    $table->json('applicable_conditions')->nullable(); // Additional conditions as JSON
    $table->boolean('is_active')->default(true);
    $table->date('effective_from');
    $table->date('effective_to')->nullable();
    $table->timestamps();
    
    $table->index(['is_active', 'effective_from']);
});
```

**Migration: `add_gtu_codes_to_products_table`**
```php
Schema::table('products', function (Blueprint $table) {
    $table->json('gtu_codes')->nullable()->after('pkwiu_code');
});
```

**Note**: No separate `invoice_item_gtu_codes` or `product_gtu_mappings` tables needed since:
- Invoice items are stored as JSON in the `Invoice.body` field
- GTU codes can be stored directly on the Product model as JSON array
- Final GTU codes are stored in the `InvoiceLineDTO` during invoice creation

### Domain Structure:

**Model: `app/Domain/Financial/Models/GTUCode.php`**
```php
<?php

namespace App\Domain\Financial\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GTUCode extends Model
{
    protected $table = 'gtu_codes';
    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'code',
        'name',
        'description',
        'amount_threshold_pln',
        'applicable_conditions',
        'is_active',
        'effective_from',
        'effective_to'
    ];
    
    protected $casts = [
        'amount_threshold_pln' => 'decimal:2',
        'applicable_conditions' => 'json',
        'is_active' => 'boolean',
        'effective_from' => 'date',
        'effective_to' => 'date'
    ];
    
    public function invoiceItemGTUCodes(): HasMany;
    public function productMappings(): HasMany;
    
    // Scopes
    public function scopeActive($query);
    public function scopeEffectiveAt($query, Carbon $date);
    public function scopeWithAmountThreshold($query);
    
    // Helper methods
    public function isEffectiveAt(Carbon $date): bool;
    public function hasAmountThreshold(): bool;
    public function meetsAmountThreshold(float $amount): bool;
    public function getApplicableConditions(): array;
}
```

**Update: `app/Domain/Financial/DTOs/InvoiceLineDTO.php`**
```php
// Add GTU codes field to existing InvoiceLineDTO
public readonly ?array $gtuCodes = null; // Array of GTU code strings
```

**Update: `app/Domain/Products/Models/Product.php`**
```php
// Add to existing Product model casts
protected $casts = [
    // ... existing casts
    'gtu_codes' => 'json',
];

// Add helper methods
public function getGtuCodes(): array
{
    return $this->gtu_codes ?? [];
}

public function hasGtuCode(string $code): bool
{
    return in_array($code, $this->getGtuCodes());
}

public function addGtuCode(string $code): void
{
    $codes = $this->getGtuCodes();
    if (!in_array($code, $codes)) {
        $codes[] = $code;
        $this->gtu_codes = $codes;
    }
}
```

**Enum: `app/Domain/Financial/Enums/GTUCodeEnum.php`**
```php
<?php

namespace App\Domain\Financial\Enums;

enum GTUCodeEnum: string
{
    case GTU_01 = 'GTU_01'; // Napoje alkoholowe
    case GTU_02 = 'GTU_02'; // Towary związane z tytoniem
    case GTU_03 = 'GTU_03'; // Paliwa silnikowe
    case GTU_04 = 'GTU_04'; // Pojazdy samochodowe
    case GTU_05 = 'GTU_05'; // Urządzenia elektroniczne
    case GTU_06 = 'GTU_06'; // Części i akcesoria do pojazdów
    case GTU_07 = 'GTU_07'; // Towary o wartości >= 50 000 PLN
    case GTU_08 = 'GTU_08'; // Metale szlachetne
    case GTU_09 = 'GTU_09'; // Lekarstwa i wyroby medyczne
    case GTU_10 = 'GTU_10'; // Budynki, budowle i grunty
    case GTU_11 = 'GTU_11'; // Świadczenia gazowe
    case GTU_12 = 'GTU_12'; // Świadczenia energii elektrycznej
    case GTU_13 = 'GTU_13'; // Świadczenia telekomunikacyjne
    
    public function getOfficialName(): string;
    public function hasAmountThreshold(): bool;
    public function getAmountThreshold(): ?float;
}
```

**Service: `app/Domain/Financial/Services/GTUAssignmentService.php`**
```php
<?php

namespace App\Domain\Financial\Services;

class GTUAssignmentService
{
    // Core assignment logic
    public function autoAssignGTUCodes(InvoiceLineDTO $line, ?Product $product = null): array;
    public function assignGTUCode(InvoiceLineDTO $line, string $gtuCode): InvoiceLineDTO;
    public function removeGTUCode(InvoiceLineDTO $line, string $gtuCode): InvoiceLineDTO;
    
    // Validation methods
    public function validateGTUAssignment(InvoiceLineDTO $line, string $gtuCode): ValidationResult;
    public function validateAmountThreshold(InvoiceLineDTO $line, GTUCode $gtuCode): bool;
    public function validateApplicabilityConditions(InvoiceLineDTO $line, GTUCode $gtuCode): bool;
    
    // Product integration
    public function assignGTUToProduct(Product $product, string $gtuCode, User $user): Product;
    public function getProductGTUCodes(Product $product): array;
    public function bulkAssignByCategory(string $category, string $gtuCode, User $user): int;
    
    // Invoice processing
    public function processInvoiceGTUAssignments(Invoice $invoice): Invoice;
    public function validateInvoiceGTUCompliance(Invoice $invoice): ValidationResult;
    
    // Detection algorithms
    public function detectGTUByProductCategory(Product $product): array;
    public function detectGTUByAmount(InvoiceLineDTO $line): array;
    public function detectGTUByKeywords(string $description): array;
    
    // Reporting
    public function getGTUStatistics(Carbon $from, Carbon $to): array;
    public function getUnassignedHighValueItems(Carbon $from, Carbon $to): Collection;
}
```

### API Implementation:

**Controller: `app/Domain/Financial/Controllers/GTUController.php`**
```php
<?php

namespace App\Domain\Financial\Controllers;

class GTUController extends Controller
{
    public function index(): JsonResponse; // List all GTU codes
    public function show(string $code): JsonResponse; // Show specific GTU code
    public function assignToInvoiceLine(AssignGTURequest $request, string $invoiceId, string $lineId): JsonResponse;
    public function removeFromInvoiceLine(string $invoiceId, string $lineId, string $gtuCode): JsonResponse;
    public function bulkAssign(BulkAssignGTURequest $request): JsonResponse;
    public function autoAssign(AutoAssignGTURequest $request): JsonResponse;
    public function suggest(string $productId): JsonResponse;
    public function validateAssignment(ValidateGTURequest $request): JsonResponse;
    public function statistics(GTUStatisticsRequest $request): JsonResponse;
}
```

**Requests:**
```php
// app/Domain/Financial/Requests/AssignGTURequest.php
class AssignGTURequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'gtu_code' => 'required|exists:gtu_codes,code',
            'assignment_type' => 'required|in:manual,automatic',
            'assignment_reason' => 'required_if:assignment_type,manual|string|max:500'
        ];
    }
}

// app/Domain/Financial/Requests/BulkAssignGTURequest.php
class BulkAssignGTURequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'assignments' => 'required|array|min:1',
            'assignments.*.invoice_id' => 'required|exists:invoices,id',
            'assignments.*.line_id' => 'required|string',
            'assignments.*.gtu_code' => 'required|exists:gtu_codes,code',
            'force_override' => 'boolean'
        ];
    }
}
```

### API Endpoints:

- `GET /api/gtu-codes` - List all GTU codes with descriptions and thresholds
- `GET /api/gtu-codes/{code}` - Get specific GTU code details
- `POST /api/invoices/{id}/lines/{lineId}/gtu-codes` - Assign GTU codes to invoice line
- `DELETE /api/invoices/{id}/lines/{lineId}/gtu-codes/{code}` - Remove GTU assignment
- `POST /api/gtu-codes/bulk-assign` - Bulk assignment for multiple items
- `POST /api/gtu-codes/auto-assign` - Trigger automatic assignment
- `GET /api/gtu-codes/suggest/{productId}` - Get GTU suggestions for product
- `POST /api/gtu-codes/validate` - Validate GTU assignment
- `GET /api/gtu-codes/statistics` - GTU usage statistics and compliance reports

### Testing Requirements:

**Feature Tests:**
- Test GTU assignment to invoice items
- Test automatic detection algorithms
- Test amount threshold validation (GTU_07)
- Test bulk assignment workflows
- Test API endpoints with various scenarios

**Unit Tests:**
- GTUAssignmentService logic
- Amount threshold calculations
- Validation rules for each GTU code
- Product category mapping

**Integration Tests:**
- Invoice workflow with GTU assignment
- KSeF export including GTU data
- Performance tests with large datasets

### Data Seeding:

**Seeder: `database/seeders/GTUCodeSeeder.php`**
```php
<?php

namespace Database\Seeders;

class GTUCodeSeeder extends Seeder
{
    public function run()
    {
        $gtuCodes = [
            [
                'code' => 'GTU_01',
                'name' => 'Napoje alkoholowe',
                'description' => 'Napoje alkoholowe - piwo, wino, napoje fermentowane i wyroby pośrednie',
                'amount_threshold_pln' => null,
                'effective_from' => '2017-07-01'
            ],
            // ... continue with all 13 GTU codes
            [
                'code' => 'GTU_07',
                'name' => 'Towary o wartości przekraczającej 50 000 PLN',
                'description' => 'Towary o wartości przekraczającej 50 000 złotych za sztukę lub komplet',
                'amount_threshold_pln' => 50000.00,
                'effective_from' => '2017-07-01'
            ]
        ];
        
        foreach ($gtuCodes as $gtuData) {
            GTUCode::create($gtuData);
        }
    }
}
```

### Integration Points:

**Extend InvoiceLineDTO:**
```php
// Add to existing InvoiceLineDTO
public readonly ?array $gtuCodes = null; // Array of GTU code strings

// Helper methods in InvoiceLineDTO
public function hasGTUCode(string $code): bool
{
    return in_array($code, $this->gtuCodes ?? []);
}

public function getGTUCodes(): array
{
    return $this->gtuCodes ?? [];
}

public function withGTUCode(string $code): self
{
    $codes = $this->getGTUCodes();
    if (!in_array($code, $codes)) {
        $codes[] = $code;
    }
    return new self(
        // ... existing properties
        gtuCodes: $codes
    );
}
```

**Invoice Validation Integration:**
```php
// Add to invoice status workflow
public function validateGTUCompliance(Invoice $invoice): bool
{
    return app(GTUAssignmentService::class)->validateInvoiceGTUCompliance($invoice)->isValid();
}
```

### Compliance Features:

- Automatic detection based on product categories and PKWiU codes
- Amount threshold validation for GTU_07 (>50,000 PLN)
- Audit trail for all GTU assignments
- Integration with KSeF XML export
- Compliance reporting for tax authorities
- Support for multiple GTU codes per invoice item when applicable
