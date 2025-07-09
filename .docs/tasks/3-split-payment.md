# Domain 3: Split Payment Mechanism Implementation

## Context
I'm extending my existing Laravel Invoice system for Polish Split Payment compliance. Split Payment (Mechanizm podzielonej płatności - MPP) is a mandatory VAT payment mechanism in Poland for B2B transactions exceeding 15,000 PLN where VAT must be paid directly to a special VAT account, while net amount goes to the supplier's regular account.

The system has existing `Invoice`, `InvoiceItem` models with payment tracking via `InvoicePaymentDTO` in `app/Domain/Financial/`. I need to implement Split Payment detection, bank account management, and payment workflow modifications. Backend stores only official Polish terms - frontend handles translations.

## Task: Complete Split Payment Mechanism Implementation

**Implement Split Payment system that:**
1. Automatically detects when Split Payment applies (>15,000 PLN B2B transactions)
2. Manages separate VAT and regular bank accounts per contractor
3. Modifies payment instructions and bank transfer details
4. Provides compliance reporting and audit trails
5. Integrates with existing payment workflow and KSeF submission

## Required Deliverables:

### Database & Models:

**Migration: `create_split_payment_bank_accounts_table`**
```php
Schema::create('split_payment_bank_accounts', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->ulid('tenant_id');
    $table->ulid('contractor_id');
    $table->string('account_type')->default('vat'); // 'vat' or 'regular'
    $table->string('iban', 34);
    $table->string('swift', 11)->nullable();
    $table->string('bank_name');
    $table->string('account_holder_name');
    $table->string('account_number', 26); // Polish account number format
    $table->boolean('is_active')->default(true);
    $table->boolean('is_default')->default(false);
    $table->date('valid_from');
    $table->date('valid_to')->nullable();
    $table->timestamps();
    
    $table->foreign('tenant_id')->references('id')->on('tenants');
    $table->foreign('contractor_id')->references('id')->on('contractors');
    
    $table->unique(['contractor_id', 'account_type', 'is_default']);
    $table->index(['tenant_id', 'is_active']);
});
```

**Migration: `create_split_payment_statuses_table`**
```php
Schema::create('split_payment_statuses', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->ulid('invoice_id');
    $table->boolean('is_required')->default(false);
    $table->enum('status', ['not_required', 'required', 'exempted', 'compliant']);
    $table->text('exemption_reason')->nullable();
    $table->ulid('exempted_by_user_id')->nullable();
    $table->json('split_details')->nullable(); // Payment breakdown
    $table->timestamp('marked_compliant_at')->nullable();
    $table->ulid('marked_compliant_by_user_id')->nullable();
    $table->timestamps();
    
    $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
    $table->foreign('exempted_by_user_id')->references('id')->on('users');
    $table->foreign('marked_compliant_by_user_id')->references('id')->on('users');
    
    $table->unique('invoice_id');
    $table->index(['status', 'is_required']);
});
```

**Migration: `add_split_payment_fields_to_invoices`**
```php
Schema::table('invoices', function (Blueprint $table) {
    $table->boolean('requires_split_payment')->default(false)->after('payment_status');
    $table->enum('split_payment_status', ['not_required', 'required', 'exempted', 'compliant'])
          ->default('not_required')->after('requires_split_payment');
    $table->text('split_payment_annotation')->nullable()->after('split_payment_status');
    
    $table->index(['requires_split_payment', 'split_payment_status']);
});
```

### Domain Structure:

**Model: `app/Domain/Financial/Models/SplitPaymentBankAccount.php`**
```php
<?php

namespace App\Domain\Financial\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Tenant\Traits\BelongsToTenant;

class SplitPaymentBankAccount extends BaseModel
{
    use BelongsToTenant;
    
    protected $fillable = [
        'tenant_id',
        'contractor_id',
        'account_type',
        'iban',
        'swift',
        'bank_name',
        'account_holder_name',
        'account_number',
        'is_active',
        'is_default',
        'valid_from',
        'valid_to'
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'valid_from' => 'date',
        'valid_to' => 'date'
    ];
    
    public function contractor(): BelongsTo;
    
    // Scopes
    public function scopeActive($query);
    public function scopeVATAccounts($query);
    public function scopeRegularAccounts($query);
    public function scopeDefault($query);
    public function scopeValidAt($query, Carbon $date);
    
    // Helper methods
    public function isVATAccount(): bool;
    public function isRegularAccount(): bool;
    public function isValidAt(Carbon $date): bool;
    public function formatPolishAccountNumber(): string;
    public function validateIBAN(): bool;
}
```

**Model: `app/Domain/Financial/Models/SplitPaymentStatus.php`**
```php
<?php

namespace App\Domain\Financial\Models;

class SplitPaymentStatus extends BaseModel
{
    protected $fillable = [
        'invoice_id',
        'is_required',
        'status',
        'exemption_reason',
        'exempted_by_user_id',
        'split_details',
        'marked_compliant_at',
        'marked_compliant_by_user_id'
    ];
    
    protected $casts = [
        'is_required' => 'boolean',
        'status' => SplitPaymentStatusEnum::class,
        'split_details' => 'json',
        'marked_compliant_at' => 'datetime'
    ];
    
    public function invoice(): BelongsTo;
    public function exemptedBy(): BelongsTo;
    public function markedCompliantBy(): BelongsTo;
    
    // Status checks
    public function isRequired(): bool;
    public function isExempted(): bool;
    public function isCompliant(): bool;
    public function getSplitDetails(): ?SplitPaymentDetailsDTO;
}
```

**Enum: `app/Domain/Financial/Enums/SplitPaymentStatusEnum.php`**
```php
<?php

namespace App\Domain\Financial\Enums;

enum SplitPaymentStatusEnum: string
{
    case NOT_REQUIRED = 'not_required';
    case REQUIRED = 'required';
    case EXEMPTED = 'exempted';
    case COMPLIANT = 'compliant';
    
    public function getOfficialName(): string
    {
        return match ($this) {
            self::NOT_REQUIRED => 'Mechanizm niepodzielonej płatności',
            self::REQUIRED => 'Mechanizm podzielonej płatności',
            self::EXEMPTED => 'Zwolnienie z mechanizmu podzielonej płatności',
            self::COMPLIANT => 'Zgodność z mechanizmem podzielonej płatności'
        };
    }
}
```

**DTO: `app/Domain/Financial/DTOs/SplitPaymentDTO.php`**
```php
<?php

namespace App\Domain\Financial\DTOs;

class SplitPaymentDTO
{
    public function __construct(
        public readonly bool $isRequired,
        public readonly SplitPaymentStatusEnum $status,
        public readonly ?float $netAmount,
        public readonly ?float $vatAmount,
        public readonly ?float $totalAmount,
        public readonly ?SplitPaymentBankAccountDTO $regularAccount,
        public readonly ?SplitPaymentBankAccountDTO $vatAccount,
        public readonly ?string $exemptionReason = null,
        public readonly ?string $annotation = null
    ) {}
    
    public static function fromInvoice(Invoice $invoice): self;
    public static function fromArray(array $data): self;
    public function toArray(): array;
    public function hasValidAccounts(): bool;
    public function getPaymentInstructions(): array;
}
```

**Service: `app/Domain/Financial/Services/SplitPaymentService.php`**
```php
<?php

namespace App\Domain\Financial\Services;

class SplitPaymentService
{
    // Core detection logic
    public function requiresSplitPayment(Invoice $invoice): bool;
    public function checkAmountThreshold(Invoice $invoice): bool;
    public function checkTransactionType(Invoice $invoice): bool; // B2B check
    public function checkExemptions(Invoice $invoice): bool;
    
    // Status management
    public function updateSplitPaymentStatus(Invoice $invoice): SplitPaymentStatus;
    public function exemptFromSplitPayment(Invoice $invoice, string $reason, User $user): SplitPaymentStatus;
    public function markAsCompliant(Invoice $invoice, User $user): SplitPaymentStatus;
    
    // Payment instructions
    public function generatePaymentInstructions(Invoice $invoice): SplitPaymentDTO;
    public function getPaymentBreakdown(Invoice $invoice): array;
    public function validateBankAccounts(Contractor $contractor): ValidationResult;
    
    // Bank account management
    public function addVATAccount(Contractor $contractor, array $accountData): SplitPaymentBankAccount;
    public function getVATAccount(Contractor $contractor): ?SplitPaymentBankAccount;
    public function getRegularAccount(Contractor $contractor): ?SplitPaymentBankAccount;
    
    // Compliance checking
    public function validateSplitPaymentCompliance(Invoice $invoice): ValidationResult;
    public function getComplianceReport(Carbon $from, Carbon $to): array;
    public function identifyNonCompliantInvoices(Carbon $from, Carbon $to): Collection;
    
    // Integration methods
    public function applySplitPaymentToInvoicePayment(Invoice $invoice): InvoicePaymentDTO;
    public function generateKSeFAnnotation(Invoice $invoice): ?string;
}
```

### API Implementation:

**Controller: `app/Domain/Financial/Controllers/SplitPaymentController.php`**
```php
<?php

namespace App\Domain\Financial\Controllers;

class SplitPaymentController extends Controller
{
    public function checkRequirement(string $invoiceId): JsonResponse;
    public function updateStatus(UpdateSplitPaymentStatusRequest $request, string $invoiceId): JsonResponse;
    public function exempt(ExemptSplitPaymentRequest $request, string $invoiceId): JsonResponse;
    public function markCompliant(string $invoiceId): JsonResponse;
    public function getPaymentInstructions(string $invoiceId): JsonResponse;
    public function validateAccounts(string $contractorId): JsonResponse;
    public function complianceReport(ComplianceReportRequest $request): JsonResponse;
}

class SplitPaymentBankAccountController extends Controller
{
    public function index(string $contractorId): JsonResponse;
    public function store(StoreBankAccountRequest $request, string $contractorId): JsonResponse;
    public function update(UpdateBankAccountRequest $request, string $accountId): JsonResponse;
    public function destroy(string $accountId): JsonResponse;
    public function setDefault(string $accountId): JsonResponse;
}
```

### API Endpoints:

- `GET /api/invoices/{id}/split-payment/check` - Check if split payment required
- `PUT /api/invoices/{id}/split-payment/status` - Update split payment status
- `POST /api/invoices/{id}/split-payment/exempt` - Exempt from split payment
- `POST /api/invoices/{id}/split-payment/mark-compliant` - Mark as compliant
- `GET /api/invoices/{id}/split-payment/instructions` - Get payment instructions
- `GET /api/contractors/{id}/split-payment-accounts` - List bank accounts
- `POST /api/contractors/{id}/split-payment-accounts` - Add bank account
- `PUT /api/split-payment-accounts/{id}` - Update bank account
- `DELETE /api/split-payment-accounts/{id}` - Remove bank account
- `GET /api/split-payment/compliance-report` - Generate compliance report

### Testing Requirements:

**Feature Tests:**
- Test split payment detection logic (amount thresholds)
- Test B2B transaction identification
- Test exemption workflows
- Test payment instruction generation
- Test bank account management

**Unit Tests:**
- SplitPaymentService detection algorithms
- Amount calculation accuracy
- IBAN validation for Polish banks
- Payment breakdown calculations

**Integration Tests:**
- Invoice workflow with split payment
- KSeF export with split payment annotations
- Payment processing integration

### Business Logic Implementation:

**Detection Rules:**
```php
// Split payment required when:
// 1. B2B transaction (both parties are VAT taxpayers)
// 2. Total amount > 15,000 PLN
// 3. Not exempted by law
// 4. Both parties in Poland or EU with Polish VAT

public function requiresSplitPayment(Invoice $invoice): bool
{
    return $this->checkAmountThreshold($invoice) &&
           $this->checkTransactionType($invoice) &&
           !$this->checkExemptions($invoice);
}

private function checkAmountThreshold(Invoice $invoice): bool
{
    return $invoice->getTotalGrossAmount() > 15000.00;
}

private function checkTransactionType(Invoice $invoice): bool
{
    return $invoice->issuer->hasValidNIP() && 
           $invoice->buyer->hasValidNIP();
}
```

### Integration Points:

**Extend Invoice Model:**
```php
// Add to existing Invoice model
public function splitPaymentStatus(): HasOne
{
    return $this->hasOne(SplitPaymentStatus::class);
}

public function requiresSplitPayment(): bool;
public function getSplitPaymentAnnotation(): ?string;
public function isSplitPaymentCompliant(): bool;
```

**Extend InvoicePaymentDTO:**
```php
// Update existing InvoicePaymentDTO
public readonly ?SplitPaymentDTO $splitPayment = null;
```

**KSeF Integration:**
```php
// Add to KSeF XML generation
public function addSplitPaymentAnnotation(Invoice $invoice): string
{
    if ($invoice->requiresSplitPayment()) {
        return 'mechanizm podzielonej płatności';
    }
    return '';
}
```

### Compliance Features:

- Automatic detection based on amount and party types
- Exemption handling with audit trails
- Payment instruction generation for correct bank routing
- Integration with existing invoice payment workflow
- KSeF XML annotation support
- Compliance reporting for tax audits
- Support for different exemption scenarios (export, import, etc.)

### Polish Legal References:

- Ustawa o VAT art. 19a (Split Payment Law)
- Rozporządzenie MPP (Split Payment Regulation)
- 15,000 PLN threshold per transaction
- B2B transaction requirements
- VAT account designation requirements
