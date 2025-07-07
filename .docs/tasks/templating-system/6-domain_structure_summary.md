COMPLETE DOMAIN-DRIVEN INVOICE TEMPLATE SYSTEM
==============================================

This document outlines the complete domain structure that follows your
architecture patterns with domain-driven design, ULID identifiers,
tenant scoping, and proper type annotations.



DOMAIN STRUCTURE
================
```
App/Domain/Template/
├── Models/
│   └── InvoiceTemplate.php              (Model with BelongsToTenant trait)
├── DTOs/
│   ├── TemplateInvoiceDTO.php           (Main template data structure)
│   ├── TemplatePartyDTO.php             (Seller/buyer information)
│   ├── TemplateInvoiceLineDTO.php       (Line item structure)
│   ├── TemplateVatSummaryDTO.php        (VAT summary structure)
│   ├── TemplatePaymentDTO.php           (Payment information)
│   ├── TemplateBankAccountDTO.php       (Bank account details)
│   ├── TemplateExchangeDTO.php          (Exchange rate information)
│   ├── TemplateOptionsDTO.php           (Rendering options)
│   └── TemplateSignatureDTO.php         (Signature information)
├── Services/
│   ├── InvoiceTemplateService.php       (Template CRUD operations)
│   ├── InvoiceGeneratorService.php      (PDF generation)
│   ├── TemplatingService.php            (Handlebars rendering)
│   ├── InvoiceToTemplateTransformer.php (DTO transformation)
│   ├── CurrencyFormatterService.php     (Currency formatting)
│   └── MediaUrlService.php              (Spatie Media URLs)
├── Enums/
│   └── TemplateCategory.php             (Template categories)
├── Casts/
│   ├── TemplatePreviewDataCast.php      (JSON casting)
│   └── TemplateSettingsCast.php         (Settings casting)
└── Exceptions/
    ├── TemplateNotFoundException.php
    └── TemplateRenderingException.php

App/Http/
├── Controllers/Api/
│   ├── Template/
│   │   └── InvoiceTemplateController.php
│   └── Invoice/
│       └── InvoiceGenerationController.php
├── Requests/
│   ├── Template/
│   │   ├── StoreTemplateRequest.php
│   │   ├── UpdateTemplateRequest.php
│   │   └── PreviewTemplateRequest.php
│   └── Invoice/
│       └── GenerateInvoiceRequest.php
└── Resources/
    └── Template/
        └── InvoiceTemplateResource.php

Database/
├── Migrations/
│   └── create_invoice_templates_table.php
├── Factories/
│   └── InvoiceTemplateFactory.php
└── Seeders/
    └── InvoiceTemplatesSeeder.php
```


 KEY FEATURES IMPLEMENTED
 ========================
 
 ✅ Domain-Driven Architecture
 - Proper domain folder structure
 - Clear separation of concerns
 - Domain services and DTOs
 
 ✅ ULID Integration
 - Uses BaseModel (your existing pattern)
 - Primary keys are ULIDs
 - Foreign keys use ULIDs
 
 ✅ Multi-Tenancy
 - BelongsToTenant trait usage
 - Automatic tenant scoping
 - Tenant-isolated templates
 
 ✅ Type Safety & Annotations
 - Full @property annotations on models
 - Typed method parameters and returns
 - DTOs are readonly with proper typing
 
 ✅ Laravel Best Practices
 - Form Request validation
 - API Resources for responses
 - Service Provider bindings
 - Eloquent factories for testing
 
 ✅ Template Features
 - Handlebars templating engine
 - Multi-language support (EN/PL/UK/RU)
 - Custom color schemes
 - Logo and signature support
 - Page numbering
 - Spatie Media Library integration
 
 ✅ Vue.js Frontend
 - Complete template editor
 - Live preview functionality
 - Color picker and options
 - Auto-save drafts
 - Keyboard shortcuts
 

USAGE EXAMPLES
==============

```php
// 1. Generate an invoice PDF
use App\Domain\Template\Services\InvoiceGeneratorService;
use App\Domain\Template\DTOs\TemplateOptionsDTO;

$generator = app(InvoiceGeneratorService::class);

$options = new TemplateOptionsDTO(
    language: 'pl',
    currency: 'PLN',
    accentColor: '#10B981',
    includeLogo: true,
    includeSignatures: false
);

$pdfContent = $generator->generatePdf($invoiceDTO, 'modern', $options);

// 2. Create a custom template
use App\Domain\Template\Services\InvoiceTemplateService;

$templateService = app(InvoiceTemplateService::class);

$template = $templateService->createTemplate([
    'name' => 'my-custom-template',
    'description' => 'My custom invoice template',
    'content' => '<div>{{invoice.number}}</div>',
    'category' => 'invoice'
], auth()->id());

// 3. Preview a template
$html = $templateService->previewTemplate(
    $templateContent,
    $previewData,
    $options
);
```


INSTALLATION STEPS
==================

1. Install PHP dependencies:
   composer require mpdf/mpdf zordius/lightncandy spatie/laravel-medialibrary

2. Install JS dependencies:
   npm install @heroicons/vue axios vue-i18n

3. Register the service provider in config/app.php:
   App\Providers\TemplateServiceProvider::class,

4. Run migrations:
   php artisan migrate

5. Seed default templates:
   php artisan db:seed --class=InvoiceTemplatesSeeder

6. Add routes to routes/api.php:
   ```php
   Route::prefix('templates')->name('templates.')->group(function () {
       Route::get('/invoice', [InvoiceTemplateController::class, 'index']);
       Route::post('/invoice', [InvoiceTemplateController::class, 'store']);
       Route::get('/invoice/{templateName}', [InvoiceTemplateController::class, 'show']);
       Route::put('/invoice/{template}', [InvoiceTemplateController::class, 'update']);
       Route::delete('/invoice/{template}', [InvoiceTemplateController::class, 'destroy']);
       Route::post('/invoice/preview', [InvoiceTemplateController::class, 'preview']);
   });
   
   Route::prefix('invoices')->group(function () {
       Route::post('/{invoice}/generate', [InvoiceGenerationController::class, 'generate']);
   });
   ```
7. Configure Spatie Media Library for S3 with tenant isolation

8. Add translation files to resources/lang/

9. Set up Vue.js components in your SPA



TESTING
=======

The system includes comprehensive testing support:

- Factory for creating test templates
- Scoped queries for tenant isolation
- Mock data generation for previews
- Form request validation testing

```php
// Example test
use App\Domain\Template\Models\InvoiceTemplate;

test('user can create template', function () {
    $user = User::factory()->create();
    
    $template = InvoiceTemplate::factory()
        ->userTemplate($user->id)
        ->create(['name' => 'test-template']);
    
    expect($template->user_id)->toBe($user->id);
    expect($template->tenant_id)->toBe($user->tenant_id);
});
```


SECURITY CONSIDERATIONS
======================

✅ Tenant Isolation
- All templates are automatically scoped by tenant
- Users can only access templates in their tenant
- System templates are shared across tenants but read-only

✅ User Permissions
- Users can only edit their own templates
- System templates cannot be modified by users
- Template names must be unique per tenant/user

✅ Template Security
- Handlebars templates are sandboxed
- No PHP code execution in templates
- Limited helper functions available
- Input validation on all template data

✅ Media Security
- Spatie Media Library handles secure file storage
- S3 URLs are generated securely
- Tenant isolation in media paths


PERFORMANCE OPTIMIZATIONS
=========================

✅ Template Compilation Caching
- Compiled Handlebars templates are cached
- Cache keys include language for i18n support
- Automatic cache invalidation on updates

✅ Database Optimizations
- Proper indexing for tenant queries
- Eager loading for related data
- Query scoping for performance

✅ PDF Generation
- Efficient CSS generation
- Minimal HTML structure
- Optimized mPDF configuration


FUTURE ENHANCEMENTS
===================

🔮 Template Versioning
- Version control for template changes
- Rollback functionality
- Change history tracking

🔮 Advanced Features
- Conditional template sections
- Dynamic data sources
- Template inheritance
- Custom helper functions

🔮 Integration Options
- Webhook notifications
- Email template integration
- API template sharing
- Template marketplace


This system provides a robust, scalable foundation for invoice template
management that follows your existing architecture patterns while adding
powerful templating capabilities.
