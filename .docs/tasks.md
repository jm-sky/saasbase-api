# Tasks

## 1. [x] Add middleware to set locale based on Accept Language header. 

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

class SetLocaleFromHeader
{
    public function handle(Request $request, Closure $next)
    {
        // Pobierz listę z configu
        $supportedLocales = Config::get('app.supported_locales', ['en']);

        // Laravelowy helper do analizy Accept-Language
        $locale = $request->getPreferredLanguage($supportedLocales);

        if ($locale) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
```

---

## 2. [x] Add seeders for all models. I need seeder for skill categories and skills, let's start with IT area. I want to have a demo with few users, few tenants, some contractors, products, projects & tasks.

- **Subtasks**:
  - Create seeder for skill categories and skills (IT area).
  - Create seeder for users (demo users, include users with different roles).
  - Create seeder for tenants (few tenants).
  - Create seeder for contractors (few contractors).
  - Create seeder for products (demo products).
  - Create seeder for projects (few demo projects).
  - Create seeder for tasks (few tasks related to projects).
  - Create seeder for Vat rates - use Polish vat rates as example.
   ```jsonc
   // database/seeders/data/polish_vat_rates.json
   [
     { "rate": 0.23, "name": "23%",     "is_default": true },
     { "rate": 0.08,  "name": "8%",       "is_default": false },
     { "rate": 0.05,  "name": "5%", "is_default": false },
     { "rate": 0.0,  "name": "0%",          "is_default": false }
   ]
   ```

---

## 3. [x] Add countries to Country seeders JSON file (European countries, and most large countries).
- **Note**: We can include all countries if performance is not impacted.
- **Subtasks**:
  - Review existing Country seeder.
  - Add all countries (Europe and large countries) to the seeder.
  - Test seeding functionality.

---

## 4. [ ] Add routes & actions for current user - change settings, reset password etc.
- **Suggested**: Use Actions instead of controller methods for better organization.
- **Subtasks**:
  - Create actions for changing user settings (username, email, etc.).
  - Create action for resetting password.
  - Create action for updating user profile. 
  - Create action for updating timezone. 
  - Create action for updating notified preferences.
  - Implement action for changing language preference.
  - Implement validation for user settings actions.

   ```php
   // routes/api.php
   // Use actions instead of controllers 
   // Add timezone & notification prefs. 
   Route::middleware('auth:sanctum')->group(function () {
       Route::put('user/settings', [UserSettingsController::class, 'update']);
       Route::patch('user/language', [UserSettingsController::class, 'changeLanguage']);
   });
   Route::post('user/password-reset', [PasswordResetController::class, 'sendResetLink']);
   Route::put('user/password', [PasswordResetController::class, 'resetPassword']);
   ```
   **Done when:**  
   - All four endpoints accept and return JSON, apply validation via FormRequest or Actions, and return appropriate HTTP codes.  
   - Tests cover success and failure cases.

---

## 5. [x] Add trait (BelongsToTenant) that applies a global scope for models with `tenant_id`. We'll store `tenant_id` in session or JWT for security.  
- **Note**: User does not have tenant_id; a user can belong to many tenants, not just one.
- **Subtasks**:
  - Create a `BelongsToTenant` trait that applies a global scope for models.
  - Implement session or JWT storage for tenant identification.
  - Refactor models that should be tenant-scoped.
  - Add unit tests for tenant scoping.

---

## 6. [ ] Refactor foreign keys. i.e. refer to country code (pl, de) instead of id. Analyse.  
- **Note**: It may not be more efficient, but we would immediately see the country name instead of an anonymous ID.
- **Subtasks**:
  - Review foreign key usage for countries.
  - Replace country IDs with country codes (pl, de) in relevant models.
  - Update migrations for country references.
  - Review and update the database schema if necessary.
  - Test functionality to ensure foreign key relations are properly handled with country codes.

---

## 7. [x] Implement standardized filtering and sorting with Spatie Query Builder to index method in all CRUD controllers. 

Create trait
```php
namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

trait HasIndexQuery
{
    /**
     * The model class to query.
     */
    protected string $modelClass;

    /**
     * Allowed filters for the query.
     */
    protected array $filters = [];

    /**
     * Allowed sorts for the query.
     */
    protected array $sorts = [];

    /**
     * Default sort option.
     */
    protected string $defaultSort = '-id';

    /**
     * Create the base query using Spatie QueryBuilder.
     */
    public function getIndexQuery(Request $request): Builder
    {
        return QueryBuilder::for($this->modelClass)
            ->allowedFilters($this->filters)
            ->allowedSorts($this->sorts)
            ->defaultSort($this->defaultSort);
    }

    /**
     * Return paginated results.
     */
    public function getIndexPaginator(Request $request): LengthAwarePaginator
    {
        return $this->getIndexQuery($request)->paginate()->appends($request->query());
    }
}
```

Create DateRangeFilter
```php
namespace App\Filters;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class DateRangeFilter implements Filter
{
    protected string $column;

    public function __construct(string $column = 'date')
    {
        $this->column = $column;
    }

    public function __invoke(Builder $query, $value, string $property)
    {
        $dates = explode(',', $value);
        $from = $dates[0] ?? null;
        $to = $dates[1] ?? null;

        if ($from && !$to) {
            $query->whereDate($this->column, '=', $from);
        } elseif ($from && $to) {
            $query->whereDate($this->column, '>=', $from)
                  ->whereDate($this->column, '<=', $to);
        }
    }
}
```

Use this trait in controllers, fill `$filters` with all columns of domain model, and some relations if needed. Use DateRangeFilter for date fields.

Create something like `Search[model]Request` with validation for those filters.

Add tests.

---

## 9. [ ] Implement task-based notifications for users (e.g., for project deadlines, task assignments).

- **Subtasks**:
  - Define task-related notifications.
  - Set up notification system using Laravel Notifications.
  - Trigger notifications based on task events (task created, updated, etc.).
  - Allow users to customize notification preferences.
  - Add tests for notification functionality.

---

## 10. [ ] Add multi-language support for user interface.

- **Subtasks**:
  - Implement language files for UI translations.
  - Set up language switching feature in the UI.
  - Ensure all UI strings are translatable.
  - Test multi-language support across the application.

---

## 11. [ ] Refactor dictionary-like tables (e.g. VAT rates) to use meaningful string primary keys

- **Goal**: Improve readability and maintainability by using string values (e.g., `'5%'`, `'PL'`, `'kg'`) as primary keys instead of UUIDs for static/dictionary data.

- **Scope**: Applies to `vat_rates` (and optionally `countries`, `units`, etc.)

- **Subtasks**:
  - Change primary key of `vat_rates` from UUID to `rate` (e.g., `'5%'`, `'23%'`, `'0%'`).
    - Drop `id` UUID column if not needed.
    - Make `rate` the primary key (`string`, unique).
  - Update all foreign keys in related models (`products`, etc.):
    - Replace `vat_rate_id` with `vat_rate` (`string`).
    - Update migrations and relationships accordingly.
  - Update seeders to use `rate` as the key.
  - Adjust model relationships:
    - In `Product`, use:  
      `belongsTo(VatRate::class, 'vat_rate', 'rate')`
  - Update all forms, API payloads and tests to use new string-based keys.
  - (Optional) Apply the same pattern to other dictionaries like `countries`, `units`, etc.

---

## 12. [ ] Implement file attachments using Spatie Media Library

- **Goal**: Allow models to support file attachments (e.g., for tasks, invoices, products, etc.) using the Spatie Media Library package.

- **Subtasks**:
  - **Install and configure**: Install and configure [spatie/laravel-medialibrary](https://github.com/spatie/laravel-medialibrary).
  - **Set up media storage**:
    - Configure the media disk in `config/filesystems.php` and `.env` for MinIO integration.
  - **Create a reusable trait**:
    - Create a trait `HasAttachments` to handle media logic across models.
  - **Update models to use media**:
    - Add `InteractsWithMedia` and `HasMedia` to models like `User`, `Project`, `Task`, `Contractor`, `Comment`, `Tenant`, `Invoice`, and `Product`.
  - **Define media collections**:
    - Allow models to handle multiple collections such as `profile_images`, `task_attachments`, `product_images`, `invoice_pdfs`, etc.
    - Define media conversions for thumbnails, PDF previews, etc.
  - **Implement attachment CRUD**:
    - Create controllers (e.g., `ProductAttachmentsController`, `InvoiceAttachmentsController`) for handling file upload, update, deletion, and retrieval.
    - Support single and multiple file uploads.
    - Implement actions or API endpoints to upload, update, delete, and retrieve attachments.
  - **Update API Resources or Transformers**:
    - Ensure media URLs are included in API responses (e.g., `profile_image_url`, `task_attachments_url`).
  - **Handle file types and previews**:
    - Implement preview generation for common file types (e.g., PDF thumbnails, image resizing).
  - **Testing**:
    - Write unit and feature tests for uploading and retrieving media across all models.
    - Test the integration of MinIO for file storage. 

---

## 13. [ ] Integrate company data lookup via NIP using the Polish White List API (MF)

- **Goal**: Allow fetching contractor details (e.g., name, address, VAT status) using NIP via the official [White List of VAT Taxpayers API](https://www.podatki.gov.pl/wykaz-podatnikow-vat).
- **Use cases**: 
  - API endpoint for user lookup (manual input)
  - Internal processes (e.g., invoice imports, contractor matching)
- **Caching**: Use configurable cache (default until midnight, but can be overridden to e.g. next month)
- **HTTP Client**: Use [Saloon](https://docs.saloon.dev) for integration.

- **Subtasks**:
  - Install and configure Saloon HTTP client package
  - Create connector class for MF White List API using Saloon
  - Create request class for NIP lookup via Saloon
  - Build service class `CompanyLookupService` to fetch and parse data from API
  - Add configurable caching logic:
    - Default: cache until midnight
    - Accept override for next-day / next-month expiration
  - Create API endpoint `GET /api/contractors/lookup-by-nip/{nip}`:
    - Validate NIP format
    - Call `CompanyLookupService`
    - Return company name, address, VAT status, REGON, bank accounts, etc.
  - Log lookup results and errors (optional: store audit log)
  - Write tests for API and service class
  - Use internally in processes like invoice import or contractor auto-fill 

---

## 14. [ ] Generate PDF files from invoices

- **Goal**: Automatically generate PDF representations of invoices for downloading, sending, and archiving purposes.

- **Note**: We'll use **mPDF** for better support of modern HTML/CSS and multilingual content.

- **Subtasks**:
  - Install [mpdf/mpdf](https://github.com/mpdf/mpdf) via Composer and configure it.
  - Create a Blade view or HTML template for rendering the invoice layout.
  - Create a service class `InvoicePdfGenerator` to generate PDFs from invoice data using mPDF.
  - Store generated PDFs as media attachments using Spatie Media Library (collection: `generated_pdfs`).
  - Add an endpoint or action to download or regenerate the PDF for a specific invoice.
  - Include tenant branding (logo, company info) and styling in the layout.
  - Handle currency formatting, VAT, totals, line items, etc.
  - Add automated tests to ensure correctness of generated PDFs.

---

## 16. [ ] Handle notifications for project/task updates and invoices waiting for payment

- **Goal**: Implement a notification system to inform users of project/task changes and invoices that are pending payment.

- **Subtasks**:
  - **Set up notifications**:
    - Define notification types: project/task updates (e.g., assigned, completed), invoice payment reminders.
    - Use **Laravel Notifications** to send notifications via email, SMS, or in-app alerts.
  - **Create custom notification channels** (if needed), such as:
    - Email notifications for project/task updates and invoice payment reminders.
    - In-app notifications for users to track progress on assigned tasks or upcoming payments.
  - **Use Horizon for background job processing**:
    - Set up **Laravel Horizon** for managing background jobs related to notifications.
    - Create queued jobs for sending notifications (e.g., task assignment, project status change, invoice due reminders).
    - Configure Horizon to monitor and manage these jobs effectively.
  - **Create jobs**:
    - Create queued jobs for project/task notifications (e.g., `SendProjectUpdateNotification`, `SendTaskUpdateNotification`).
    - Create jobs for invoice payment reminders (e.g., `SendInvoicePaymentReminder`).
  - **Handle delayed notifications**:
    - For invoices, ensure notifications are sent a few days before the due date.
    - For project/task updates, send notifications immediately or after a short delay.
  - **Add testing**:
    - Write automated tests for job processing and notification delivery.
    - Test scenarios for notifications on task changes, project progress, and invoice due dates.
  - **Optional**:
    - Implement user preferences for notification settings (e.g., frequency of reminders, channels).
    - Add retry logic for failed notifications and ensure proper logging for debugging.

---

## 17. [ ] Implement customizable statuses per model with admin-managed defaults

- **Goal**: Allow each tenant to define their own statuses per domain model (e.g., tasks, projects), with a shared set of admin-managed default statuses.

- **Subtasks**:
  - Create separate status models, e.g. `ProjectStatus`, `TaskStatus`.
  - For each status model:
    - Add `tenant_id` (**required**).
    - Add `is_default` boolean flag (used to distinguish default records copied to new tenants).
    - Include fields like `name`, `color`, `order`, etc.
  - Create seeders for `DefaultProjectStatus` and `DefaultTaskStatus` managed by the system.
  - On tenant creation, copy default statuses into tenant-specific records with proper `tenant_id`.
  - Create API endpoints/actions to manage statuses per tenant (CRUD).
  - Ensure each related domain model (`Project`, `Task`, etc.) uses the appropriate status model via relationship.
  - Add UI and validation rules for status management.
  - Include automated tests for:
    - Default seeding
    - Tenant customization
    - Relationship with domain models 

---

## 18. [ ] Implement tenant-specific measurement units for invoice items (e.g. hour, day, km)

- **Goal**: Allow tenants to manage their own units of measurement (e.g., hours, kilometers), with admin-managed defaults categorized by type.

- **Subtasks**:
  - Create `MeasurementUnit` model with the following fields:
    - `tenant_id` (**required**)
    - `name` (e.g. "hour")
    - `shortcut` (e.g. "h")
    - `category` (e.g. "time", "length", "energy")
    - `is_default` (boolean to distinguish system defaults)
  - Seed default units (e.g., hour, day, km, liter) categorized properly.
  - On tenant creation, copy default units to tenant with `is_default = true`.
  - Allow tenants to:
    - View their unit list
    - Create new custom units
    - Add more predefined units from a selected category
  - Add API endpoints/actions to manage units (CRUD).
  - Add validation for name/shortcut uniqueness per tenant.
  - Use the `MeasurementUnit` model in invoice items and other relevant models.
  - Add automated tests for:
    - Default seeding
    - Tenant initialization
    - CRUD operations
    - Unit usage in invoices

---

## 19.[] **Invitation System**
  - Implement a system allowing users with appropriate permissions to send invitations to join a tenant.
  - Each invitation should include:
    - Recipient email address.
    - The tenant the invitation is associated with.
    - The role the invited user should be assigned to upon accepting the invitation.
  - Support invitation acceptance via token-based link (e.g. signed URL or UUID/UULID).
  - Handle cases for existing users and new user registrations through invitations.
  - Store invitation metadata (status, timestamps, who invited whom, etc.).
  - Migration and `Invitation` model with fields: `recipient_email`, `tenant_id`, `role`, `invitation_token`, `invited_by`, `status`, `sent_at`.  
   - `InvitationController@send`: accepts `{ email, tenant_id, role }`, creates record, sends signed URL email.  
   - `InvitationController@accept`: via `GET /api/v1/invitations/{token}`, links or creates user, assigns role.  
   - **Done when:**  
     - Endpoints `POST /api/v1/tenant/{tenant}/invite` and `GET /api/v1/invitations/{token}` work end‑to‑end.  
     - Tests assert invitation creation, email sent, and acceptance flow.

---

## 20. [] **Generate OpenAPI YAML Specification**
  - Automatically generate OpenAPI documentation in YAML format for the entire API.
  - Include all endpoints, models, request/response schemas, authentication details.
  - Ensure compatibility with tools like Swagger UI and Postman.
  - Preferably automate via Artisan command or during CI build.

---

## 21. [] **Migrate UUID to ULID**
  - Replace UUID identifiers with ULID across all models that currently use UUID.
  - Check latest Laravel documentation (v12) for native Laravel support for ULID (in migration, validation, model trait) 
  - Create a new Laravel trait similar to `HasUuid` but using ULID (e.g. `HasUlid`) (if needed).
  - Update model factories, migrations, and any related seeding logic to use ULID.
  - Ensure compatibility with existing tools like Spatie Media Library, Horizon, etc.
  - Validate sortability and uniqueness of ULIDs across tenants and environments.
    - Migrations update PK types to `ulid()`.  
    - Factories use `Str::ulid()`.  

---

## 22. [] **Add Admin API Endpoints**
  - Introduce new API namespace: `/api/v1/admin/...`.
  - Allow full CRUD access to global models and dictionary tables outside of tenant scope.
  - Example resources: tenants, users, invoices, default project/task statuses, units, VAT rates, etc.
  - Ensure all admin endpoints are protected by proper authorization middleware (e.g. `is_admin`).
  - Use separate controllers or route groups to avoid conflicts with tenant-scoped logic.
  - Tag admin routes accordingly in OpenAPI documentation for visibility and clarity. 

---
## 22. Refresh Token Support in JWTAuth

### Goal
Implement full support for refresh tokens in a Laravel app using the `tymon/jwt-auth` package.

### Requirements

1. **TTL Configuration**:
   - Access token: 15 minutes
   - Refresh token: 7 days (10080 minutes)

2. **Endpoints**:
   - `POST /api/auth/login` — returns access token and refresh token
   - `POST /api/auth/refresh` — accepts refresh token and returns new access token
   - `POST /api/auth/logout` — invalidates current access token (and optionally the refresh token)

3. **Security**:
   - Refresh token should be stored on the client side (preferably in an HttpOnly cookie, or as a returned JSON field)
   - Stateless approach: no server-side storage of refresh tokens unless explicitly extended

4. **Middleware**:
   - Every request should validate the access token
   - Handle "token expired" errors with a clear path to refresh

### Extras
- Add unit and integration tests for login, refresh, and logout flows.
- Optionally create a dedicated service (e.g., `AuthService`) to encapsulate token logic.

### Definition of Done
- All endpoints work as described.
- Tokens are issued and refreshed respecting their TTLs.
- Tests cover the main use cases.
- API documentation updated (OpenAPI/Swagger or README). 

--

### Task: Implement Exchange and ExchangeRate Models with Read-Only Endpoints

**Goal:**  
Allow users to view currency exchange rates.

**Scope:**  
- Models:
  - `Exchange` – Represents a currency (e.g., USD, EUR).
  - `ExchangeRate` – Represents the rate for a specific day between two currencies.
- Relationships:
  - `ExchangeRate` belongs to `Exchange` (for both base and target currency).
- Fields:
  - `Exchange`: `id`, `code` (e.g., "USD"), `name` (e.g., "US Dollar")
  - `ExchangeRate`: `id`, `exchange_id`, `target_exchange_id`, `rate`, `date`
- Endpoints:
  - `GET /api/exchanges`
  - `GET /api/exchanges/{id}`
  - `GET /api/exchange-rates`
  - `GET /api/exchange-rates/{id}`
- Notes:
  - Read-only (no create/update/delete)
  - Optional: Seed with basic currencies and rates

**Definition of Done:**
- Models and migrations created
- Read-only API routes and controllers implemented
- Proper resource classes for JSON output
- Seeders for major currencies and sample rates

--

### Task: Create Invoice Numbering Template System

**Goal:**  
Allow tenants to define custom invoice numbering templates (e.g., `YYYY/NNN`, `INV-YYYY-MM/NNNN`).

**Scope:**  
- Model: `InvoiceNumberTemplate`
- Fields:
  - `id`, `tenant_id`, `invoice_type` (e.g., "sales", "proforma"), `template` (e.g., "YYYY/NNN")
- Logic:
  - Tokens to support: `YYYY`, `YY`, `MM`, `DD`, `NNN`, `NNNN`, etc.
  - Stored per tenant and per invoice type
- Endpoint:
  - `GET /api/invoice-number-templates` (list for current tenant)
  - Optional admin endpoint to define default templates
- Usage:
  - Will be used when generating new invoices

**Definition of Done:**
- Model and migration created
- Read-only endpoint showing current tenant templates
- Ability to support token parsing (future: during invoice creation)
- Unit test for template rendering logic (optional in this task)

---

## xx. [ ] LATER. Integrate OCR functionality using Tesseract for document text extraction

- **Goal**: Enable text extraction from images and PDFs (e.g., invoices, ID documents, scanned agreements) using the Tesseract OCR engine.

- **Use Cases**:
  - Extracting text from scanned invoices during import processes
  - Reading text from uploaded ID documents for contractors or users
  - Processing scanned agreements or other documents for data extraction

- **Subtasks**:
  - Install and configure Tesseract OCR on the server environment
  - Create a service class `OcrService` to handle image processing and text extraction
  - Implement support for common image formats (e.g., JPEG, PNG) and PDFs
  - Integrate OCR processing into relevant workflows (e.g., invoice import, document upload)
  - Store or display extracted text as needed for further processing or user review
  - Add configuration options for OCR processing (e.g., language selection, preprocessing steps)
  - Write unit and integration tests for OCR functionality 

--

### Invoice Model – Requirements Summary (Updated)

#### 1. Core Fields & Relationships
- `id`, `tenant_id`, `contractor_id`, `project_id` (optional)
- `employee_id`: user who issued the invoice
- `template_id`: foreign key to `InvoiceNumberTemplate`
- `issue_date`, `due_date`, `payment_date` (nullable)
- `invoice_number`: generated
- `status`: draft / issued / paid / overdue / canceled
- `currency`, `exchange_rate`
- `language_code`
- `notes`, `footer`, `terms_and_conditions`

#### 2. Line Items
- Product/service reference
- Quantity, unit, unit price
- VAT rate per line
- Line discount (optional)
- Description

#### 3. VAT & Tax
- Support for multiple VAT rates per invoice
- VAT-exempt support (e.g., reverse charge)
- Summary per VAT rate
- **[TODO] VIES API integration**: to validate EU VAT numbers

#### 4. Attachments
- File uploads: PDFs, images, related docs
- Link to generated PDF version
- Link to email confirmation or signed documents

#### 5. Payments
- `payment_status`: unpaid / partial / paid
- Amount paid, outstanding
- Payment log (optional, external or internal)
- Support for partial payments

#### 6. PDF Generation
- Custom layout per tenant
- Multi-language template support
- Branding: logo, colors, footer

#### 7. Automation & Workflow
- Auto-increment invoice number via template
- Drafts and scheduled creation
- Recurring invoices (future)
- Email invoice after status = `issued`, if enabled in preferences

#### 8. Compliance & Audit
- Lock after issuance (no edits allowed)
- No invoice number gaps (future validation)
- **Audit log:** handled by central **Action Log** mechanism

#### 9. Reporting
- Export to CSV/Excel
- Filters by date, contractor, status
- VAT summary reports
- Overdue invoices

#### 10. Miscellaneous
- Credit notes / corrections
- Proforma invoices
- Duplicate (create based on existing)
- Draft saving
- Custom fields (per tenant, JSON or relational) 