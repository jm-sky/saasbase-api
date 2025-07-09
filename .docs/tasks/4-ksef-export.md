# Domain 4: KSeF Integration & XML Generation Implementation

## Context
I'm extending my existing Laravel Invoice system that already has basic KSeF integration (`app/Services/KSeF/`) to support complete FA_VAT schema XML generation and submission. The system needs to transform our Invoice/InvoiceItem data into valid KSeF XML format including PKWiU codes, GTU codes, and Split Payment annotations for mandatory e-invoice submission to Polish tax authorities.

Current KSeF implementation includes DTOs, enums, and API integration at `app/Services/KSeF/`, but lacks the XML generation for invoice submission. I need to complete this with proper FA_VAT schema compliance. Backend uses only official Polish terms - frontend handles translations.

## Task: Complete KSeF XML Generation & Enhanced Integration

**Extend the existing KSeF system to:**
1. Generate valid FA_VAT XML from Invoice models with all Polish compliance data
2. Handle invoice submission workflow with comprehensive status tracking
3. Support corrections, cancellations, and bidirectional status synchronization
4. Include robust error handling and retry mechanisms with Polish tax authority integration
5. Provide invoice verification and comprehensive audit capabilities

## Required Deliverables:

### Database & Models:

**Migration: `create_ksef_submissions_table`**
```php
Schema::create('ksef_submissions', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->ulid('tenant_id');
    $table->ulid('invoice_id');
    $table->string('submission_type')->default('invoice'); // invoice, correction, cancellation
    $table->string('ksef_reference_number')->nullable()->unique();
    $table->enum('status', ['pending', 'submitted', 'accepted', 'rejected', 'error']);
    $table->text('submission_xml', 16777215); // MEDIUMTEXT for XML storage
    $table->json('ksef_response')->nullable();
    $table->json('error_details')->nullable();
    $table->integer('retry_count')->default(0);
    $table->timestamp('submitted_at')->nullable();
    $table->timestamp('last_status_check')->nullable();
    $table->timestamp('next_retry_at')->nullable();
    $table->ulid('submitted_by_user_id')->nullable();
    $table->timestamps();
    
    $table->foreign('tenant_id')->references('id')->on('tenants');
    $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
    $table->foreign('submitted_by_user_id')->references('id')->on('users');
    
    $table->index(['status', 'next_retry_at']);
    $table->index(['tenant_id', 'status']);
    $table->index(['submitted_at', 'status']);
});
```

**Migration: `add_ksef_fields_to_invoices`**
```php
Schema::table('invoices', function (Blueprint $table) {
    $table->string('ksef_reference_number')->nullable()->after('number');
    $table->enum('ksef_status', ['not_submitted', 'pending', 'submitted', 'accepted', 'rejected'])
          ->default('not_submitted')->after('ksef_reference_number');
    $table->timestamp('ksef_submitted_at')->nullable()->after('ksef_status');
    $table->text('ksef_annotation')->nullable()->after('ksef_submitted_at');
    
    $table->unique('ksef_reference_number');
    $table->index(['ksef_status', 'ksef_submitted_at']);
});
```

### Domain Structure:

**Model: `app/Domain/Financial/Models/KSeFSubmission.php`**
```php
<?php

namespace App\Domain\Financial\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Tenant\Traits\BelongsToTenant;

class KSeFSubmission extends BaseModel
{
    use BelongsToTenant;
    
    protected $table = 'ksef_submissions';
    
    protected $fillable = [
        'tenant_id',
        'invoice_id',
        'submission_type',
        'ksef_reference_number',
        'status',
        'submission_xml',
        'ksef_response',
        'error_details',
        'retry_count',
        'submitted_at',
        'last_status_check',
        'next_retry_at',
        'submitted_by_user_id'
    ];
    
    protected $casts = [
        'ksef_response' => 'json',
        'error_details' => 'json',
        'retry_count' => 'integer',
        'submitted_at' => 'datetime',
        'last_status_check' => 'datetime',
        'next_retry_at' => 'datetime',
        'status' => KSeFSubmissionStatusEnum::class
    ];
    
    public function invoice(): BelongsTo;
    public function submittedBy(): BelongsTo;
    
    // Status methods
    public function isPending(): bool;
    public function isSubmitted(): bool;
    public function isAccepted(): bool;
    public function isRejected(): bool;
    public function hasError(): bool;
    public function canRetry(): bool;
    public function getErrorMessage(): ?string;
    public function getKSeFErrors(): array;
    
    // Retry logic
    public function incrementRetryCount(): self;
    public function scheduleRetry(Carbon $nextRetryAt): self;
    public function markAsSubmitted(string $referenceNumber): self;
}
```

**Service: `app/Domain/Financial/Services/KSeFXMLGeneratorService.php`**
```php
<?php

namespace App\Domain\Financial\Services;

class KSeFXMLGeneratorService
{
    // Core XML generation
    public function generateInvoiceXML(Invoice $invoice): string;
    public function generateCorrectionXML(Invoice $originalInvoice, Invoice $correctionInvoice): string;
    public function generateCancellationXML(Invoice $invoice): string;
    
    // XML structure building
    public function buildFAVATHeader(Invoice $invoice): array;
    public function buildPodmiot(InvoicePartyDTO $party): array;
    public function buildFa(Invoice $invoice): array;
    public function buildFaWiersz(InvoiceItem $item): array;
    
    // Polish compliance integration
    public function addPKWiUData(array $faWiersz, InvoiceItem $item): array;
    public function addGTUData(array $fa, Invoice $invoice): array;
    public function addSplitPaymentAnnotation(array $fa, Invoice $invoice): array;
    
    // Validation and verification
    public function validateXMLStructure(string $xml): ValidationResult;
    public function validateAgainstXSD(string $xml): bool;
    public function sanitizeXMLData(array $data): array;
    
    // Template rendering
    public function renderXMLTemplate(string $template, array $data): string;
    public function getTemplateData(Invoice $invoice): array;
}
```

**Service: `app/Domain/Financial/Services/KSeFSubmissionService.php`**
```php
<?php

namespace App\Domain\Financial\Services;

class KSeFSubmissionService
{
    // Submission workflow
    public function submitInvoice(Invoice $invoice, ?User $user = null): KSeFSubmission;
    public function submitCorrection(Invoice $originalInvoice, Invoice $correctionInvoice, ?User $user = null): KSeFSubmission;
    public function submitCancellation(Invoice $invoice, ?User $user = null): KSeFSubmission;
    
    // Status management
    public function checkSubmissionStatus(KSeFSubmission $submission): KSeFSubmission;
    public function syncAllPendingSubmissions(): int;
    public function handleKSeFCallback(array $callbackData): void;
    
    // Error handling and retry
    public function retryFailedSubmission(KSeFSubmission $submission): KSeFSubmission;
    public function processRetryQueue(): int;
    public function handleSubmissionError(KSeFSubmission $submission, \Exception $exception): void;
    
    // Batch operations
    public function bulkSubmitInvoices(Collection $invoices, ?User $user = null): Collection;
    public function getSubmissionStatistics(Carbon $from, Carbon $to): array;
    
    // Integration with existing KSeF service
    public function initializeKSeFSession(): bool;
    public function submitToKSeF(string $xml): array;
    public function getSubmissionStatus(string $referenceNumber): array;
    
    // Validation
    public function validateInvoiceForSubmission(Invoice $invoice): ValidationResult;
    public function canSubmitInvoice(Invoice $invoice): bool;
}
```

### XML Templates:

**Template: `resources/views/ksef/fa_vat.blade.php`**
```xml
<?xml version="1.0" encoding="UTF-8"?>
<tns:Faktura xmlns:tns="http://crd.gov.pl/wzor/2023/06/29/12648/" xmlns:etd="http://crd.gov.pl/xml/schematy/dziedzinowe/mf/2022/09/05/eD/DefinicjeTypy/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <tns:Naglowek>
        <tns:KodFormularza kodSystemowy="{{ $header['kodSystemowy'] }}" kodPodatku="{{ $header['kodPodatku'] }}" rodzajVat="{{ $header['rodzajVat'] }}" wersjaSchemy="{{ $header['wersjaSchemy'] }}">{{ $header['kodFormularza'] }}</tns:KodFormularza>
        <tns:WariantFormularza>{{ $header['wariantFormularza'] }}</tns:WariantFormularza>
        <tns:DataWytworzeniaFa>{{ $header['dataWytworzeniaFa'] }}</tns:DataWytworzeniaFa>
        <tns:SystemInfo>{{ $header['systemInfo'] }}</tns:SystemInfo>
    </tns:Naglowek>
    
    <tns:Podmiot1>
        @include('ksef.partials.podmiot', ['podmiot' => $podmiot1])
    </tns:Podmiot1>
    
    <tns:Podmiot2>
        @include('ksef.partials.podmiot', ['podmiot' => $podmiot2])
    </tns:Podmiot2>
    
    <tns:Fa>
        @include('ksef.partials.fa', ['fa' => $fa])
    </tns:Fa>
</tns:Faktura>
```

**Partial: `resources/views/ksef/partials/fa.blade.php`**
```xml
<tns:KodWaluty>{{ $fa['kodWaluty'] }}</tns:KodWaluty>
<tns:P_1>{{ $fa['dataWystawienia'] }}</tns:P_1>
<tns:P_2A>{{ $fa['numerFaktury'] }}</tns:P_2A>
@if(isset($fa['p_6']))
<tns:P_6>{{ $fa['p_6'] }}</tns:P_6>
@endif

@foreach($fa['faWiersze'] as $wiersz)
<tns:FaWiersz>
    <tns:NrWierszaFa>{{ $wiersz['nrWiersza'] }}</tns:NrWierszaFa>
    @if(isset($wiersz['pkwiu']))
    <tns:P_7>{{ $wiersz['pkwiu'] }}</tns:P_7>
    @endif
    <tns:P_8A>{{ $wiersz['nazwaTowaru'] }}</tns:P_8A>
    <tns:P_8B>{{ $wiersz['miaraJednostki'] }}</tns:P_8B>
    <tns:P_9A>{{ $wiersz['ilosc'] }}</tns:P_9A>
    <tns:P_9B>{{ $wiersz['cenaJednostki'] }}</tns:P_9B>
    <tns:P_11>{{ $wiersz['wartoscNetto'] }}</tns:P_11>
    <tns:P_12>{{ $wiersz['stawkaVat'] }}</tns:P_12>
    <tns:P_13_1>{{ $wiersz['kwotaVat'] }}</tns:P_13_1>
</tns:FaWiersz>
@endforeach

@if(count($fa['gtuCodes']) > 0)
@foreach($fa['gtuCodes'] as $gtuCode)
<tns:{{ $gtuCode }}>1</tns:{{ $gtuCode }}>
@endforeach
@endif

@if($fa['mechanizmPodzielonejPlatnosci'])
<tns:P_16_2>{{ $fa['mechanizmPodzielonejPlatnosci'] }}</tns:P_16_2>
@endif

<tns:P_15>{{ $fa['wartoscBrutto'] }}</tns:P_15>
```

### API Implementation:

**Controller: `app/Domain/Financial/Controllers/KSeFController.php`**
```php
<?php

namespace App\Domain\Financial\Controllers;

class KSeFController extends Controller
{
    public function submitInvoice(SubmitKSeFRequest $request, string $invoiceId): JsonResponse;
    public function checkStatus(string $invoiceId): JsonResponse;
    public function downloadXML(string $submissionId): Response;
    public function retrySubmission(string $submissionId): JsonResponse;
    public function getSubmissionHistory(string $invoiceId): JsonResponse;
    public function bulkSubmit(BulkSubmitKSeFRequest $request): JsonResponse;
    public function syncStatuses(SyncKSeFStatusesRequest $request): JsonResponse;
    public function generatePreview(string $invoiceId): JsonResponse;
    public function validateForSubmission(string $invoiceId): JsonResponse;
}

class KSeFCallbackController extends Controller
{
    public function handleCallback(KSeFCallbackRequest $request): JsonResponse;
    public function handleStatusUpdate(KSeFStatusUpdateRequest $request): JsonResponse;
}
```

### API Endpoints:

- `POST /api/invoices/{id}/ksef/submit` - Submit invoice to KSeF
- `GET /api/invoices/{id}/ksef/status` - Check KSeF submission status
- `POST /api/invoices/{id}/ksef/correction` - Submit correction to KSeF
- `POST /api/invoices/{id}/ksef/cancel` - Cancel KSeF submission
- `GET /api/ksef/submissions/{id}/xml` - Download submission XML
- `POST /api/ksef/submissions/{id}/retry` - Retry failed submission
- `GET /api/ksef/submissions` - List all submissions with filters
- `POST /api/ksef/bulk-submit` - Bulk submit invoices
- `POST /api/ksef/sync-statuses` - Sync all pending statuses
- `GET /api/invoices/{id}/ksef/preview` - Generate XML preview
- `POST /api/invoices/{id}/ksef/validate` - Validate for submission
- `POST /api/ksef/callback` - Webhook endpoint for KSeF callbacks

### Testing Requirements:

**Feature Tests:**
- Test XML generation against official FA_VAT XSD schema
- Test submission workflow with mock KSeF responses
- Test error handling and retry mechanisms
- Test bulk submission functionality
- Test callback handling

**Unit Tests:**
- KSeFXMLGeneratorService XML structure
- Template rendering with various invoice scenarios
- Polish compliance data integration (PKWiU, GTU, Split Payment)
- Validation logic for submission requirements

**Integration Tests:**
- Full submission workflow with KSeF sandbox
- Status synchronization accuracy
- Error message parsing and handling
- Performance tests for bulk operations

### XML Generation Details:

**FA_VAT Structure Implementation:**
```php
public function generateInvoiceXML(Invoice $invoice): string
{
    $templateData = [
        'header' => $this->buildFAVATHeader($invoice),
        'podmiot1' => $this->buildPodmiot($invoice->issuer),
        'podmiot2' => $this->buildPodmiot($invoice->buyer), 
        'fa' => $this->buildFa($invoice)
    ];
    
    return $this->renderXMLTemplate('ksef.fa_vat', $templateData);
}

private function buildFa(Invoice $invoice): array
{
    $fa = [
        'kodWaluty' => $invoice->currency,
        'dataWystawienia' => $invoice->issue_date->format('Y-m-d'),
        'numerFaktury' => $invoice->number,
        'faWiersze' => [],
        'gtuCodes' => [],
        'wartoscBrutto' => $invoice->getTotalGrossAmount()
    ];
    
    // Add invoice items with PKWiU
    foreach ($invoice->items as $index => $item) {
        $faWiersz = $this->buildFaWiersz($item, $index + 1);
        $fa['faWiersze'][] = $this->addPKWiUData($faWiersz, $item);
    }
    
    // Add GTU codes
    $fa = $this->addGTUData($fa, $invoice);
    
    // Add split payment annotation
    $fa = $this->addSplitPaymentAnnotation($fa, $invoice);
    
    return $fa;
}
```

### Integration Points:

**Extend Invoice Model:**
```php
// Add to existing Invoice model
public function ksefSubmissions(): HasMany
{
    return $this->hasMany(KSeFSubmission::class);
}

public function latestKSeFSubmission(): HasOne
{
    return $this->hasOne(KSeFSubmission::class)->latest();
}

public function isSubmittedToKSeF(): bool;
public function canBeSubmittedToKSeF(): bool;
public function getKSeFStatus(): string;
public function getKSeFAnnotation(): ?string;
```

**Extend Existing KSeF Service:**
```php
// Enhance app/Services/KSeF/Services/KSeFService.php
public function submitFAVAT(string $xml): SendInvoiceResponseDTO
{
    $invoiceData = new SendInvoiceRequestDTO($xml);
    return $this->sendInvoice($invoiceData);
}

public function getInvoiceStatusByReference(string $referenceNumber): StatusInvoiceResponseDTO
{
    return $this->getInvoiceStatus($referenceNumber);
}
```

### Error Handling:

**Polish Tax Authority Error Mapping:**
```php
class KSeFErrorHandler
{
    protected array $errorCodeMapping = [
        'WG-001' => 'Nieprawidłowy format danych',
        'WG-002' => 'Brak wymaganych danych',
        'FA-001' => 'Nieprawidłowy numer faktury',
        'FA-002' => 'Duplikat faktury',
        // ... other error codes
    ];
    
    public function parseKSeFError(array $errorResponse): string;
    public function isRetryableError(string $errorCode): bool;
    public function getRetryDelay(int $retryCount): int;
}
```

### Compliance Features:

- Complete FA_VAT XML schema compliance
- Integration with all Polish tax requirements (PKWiU, GTU, Split Payment)
- Comprehensive error handling with Polish tax authority messages
- Automatic retry mechanisms for transient failures
- Audit trails for all submissions and status changes
- Support for corrections and cancellations
- Real-time status synchronization
- Bulk processing capabilities for large volumes
