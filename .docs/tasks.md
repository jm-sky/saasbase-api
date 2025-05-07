# Tasks

## [ ] Refactor foreign keys. i.e. refer to country code (pl, de) instead of id.  
- **Note**: It may not be more efficient, but we would immediately see the country name instead of an anonymous ID.
- **Subtasks**:
  - Review foreign key usage for countries.
  - Replace country IDs with country codes (pl, de) in relevant models.
  - Update migrations for country references.
  - Review and update the database schema if necessary.
  - Test functionality to ensure foreign key relations are properly handled with country codes.

---

## [ ] Add multi-language support for user interface.

- **Subtasks**:
  - Implement language files for UI translations.
  - Set up language switching feature in the UI.
  - Ensure all UI strings are translatable.
  - Test multi-language support across the application.

---

## [ ] Refactor dictionary-like tables (e.g. VAT rates) to use meaningful string primary keys

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

## [ ] Generate PDF files from invoices

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

## [ ] Implement task-based notifications for users (e.g., for project deadlines, task assignments).

- **Subtasks**:
  - Define task-related notifications.
  - Set up notification system using Laravel Notifications.
  - Trigger notifications based on task events (task created, updated, etc.).
  - Allow users to customize notification preferences.
  - Add tests for notification functionality.

---

## [ ] Handle notifications for project/task updates and invoices waiting for payment

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


## [ ] Implement tenant-specific measurement units for invoice items (e.g. hour, day, km)

- **Goal**: Allow tenants to manage their own units of measurement (e.g., hours, kilometers), with admin-managed defaults categorized by type.

- **Subtasks**:
  - [x] Create `MeasurementUnit` model with the following fields:
    - `tenant_id` (**required**)
    - `name` (e.g. "hour")
    - `shortcut` (e.g. "h")
    - `category` (e.g. "time", "length", "energy")
    - `is_default` (boolean to distinguish system defaults)
  - [ ] Seed default units (e.g., hour, day, km, liter) categorized properly.
  - [ ] On tenant creation, copy default units to tenant with `is_default = true`.
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

## [] **Invitation System**
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

## [] **Generate OpenAPI YAML Specification**
  - Automatically generate OpenAPI documentation in YAML format for the entire API.
  - Include all endpoints, models, request/response schemas, authentication details.
  - Ensure compatibility with tools like Swagger UI and Postman.
  - Preferably automate via Artisan command or during CI build.

---

### [] Task: Create Invoice Numbering Template System

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

## [ ] Integrate OCR functionality using Tesseract for document text extraction

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

--

### [] Task: Implement Contractor Preferences

**Objective:** Enable the system to store and manage contractor-specific preferences for language, currency exchange rate, invoice format, and payment method.

#### Preferences:
1. **Language**: Store the contractor's preferred language for communication and documents (e.g., Polish, English).
2. **Currency Exchange**: Set the contractor's preferred currency for invoicing and transactions (e.g., PLN, EUR, USD).
3. **Invoice Format**: Allow the contractor's default invoice format to be selected (e.g., PDF, HTML).
4. **Payment Method**: Store the preferred payment method for transactions (e.g., Bank Transfer, PayPal, Credit Card).

**Definition of Done:**
- Contractor preferences are stored and can be updated.
- Preferences are applied to invoices and financial transactions.
- Changes can be easily made via a UI interface.

---

### [] Task: Integrate Exchange Rates from NBP API

**Objective:** Integrate exchange rate data from Polish NBP for daily exchange rate import.

#### Subtasks:
1. **Data Source Integration**: 
   - [ ] Integrate the Polish NBP API to fetch exchange rates.
   
2. **Daily Import**: 
   - Set up a scheduled task (e.g., using Laravel Scheduler) to import exchange rates every day.
   - Store the exchange rates in the database, linked to each currency.

3. **Error Handling**: 
   - Implement fallback logic if one of the APIs fails to return valid data (e.g., use the second source).
   - Notify admins if data import fails for more than one day.

4. **Use Case**: 
   - Use exchange rates for contractor currency preferences and invoices.
   - Ensure that the system can convert between currencies using the latest available rates.

**Definition of Done:**
- Exchange rates are automatically fetched from at least one source daily.
- Rates are stored correctly and used for currency conversion.
- API failure is handled gracefully with fallback options. 