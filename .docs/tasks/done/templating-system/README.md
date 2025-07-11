# Invoice Template System - Technical Specification

> This file should be updated after every refactor to keep track of changes, what works and what not.

## 🎯 **System Purpose**

Create a comprehensive invoice template management system for a multi-tenant SaaS application that allows:

1. **Template Management**: Users can create, edit, and manage custom invoice templates
2. **PDF Generation**: Convert invoices to PDF using customizable templates with branding
3. **Multi-Language Support**: Templates support EN/PL/UK/RU with Laravel translations
4. **Tenant Isolation**: Each tenant has isolated templates with shared system defaults
5. **Rich Customization**: Colors, logos, signatures, page numbering, currency formatting
6. **Vue.js Frontend**: Complete SPA interface for template editing with live preview

## 🏗️ **Architecture Overview**

### **Domain-Driven Design Structure**
- Follows existing Laravel domain folder structure: `App/Domain/Template/`
- Uses existing patterns: BaseModel, BelongsToTenant trait, ULID identifiers
- Separates concerns: Models, DTOs, Services, Controllers, Exceptions

### **Key Components**

#### **1. Template Storage & Management**
- **InvoiceTemplate Model**: Stores template content, metadata, tenant isolation
- **Template Categories**: Invoice, Quote, Receipt, Estimate via enum
- **User Templates**: Custom templates per user + shared system templates
- **Versioning Ready**: Structure supports future template versioning

#### **2. Templating Engine**
- **Handlebars Templates**: Safe, sandboxed templating (zordius/lightncandy)
- **Helper Functions**: Currency formatting, date formatting, translations, images
- **Template Compilation**: Cached compiled templates for performance
- **Security**: No PHP code execution, limited helper functions

#### **3. PDF Generation**
- **mPDF Integration**: HTML to PDF conversion with proper formatting
- **Page Numbering**: Automatic "p. 1/2" footer generation
- **Custom CSS**: Dynamic color schemes, responsive layouts
- **Media Support**: Logo and signature image embedding

#### **4. Data Transformation**
- **DTO Architecture**: Separate Template DTOs from domain Invoice DTOs
- **Currency Formatting**: BigDecimal amounts → formatted strings
- **Media URLs**: Spatie Media Library → secure S3 URLs
- **Calculations**: All math done server-side, templates receive formatted values

#### **5. Multi-Tenancy**
- **Automatic Scoping**: BelongsToTenant trait handles tenant filtering
- **Isolated Templates**: Users only see templates from their tenant
- **Shared System Templates**: Default templates available to all tenants
- **Secure Media**: S3 paths include tenant context

## 📁 **Implementation Structure**

### **Domain Layer** (`App/Domain/Template/`)

```
Models/
├── InvoiceTemplate.php              # Main template model with tenant scoping

DTOs/
├── TemplateInvoiceDTO.php          # Main invoice data for templates
├── TemplatePartyDTO.php            # Seller/buyer information
├── TemplateInvoiceLineDTO.php      # Individual line items
├── TemplateVatSummaryDTO.php       # VAT calculation summaries
├── TemplatePaymentDTO.php          # Payment information
├── TemplateBankAccountDTO.php      # Bank account details
├── TemplateExchangeDTO.php         # Currency exchange info
├── TemplateOptionsDTO.php          # Rendering configuration
└── TemplateSignatureDTO.php       # Digital signature data

Services/
├── InvoiceTemplateService.php      # Template CRUD operations
├── InvoiceGeneratorService.php     # PDF generation orchestration
├── TemplatingService.php           # Handlebars rendering engine
├── InvoiceToTemplateTransformer.php # Domain DTO → Template DTO
├── CurrencyFormatterService.php    # BigDecimal → formatted strings
├── MediaUrlService.php             # Spatie Media → S3 URLs
└── TemplateConfigurationService.php # System configuration

Enums/
└── TemplateCategory.php            # Template types (Invoice, Quote, etc.)

Casts/
├── TemplatePreviewDataCast.php     # JSON array casting
└── TemplateSettingsCast.php       # JSON settings casting

Exceptions/
├── TemplateNotFoundException.php
└── TemplateRenderingException.php

Observers/
└── InvoiceTemplateObserver.php     # Cache invalidation on changes
```

### **HTTP Layer** (`App/Http/`)

```
Controllers/Api/
├── Template/
│   ├── InvoiceTemplateController.php    # Template CRUD API
│   └── TemplateConfigurationController.php # System config API
└── Invoice/
    └── InvoiceGenerationController.php  # PDF generation API

Requests/
├── Template/
│   ├── StoreTemplateRequest.php         # Template creation validation
│   ├── UpdateTemplateRequest.php        # Template update validation
│   └── PreviewTemplateRequest.php       # Template preview validation
└── Invoice/
    └── GenerateInvoiceRequest.php       # PDF generation validation

Resources/
└── Template/
    └── InvoiceTemplateResource.php      # JSON API responses

Middleware/
└── ValidateTemplateSize.php            # Prevent oversized templates
```

### **Database Layer**

```
migrations/
└── create_invoice_templates_table.php  # ULID primary keys, tenant isolation

factories/
└── InvoiceTemplateFactory.php          # Testing data generation

seeders/
└── InvoiceTemplatesSeeder.php          # System template defaults
```

### **Frontend Layer** (Vue.js SPA)

```
resources/js/
├── api/
│   └── templates.js                    # API client for template operations
├── components/
│   ├── InvoiceTemplateEditor.vue       # Main template editor
│   ├── PreviewOptions.vue              # Color/language/option controls
│   ├── TemplateHelperReference.vue     # Documentation component
│   └── AdvancedFeaturesReference.vue   # Extended help documentation
└── composables/
    └── useToast.js                     # Toast notifications
```

### **Configuration & Support**

```
config/
└── template.php                       # System configuration

resources/lang/
├── en/invoices.php                    # English translations
├── pl/invoices.php                    # Polish translations
├── uk/invoices.php                    # Ukrainian translations
├── ru/invoices.php                    # Russian translations
└── */common.php                       # Shared translation keys

tests/
├── Feature/Domain/Template/
│   ├── InvoiceTemplateTest.php        # Template CRUD testing
│   └── InvoiceGenerationTest.php      # PDF generation testing
└── Unit/Domain/Template/
    └── CurrencyFormatterServiceTest.php # Unit testing

console/commands/
├── GenerateInvoiceTemplatesCommand.php # CLI template creation
└── ClearTemplateCacheCommand.php      # Cache management
```

## 🔧 **Technical Requirements**

### **Dependencies**
```bash
# PHP Dependencies
composer require zordius/lightncandy
# Note: barryvdh/laravel-dompdf already installed (using instead of mpdf)
# Note: spatie/laravel-medialibrary already installed

# JavaScript Dependencies (for frontend - if needed later)
npm install @heroicons/vue vue-i18n
# Note: axios already available in existing setup
```

### **Environment Configuration**
```env
# Template defaults
TEMPLATE_DEFAULT_LANGUAGE=en
TEMPLATE_DEFAULT_TIMEZONE=UTC
TEMPLATE_DEFAULT_CURRENCY=USD
TEMPLATE_DEFAULT_ACCENT_COLOR=#3B82F6
TEMPLATE_CACHE_TTL=3600
TEMPLATE_MAX_SIZE=1048576
```

### **Database Requirements**
- **ULID Support**: Uses existing BaseModel pattern
- **Tenant Scoping**: BelongsToTenant trait integration
- **Soft Deletes**: Template versioning support
- **JSON Columns**: Settings and preview data storage
- **Proper Indexing**: Multi-tenant query optimization

## 🎨 **Template System Details**

### **Handlebars Template Structure**
Templates use Handlebars syntax with custom helpers:

```handlebars
<!-- Basic invoice structure -->
<div class="invoice-container">
  <h1 class="accent-text">{{t "invoices.invoice"}}</h1>
  <p>{{invoice.number}}</p>
  
  <!-- Company logo -->
  {{#if invoice.seller.logoUrl}}
    {{{logoUrl invoice.seller.logoUrl width="180px"}}}
  {{/if}}
  
  <!-- Line items -->
  {{#each invoice.lines}}
    <tr>
      <td>{{description}}</td>
      <td>{{formattedTotalGross}}</td>
    </tr>
  {{/each}}
  
  <!-- Signatures -->
  {{#if options.includeSignatures}}
    {{{signatureUrl options.issuerSignature.imageUrl}}}
  {{/if}}
</div>
```

### **Available Helper Functions**
- **`{{t "key"}}`**: Laravel translations
- **`{{{logoUrl url width="180px"}}}`**: Logo image rendering
- **`{{{signatureUrl url}}}`**: Signature image rendering
- **`{{#if condition}}`**: Conditional blocks
- **`{{#each array}}`**: Array iteration
- **`{{{pageNumber}}}`**: Current page (mPDF)
- **`{{{totalPages}}}`**: Total pages (mPDF)

### **Template Data Structure**
Templates receive a `TemplateInvoiceDTO` with pre-formatted values:

```php
TemplateInvoiceDTO {
  +id: string
  +number: string
  +formattedTotalGross: string  // "1,234.56 zł"
  +seller: TemplatePartyDTO
  +buyer: TemplatePartyDTO  
  +lines: TemplateInvoiceLineDTO[]
  +vatSummary: TemplateVatSummaryDTO[]
  +payment: TemplatePaymentDTO
  // ... all values pre-calculated and formatted
}
```

## 🎯 **Key Features & Capabilities**

### **1. Multi-Language Support**
- **4 Languages**: English, Polish, Ukrainian, Russian
- **Laravel Integration**: Uses `__('invoices.key')` translation system
- **Dynamic Switching**: Templates adapt to user language preference
- **Currency Symbols**: Locale-appropriate currency formatting

### **2. Customization Options**
- **Color Schemes**: 7 predefined + custom hex colors
- **Date Formats**: 6 different date display options
- **Logo Placement**: Header and/or footer positioning
- **Signatures**: Digital signature images or text signatures
- **Typography**: Tailwind CSS utility classes

### **3. Template Categories**
- **Invoice**: Standard billing documents
- **Quote**: Price quotations
- **Receipt**: Payment confirmations  
- **Estimate**: Project estimates
- **Credit Note**: Credit memos

### **4. Security & Performance**
- **Tenant Isolation**: Automatic database scoping
- **Template Sandboxing**: No PHP code execution
- **Size Limits**: Configurable template size restrictions
- **Caching**: Compiled template caching for performance
- **Input Validation**: Comprehensive request validation

### **5. Vue.js Frontend Features**
- **Live Preview**: Real-time template rendering
- **Syntax Highlighting**: Code editor with Handlebars support
- **Auto-Save**: Draft functionality with local storage
- **Keyboard Shortcuts**: Ctrl+S (save), Ctrl+P (preview)
- **Color Picker**: Visual color scheme selection
- **Help Documentation**: Built-in template reference

## 🚀 **Implementation Guidelines**

### **1. Start with Domain Layer**
1. Create models with proper annotations and relationships
2. Implement DTOs as readonly classes with type safety
3. Build services with dependency injection
4. Add proper exception handling

### **2. Database & Migrations**
1. Create migration with ULID primary keys
2. Add proper indexes for tenant queries
3. Include foreign key constraints
4. Set up factory for testing

### **3. HTTP Layer**
1. Create controllers with proper type hints
2. Implement form requests with validation rules
3. Build API resources for consistent responses
4. Add middleware for security

### **4. Template Engine**
1. Integrate LightnCandy for Handlebars
2. Register helper functions safely
3. Implement template compilation caching
4. Add proper error handling

### **5. PDF Generation**
1. Configure mPDF with proper settings
2. Add page numbering and footers
3. Implement dynamic CSS generation
4. Handle media file embedding

### **6. Frontend Integration**
1. Create Vue components with TypeScript support
2. Implement API client with proper error handling
3. Add real-time preview functionality
4. Include comprehensive help documentation

### **7. Testing Strategy**
1. Feature tests for CRUD operations
2. Unit tests for services and formatters
3. Integration tests for PDF generation
4. Frontend tests for Vue components

## 📝 **Configuration Examples**

### **Template Options**
```php
$options = new TemplateOptionsDTO(
    language: 'pl',
    timezone: 'Europe/Warsaw', 
    accentColor: '#10B981',
    secondaryColor: '#6B7280',
    includeLogo: true,
    includeSignatures: true,
    dateFormat: 'd.m.Y'
);
```

### **Usage Example**
```php
// Generate PDF
$generator = app(InvoiceGeneratorService::class);
$pdf = $generator->generatePdf($invoiceDTO, 'modern', $options);

// Create template
$templateService = app(InvoiceTemplateService::class);
$template = $templateService->createTemplate([
    'name' => 'custom-template',
    'content' => '<div>{{invoice.number}}</div>',
    'category' => 'invoice'
], auth()->id());
```

## ✅ **Success Criteria**

The implementation is complete when:

1. **✅ Users can create/edit custom templates** via Vue.js interface
2. **✅ Templates generate proper PDFs** with page numbering
3. **✅ Multi-language support works** across all 4 languages  
4. **✅ Tenant isolation functions** properly with security
5. **✅ Currency formatting displays** correctly for all supported currencies
6. **✅ Media integration works** with logos and signatures
7. **✅ All tests pass** with proper coverage
8. **✅ Performance is optimized** with caching and proper indexing

This specification provides a complete roadmap for implementing a production-ready invoice template system that integrates seamlessly with the existing Laravel domain architecture.


## Artifacts

1. [] `domain_services_controllers` - Main domain implementation (Models, DTOs, Services, Controllers)
2. [] `invoice_translations` - Translation files (EN/PL/UK/RU)
3. [] `invoice_template_dto` - Template using your DTO structure
4. [] `vue_template_editor` - Vue.js components (complete frontend)
5. [] `additional_translations` - Additional translation keys for Vue
6. [x] `domain_structure_summary` - Complete documentation & overview
7. [x] `domain_seeder_vue_updates` - Database seeder & Vue API updates
8. [x] `final_implementation_pieces` - Tests, config, additional services
