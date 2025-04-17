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

## 2. [ ] Add seeders for all models. I need seeder for skill categories and skills, let's start with IT area. I want to have a demo with few users, few tenants, some contractors, products, projects & tasks.

- **Subtasks**:
  - Create seeder for skill categories and skills (IT area).
  - Create seeder for users (demo users, include users with different roles).
  - Create seeder for tenants (few tenants).
  - Create seeder for contractors (few contractors).
  - Create seeder for products (demo products).
  - Create seeder for projects (few demo projects).
  - Create seeder for tasks (few tasks related to projects).
  - Create seeder for Vat rates - use Polish vat rates as example.

---

## 3. [ ] Add countries to Country seeders JSON file (European countries, and most large countries).
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
  - Implement action for changing language preference.
  - Implement validation for user settings actions.

---

## 5. [ ] Add trait (BelongsToTenant) that applies a global scope for models with `tenant_id`. We'll store `tenant_id` in session or JWT for security.  
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

## 7. [ ] Implement standardized filtering and sorting with Spatie Query Builder to index method in all CRUD controllers. 

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

## 8. [ ] Implement user settings and preferences as part of the user profile.

- **Subtasks**:
  - Implement a user settings model to store preferences (e.g., language, timezone, notification preferences).
  - Create actions for updating user settings (language, timezone, etc.).
  - Update user profile interface to allow settings modification.
  - Add validation for user settings actions.
  - Create migration for user settings table.
  - Add tests for user settings actions.

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