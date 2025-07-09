# Domain 5: JPK-VAT Export & Reporting Implementation

## Context
I'm extending my existing Laravel Invoice system to support JPK-VAT (Jednolity Plik Kontrolny - VAT) reporting required by Polish tax authorities. JPK-VAT is a standardized XML report containing detailed VAT transaction data that must be submitted monthly. The system needs to generate compliant XML from Invoice/InvoiceItem data including all Polish compliance fields (PKWiU, GTU, Split Payment) implemented in previous domains.

The existing system has comprehensive invoice management in `app/Domain/Financial/` with status tracking and multi-tenancy. I need to implement JPK-VAT generation, validation, and submission workflow. Backend uses only official Polish terms - frontend handles translations.

## Task: Complete JPK-VAT Export & Reporting System

**Implement JPK-VAT system that:**
1. Generates compliant JPK-VAT XML for specified periods with all required sections and control totals
2. Includes comprehensive data validation and detailed error reporting
3. Supports different JPK-VAT versions and submission workflows to tax authorities
4. Provides complete audit trails and reprocessing capabilities
5. Integrates with existing invoice workflow and all Polish compliance data

## Required Deliverables:

### Database & Models:

**Migration: `create_jpk_vat_reports_table`**
```php
Schema::create('jpk_vat_reports', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->ulid('tenant_id');
    $table->string('period_year', 4);
    $table->string('period_month', 2);
    $table->enum('period_type', ['monthly', 'quarterly']);
    $table->date('period_start');
    $table->date('period_end');
    $table->string('jpk_version', 10)->default('v7-2'); // JPK_VAT schema version
    $table->enum('status', ['draft', 'generating', 'completed', 'validated', 'submitted', 'error']);
    $table->text('xml_content', 16777215)->nullable(); // MEDIUMTEXT for large XML
    $table->json('validation_errors')->nullable();
    $table->json('control_totals')->nullable(); // Sums for verification
    $table->json('generation_metadata')->nullable(); // Processing details
    $table->integer('invoice_count')->default(0);
    $table->decimal('total_net_amount', 15, 2)->default(0);
    $table->decimal('total_vat_amount', 15, 2)->default(0);
    $table->decimal('total_gross_amount', 15, 2)->default(0);
    $table->timestamp('generated_at')->nullable();
    $table->ulid('generated_by_user_id')->nullable();
    $table->timestamps();
    
    $table->foreign('tenant_id')->references('id')->on('tenants');
    $table->foreign('generated_by_user_id')->references('id')->on('users');
    
    $table->unique(['tenant_id', 'period_year', 'period_month', 'period_type']);
    $table->index(['status', 'period_start']);
    $table->index(['tenant_id', 'status']);
});
```

**Migration: `create_jpk_vat_submissions_table`**
```php
Schema::create('jpk_vat_submissions', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->ulid('jpk_vat_report_id');
    $table->string('submission_reference')->nullable();
    $table->enum('submission_method', ['web', 'api', 'bramka']); // Submission channel
    $table->enum('status', ['pending', 'submitted', 'accepted', 'rejected', 'error']);
    $table->json('submission_response')->nullable();
    $table->json('error_details')->nullable();
    $table->integer('retry_count')->default(0);
    $table->timestamp('submitted_at')->nullable();
    $table->timestamp('last_status_check')->nullable();
    $table->ulid('submitted_by_user_id')->nullable();
    $table->timestamps();
    
    $table->foreign('jpk_vat_report_id')->references('id')->on('jpk_vat_reports')->onDelete('cascade');
    $table->foreign('submitted_by_user_id')->references('id')->on('users');
    
    $table->index(['status', 'submitted_at']);
});
```

**Migration: `create_jpk_vat_report_invoices_table`**
```php
Schema::create('jpk_vat_report_invoices', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->ulid('jpk_vat_report_id');
    $table->ulid('invoice_id');
    $table->enum('transaction_type', ['sprzedaz', 'zakup']); // Sale or purchase
    $table->json('jpk_data_snapshot')->nullable(); // Snapshot of data at generation time
    $table->boolean('included_in_report')->default(true);
    $table->text('exclusion_reason')->nullable();
    $table->timestamps();
    
    $table->foreign('jpk_vat_report_id')->references('id')->on('jpk_vat_reports')->onDelete('cascade');
    $table->foreign('invoice_id')->references('id')->on('invoices');
    
    $table->unique(['jpk_vat_report_id', 'invoice_id']);
    $table->index(['jpk_vat_report_id', 'transaction_type']);
});
```

### Domain Structure:

**Model: `app/Domain/Financial/Models/JPKVATReport.php`**
```php
<?php

namespace App\Domain\Financial\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\Tenant\Traits\BelongsToTenant;

class JPKVATReport extends BaseModel
{
    use BelongsToTenant;
    
    protected $table = 'jpk_vat_reports';
    
    protected $fillable = [
        'tenant_id',
        'period_year',
        'period_month', 
        'period_type',
        'period_start',
        'period_end',
        'jpk_version',
        'status',
        'xml_content',
        'validation_errors',
        'control_totals',
        'generation_metadata',
        'invoice_count',
        'total_net_amount',
        'total_vat_amount', 
        'total_gross_amount',
        'generated_at',
        'generated_by_user_id'
    ];
    
    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'status' => JPKVATStatusEnum::class,
        'validation_errors' => 'json',
        'control_totals' => 'json',
        'generation_metadata' => 'json',
        'invoice_count' => 'integer',
        'total_net_amount' => 'decimal:2',
        'total_vat_amount' => 'decimal:2',
        'total_gross_amount' => 'decimal:2',
        'generated_at' => 'datetime'
    ];
    
    public function submissions(): HasMany;
    public function reportInvoices(): HasMany;
    public function generatedBy(): BelongsTo;
    
    // Status methods
    public function isDraft(): bool;
    public function isGenerating(): bool;
    public function isCompleted(): bool;
    public function isValidated(): bool;
    public function hasErrors(): bool;
    
    // Period methods
    public function getPeriodLabel(): string;
    public function isMonthlyReport(): bool;
    public function isQuarterlyReport(): bool;
    public function getNextPeriod(): array;
    public function getPreviousPeriod(): array;
    
    // Validation methods
    public function getValidationErrors(): array;
    public function hasValidationErrors(): bool;
    public function getControlTotals(): array;
}
```

**Service: `app/Domain/Financial/Services/JPKVATGeneratorService.php`**
```php
<?php

namespace App\Domain\Financial\Services;

class JPKVATGeneratorService
{
    // Core generation methods
    public function generateReport(Carbon $periodStart, Carbon $periodEnd, string $periodType = 'monthly'): JPKVATReport;
    public function regenerateReport(JPKVATReport $report): JPKVATReport;
    public function generateXML(JPKVATReport $report): string;
    
    // Data collection
    public function collectSalesData(Carbon $from, Carbon $to): Collection;
    public function collectPurchaseData(Carbon $from, Carbon $to): Collection;
    public function filterInvoicesForPeriod(Carbon $from, Carbon $to): Collection;
    
    // XML structure building
    public function buildJPKHeader(JPKVATReport $report): array;
    public function buildPodmiotDowolny(Tenant $tenant): array;
    public function buildSprzedazWiersze(Collection $salesInvoices): array;
    public function buildZakupWiersze(Collection $purchaseInvoices): array;
    public function buildStawkaVat(Collection $allInvoices): array;
    public function buildSprzedazCtrl(Collection $salesInvoices): array;
    public function buildZakupCtrl(Collection $purchaseInvoices): array;
    
    // Polish compliance integration
    public function addPKWiUToSprzedazWiersz(array $wiersz, InvoiceItem $item): array;
    public function addGTUToSprzedazWiersz(array $wiersz, InvoiceItem $item): array;
    public function addSplitPaymentMarkers(array $wiersz, Invoice $invoice): array;
    
    // Calculation methods
    public function calculateControlTotals(Collection $invoices): array;
    public function calculateVATSummary(Collection $invoices): array;
    public function validateCalculations(JPKVATReport $report): ValidationResult;
    
    // Template and rendering
    public function renderJPKTemplate(array $data): string;
    public function getTemplateData(JPKVATReport $report): array;
}
```

**Service: `app/Domain/Financial/Services/JPKVATValidationService.php`**
```php
<?php

namespace App\Domain\Financial\Services;

class JPKVATValidationService
{
    // Validation methods
    public function validateReport(JPKVATReport $report): ValidationResult;
    public function validateXMLStructure(string $xml): ValidationResult;
    public function validateAgainstXSD(string $xml): bool;
    public function validateBusinessRules(JPKVATReport $report): ValidationResult;
    
    // Data validation
    public function validateControlTotals(JPKVATReport $report): ValidationResult;
    public function validateVATCalculations(JPKVATReport $report): ValidationResult;
    public function validatePeriodCompleteness(JPKVATReport $report): ValidationResult;
    public function validateInvoiceStatuses(JPKVATReport $report): ValidationResult;
    
    // Polish compliance validation
    public function validatePKWiUCodes(JPKVATReport $report): ValidationResult;
    public function validateGTUAssignments(JPKVATReport $report): ValidationResult;
    public function validateSplitPaymentData(JPKVATReport $report): ValidationResult;
    
    // Format validation
    public function validateNIPNumbers(JPKVATReport $report): ValidationResult;
    public function validateDates(JPKVATReport $report): ValidationResult;
    public function validateAmounts(JPKVATReport $report): ValidationResult;
    
    // Completeness checks
    public function checkMissingInvoices(JPKVATReport $report): array;
    public function checkDuplicateInvoices(JPKVATReport $report): array;
    public function checkInconsistentData(JPKVATReport $report): array;
}
```

### XML Template Structure:

**Template: `resources/views/jpk/jpk_vat.blade.php`**
```xml
<?xml version="1.0" encoding="UTF-8"?>
<tns:JPK xmlns:tns="http://jpk.mf.gov.pl/wzor/2022/02/17/02171/" xmlns:etd="http://crd.gov.pl/xml/schematy/dziedzinowe/mf/2022/09/05/eD/DefinicjeTypy/">
    
    <tns:Naglowek>
        <tns:KodFormularza kodSystemowy="{{ $naglowek['kodSystemowy'] }}" wersjaSchemy="{{ $naglowek['wersjaSchemy'] }}">{{ $naglowek['kodFormularza'] }}</tns:KodFormularza>
        <tns:WariantFormularza>{{ $naglowek['wariantFormularza'] }}</tns:WariantFormularza>
        <tns:CelZlozenia poz="{{ $naglowek['celZlozenia'] }}">{{ $naglowek['celZlozeniaOpis'] }}</tns:CelZlozenia>
        <tns:DataWytworzeniaJPK>{{ $naglowek['dataWytworzeniaJPK'] }}</tns:DataWytworzeniaJPK>
        <tns:DataOd>{{ $naglowek['dataOd'] }}</tns:DataOd>
        <tns:DataDo>{{ $naglowek['dataDo'] }}</tns:DataDo>
        <tns:DomyslnyKodWaluty>{{ $naglowek['domyslnyKodWaluty'] }}</tns:DomyslnyKodWaluty>
        <tns:KodUrzedu>{{ $naglowek['kodUrzedu'] }}</tns:KodUrzedu>
    </tns:Naglowek>
    
    <tns:Podmiot>
        @include('jpk.partials.podmiot_dowolny', ['podmiot' => $podmiot])
    </tns:Podmiot>
    
    @if(count($sprzedazWiersze) > 0)
    @foreach($sprzedazWiersze as $wiersz)
    <tns:SprzedazWiersz typ="G">
        @include('jpk.partials.sprzedaz_wiersz', ['wiersz' => $wiersz])
    </tns:SprzedazWiersz>
    @endforeach
    @endif
    
    @if(count($zakupWiersze) > 0)
    @foreach($zakupWiersze as $wiersz)
    <tns:ZakupWiersz typ="G">
        @include('jpk.partials.zakup_wiersz', ['wiersz' => $wiersz])
    </tns:ZakupWiersz>
    @endforeach
    @endif
    
    @foreach($stawkiVat as $stawka)
    <tns:StawkaVat>
        @include('jpk.partials.stawka_vat', ['stawka' => $stawka])
    </tns:StawkaVat>
    @endforeach
    
    <tns:SprzedazCtrl>
        @include('jpk.partials.sprzedaz_ctrl', ['ctrl' => $sprzedazCtrl])
    </tns:SprzedazCtrl>
    
    <tns:ZakupCtrl>
        @include('jpk.partials.zakup_ctrl', ['ctrl' => $zakupCtrl])
    </tns:ZakupCtrl>
    
</tns:JPK>
```

**Partial: `resources/views/jpk/partials/sprzedaz_wiersz.blade.php`**
```xml
<tns:LpSprzedazy>{{ $wiersz['lpSprzedazy'] }}</tns:LpSprzedazy>
<tns:NrKontrahenta>{{ $wiersz['nrKontrahenta'] }}</tns:NrKontrahenta>
<tns:NazwaKontrahenta>{{ $wiersz['nazwaKontrahenta'] }}</tns:NazwaKontrahenta>
<tns:AdresKontrahenta>{{ $wiersz['adresKontrahenta'] }}</tns:AdresKontrahenta>
<tns:DowodSprzedazy>{{ $wiersz['dowodSprzedazy'] }}</tns:DowodSprzedazy>
<tns:DataWystawienia>{{ $wiersz['dataWystawienia'] }}</tns:DataWystawienia>
<tns:DataSprzedazy>{{ $wiersz['dataSprzedazy'] }}</tns:DataSprzedazy>
@if(isset($wiersz['k_19']))
<tns:K_19>{{ $wiersz['k_19'] }}</tns:K_19>
@endif
@if(isset($wiersz['k_20']))
<tns:K_20>{{ $wiersz['k_20'] }}</tns:K_20>
@endif
@if(isset($wiersz['pkwiu']))
<tns:PKWIU>{{ $wiersz['pkwiu'] }}</tns:PKWIU>
@endif
@if(isset($wiersz['sw']) && $wiersz['sw'])
<tns:SW>1</tns:SW>
@endif
@if(isset($wiersz['ee']) && $wiersz['ee'])
<tns:EE>1</tns:EE>
@endif
@if(isset($wiersz['tp']) && $wiersz['tp'])
<tns:TP>1</tns:TP>
@endif
@if(isset($wiersz['tt_wnt']) && $wiersz['tt_wnt'])
<tns:TT_WNT>1</tns:TT_WNT>
@endif
@if(isset($wiersz['tt_d']) && $wiersz['tt_d'])
<tns:TT_D>1</tns:TT_D>
@endif
@if(isset($wiersz['mr_t']) && $wiersz['mr_t'])
<tns:MR_T>1</tns:MR_T>
@endif
@if(isset($wiersz['mr_uz']) && $wiersz['mr_uz'])
<tns:MR_UZ>1</tns:MR_UZ>
@endif
@if(isset($wiersz['i_42']) && $wiersz['i_42'])
<tns:I_42>1</tns:I_42>
@endif
@if(isset($wiersz['i_63']) && $wiersz['i_63'])
<tns:I_63>1</tns:I_63>
@endif
@if(isset($wiersz['b_spv']) && $wiersz['b_spv'])
<tns:B_SPV>1</tns:B_SPV>
@endif
@if(isset($wiersz['b_spv_dostawa']) && $wiersz['b_spv_dostawa'])
<tns:B_SPV_DOSTAWA>1</tns:B_SPV_DOSTAWA>
@endif
@if(isset($wiersz['b_mpv_prowizja']) && $wiersz['b_mpv_prowizja'])
<tns:B_MPV_PROWIZJA>1</tns:B_MPV_PROWIZJA>
@endif
@foreach($wiersz['gtuCodes'] as $gtuCode)
<tns:{{ $gtuCode }}>1</tns:{{ $gtuCode }}>
@endforeach
```

### API Implementation:

**Controller: `app/Domain/Financial/Controllers/JPKVATController.php`**
```php
<?php

namespace App\Domain\Financial\Controllers;

class JPKVATController extends Controller
{
    public function index(JPKVATIndexRequest $request): JsonResponse;
    public function generate(GenerateJPKVATRequest $request): JsonResponse;
    public function show(string $reportId): JsonResponse;
    public function download(string $reportId): Response;
    public function validate(string $reportId): JsonResponse;
    public function regenerate(string $reportId): JsonResponse;
    public function submit(SubmitJPKVATRequest $request, string $reportId): JsonResponse;
    public function getAvailablePeriods(): JsonResponse;
    public function preview(PreviewJPKVATRequest $request): JsonResponse;
    public function statistics(JPKVATStatsRequest $request): JsonResponse;
    public function checkCompleteness(CompletenessCheckRequest $request): JsonResponse;
}

class JPKVATSubmissionController extends Controller
{
    public function index(string $reportId): JsonResponse;
    public function retry(string $submissionId): JsonResponse;
    public function checkStatus(string $submissionId): JsonResponse;
}
```

### API Endpoints:

- `GET /api/jpk-vat/reports` - List JPK-VAT reports with filters
- `POST /api/jpk-vat/reports/generate` - Generate new JPK-VAT report
- `GET /api/jpk-vat/reports/{id}` - Get report details
- `GET /api/jpk-vat/reports/{id}/download` - Download XML file
- `POST /api/jpk-vat/reports/{id}/validate` - Validate report
- `POST /api/jpk-vat/reports/{id}/regenerate` - Regenerate report
- `POST /api/jpk-vat/reports/{id}/submit` - Submit to tax authorities
- `GET /api/jpk-vat/periods` - Get available reporting periods
- `POST /api/jpk-vat/preview` - Preview report for period
- `GET /api/jpk-vat/statistics` - Get JPK-VAT statistics
- `POST /api/jpk-vat/check-completeness` - Check period completeness
- `GET /api/jpk-vat/reports/{id}/submissions` - List submissions
- `POST /api/jpk-vat/submissions/{id}/retry` - Retry failed submission

### Testing Requirements:

**Feature Tests:**
- Test XML generation against official JPK-VAT XSD schema
- Test period-based data collection accuracy
- Test control total calculations
- Test validation rules for business logic
- Test API endpoints with various scenarios

**Unit Tests:**
- JPKVATGeneratorService calculation methods
- JPKVATValidationService validation logic
- Control total accuracy with complex scenarios
- VAT rate calculations and summaries

**Integration Tests:**
- Full report generation with real invoice data
- Performance tests with large datasets (1000+ invoices)
- XML structure validation against multiple schema versions
- Integration with all Polish compliance domains

### Data Processing Logic:

**Report Generation Flow:**
```php
public function generateReport(Carbon $periodStart, Carbon $periodEnd, string $periodType = 'monthly'): JPKVATReport
{
    // 1. Create report record
    $report = JPKVATReport::create([
        'tenant_id' => auth()->user()->tenant_id,
        'period_start' => $periodStart,
        'period_end' => $periodEnd,
        'period_type' => $periodType,
        'status' => JPKVATStatusEnum::GENERATING
    ]);
    
    // 2. Collect invoice data
    $salesInvoices = $this->collectSalesData($periodStart, $periodEnd);
    $purchaseInvoices = $this->collectPurchaseData($periodStart, $periodEnd);
    
    // 3. Generate XML structure
    $xmlData = [
        'naglowek' => $this->buildJPKHeader($report),
        'podmiot' => $this->buildPodmiotDowolny(auth()->user()->tenant),
        'sprzedazWiersze' => $this->buildSprzedazWiersze($salesInvoices),
        'zakupWiersze' => $this->buildZakupWiersze($purchaseInvoices),
        'stawkiVat' => $this->buildStawkaVat($salesInvoices->merge($purchaseInvoices)),
        'sprzedazCtrl' => $this->buildSprzedazCtrl($salesInvoices),
        'zakupCtrl' => $this->buildZakupCtrl($purchaseInvoices)
    ];
    
    // 4. Generate XML and calculate totals
    $xml = $this->renderJPKTemplate($xmlData);
    $controlTotals = $this->calculateControlTotals($salesInvoices->merge($purchaseInvoices));
    
    // 5. Update report with results
    $report->update([
        'xml_content' => $xml,
        'control_totals' => $controlTotals,
        'invoice_count' => $salesInvoices->count() + $purchaseInvoices->count(),
        'status' => JPKVATStatusEnum::COMPLETED,
        'generated_at' => now()
    ]);
    
    return $report;
}
```

### Integration Points:

**Invoice Data Collection:**
```php
// Collect sales invoices with all Polish compliance data
private function collectSalesData(Carbon $from, Carbon $to): Collection
{
    return Invoice::with([
        'items.pkwiuClassification',
        'items.gtuCodes', 
        'splitPaymentStatus',
        'buyer',
        'issuer'
    ])
    ->where('type', 'basic')
    ->where('status', 'issued')
    ->whereBetween('issue_date', [$from, $to])
    ->get()
    ->map(function (Invoice $invoice) {
        return $this->transformInvoiceForJPK($invoice);
    });
}
```

### Compliance Features:

- Complete JPK-VAT XML schema compliance (v7-2)
- Integration with PKWiU, GTU, and Split Payment data
- Automatic period-based data collection
- Control total verification and validation
- Business rule validation for Polish tax requirements
- Support for monthly and quarterly reporting
- Comprehensive error reporting and validation
- Audit trails for all generated reports
- Performance optimization for large datasets
- Multi-version schema support for future updates
