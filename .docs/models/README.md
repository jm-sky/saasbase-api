# SaaSBase – Data Model Index

This document provides a high-level overview of all models in the SaaSBase project. Each entry includes a brief description and a link to the full specification of the model, which includes attributes, relationships, behaviors, and relevant API endpoints or use cases.

---

## Core & Auth

- **[User](./User.md)**  
  Represents an authenticated user of the system. Supports email/password auth, 2FA, and OAuth integration.
- **[Tenant](./Tenant.md)**  
  An organization or team using the platform. Core of the multi-tenancy system.
- **[Role](./Role.md)**  
  Defines permissions and access levels within a tenant. Supports RBAC implementation.
- **[Permission](./Permission.md)**  
  Specific access rights assigned to roles. Granular control over system features.
- **[OAuthProvider](./OAuthProvider.md)**  
  Manages linked accounts via Google, GitHub, etc. Supports social login integration.
- **[TwoFactorAuth](./TwoFactorAuth.md)**  
  Manages 2FA settings and verification codes for users.
- **[PasswordReset](./PasswordReset.md)**  
  Handles password reset tokens and processes.

---

## Projects & Tasks

- **[Project](./Project.md)**  
  Represents a business or development initiative with JIRA-like capabilities.
- **[Task](./Task.md)**  
  A unit of work under a project. Supports priorities, statuses, and dependencies.
- **[Sprint](./Sprint.md)**  
  Groups tasks for agile project management.
- **[Comment](./Comment.md)**  
  User-provided discussion on tasks or other entities.
- **[Tag](./Tag.md)**  
  Labeling system for categorizing tasks and projects.
- **[Attachment](./Attachment.md)**  
  Files related to tasks, comments, etc.
- **[Skill](./Skill.md)**  
  Used to describe required or available skills.
- **[TimeEntry](./TimeEntry.md)**  
  Tracks time spent on tasks and projects.

---

## Invoicing & Finance

- **[Invoice](./Invoice.md)**  
  Document representing a sale, linked to products and contractors.
- **[InvoiceItem](./InvoiceItem.md)**  
  Individual line item on an invoice.
- **[Contractor](./Contractor.md)**  
  Represents a client or partner company with full contact management.
- **[Product](./Product.md)**  
  Goods or services offered in invoices.
- **[InvoiceNumberingTemplate](./InvoiceNumberingTemplate.md)**  
  Controls invoice numbering schemes per tenant.
- **[Exchange](./Exchange.md)**  
  Document of currency exchange with historical tracking.
- **[ExchangeRate](./ExchangeRate.md)**  
  Historical or live exchange rate info from external services.
- **[TaxRate](./TaxRate.md)**  
  Configurable tax rates with calculation rules.
- **[MeasurementUnit](./MeasurementUnit.md)**  
  Standard and custom units with conversion support.
- **[CompanyProfile](./CompanyProfile.md)**  
  Enhanced company information from lookup services.

---

## Offices & Services

- **[Office](./Office.md)**  
  Physical office locations within the system.
- **[OfficeAgreement](./OfficeAgreement.md)**  
  Agreements linking tenants to office usage periods.
- **[OfficeServiceType](./OfficeServiceType.md)**  
  Types of services provided by offices.
- **[OfficeTenantRelation](./OfficeTenantRelation.md)**  
  Links tenants with offices and service details.

---

## Employees

- **[Employee](./Employee.md)**  
  Represents a person hired by a tenant, optionally linked to a user.
- **[EmployeeAgreement](./EmployeeAgreement.md)**  
  Describes employment contract details such as dates and salary.

---

## System & Localization

- **[Country](./Country.md)**  
  List of countries for addresses or registration.
- **[VatRate](./VatRate.md)**  
  VAT rates applied per product or invoice.
- **[Unit](./Unit.md)**  
  Unit of measurement (kg, hour, piece, etc.) used by tenants.
- **[DictionaryEntry](./DictionaryEntry.md)**  
  Represents customizable values like statuses.
- **[AuditLog](./AuditLog.md)**  
  Tracks changes to system entities for compliance.

---

## Team Management

- **[Member](./Member.md)**  
  Represents a user within a tenant team.
- **[UserSetting](./UserSetting.md)**  
  Key-value settings per user including preferences and notifications.
- **[Notification](./Notification.md)**  
  Tracks system or user-triggered alerts.

---

## Model Conventions

All models follow these conventions:
- Implement `BelongsToTenant` trait for multi-tenancy support
- Use UUIDs as primary keys
- Support soft deletes
- Include audit logging
- Follow Laravel naming conventions
- Include proper PHPDoc documentation

For detailed implementation guidelines, see:
- [Tenant Scope Documentation](../.docs/handling-tenant-scope.md)
- [Database Schema](../architecture/database-schema.dbml)