# SaaSBass – Data Model Index

This document provides a high-level overview of all models in the SaaSBass project. Each entry includes a brief description and a link to the full specification of the model, which includes attributes, relationships, behaviors, and relevant API endpoints or use cases.

---

## Core & Auth

- **[User](./User.md)**  
  Represents an authenticated user of the system.
- **[Tenant](./Tenant.md)**  
  An organization or team using the platform.
- **[Role](./Role.md)**  
  Defines permissions and access levels within a tenant.
- **[Permission](./Permission.md)**  
  Specific access rights assigned to roles.
- **[OAuthProvider](./OAuthProvider.md)**  
  Manages linked accounts via Google, GitHub, etc.

---

## Projects & Tasks

- **[Project](./Project.md)**  
  Represents a business or development initiative.
- **[Task](./Task.md)**  
  A unit of work under a project.
- **[Comment](./Comment.md)**  
  User-provided discussion on tasks or other entities.
- **[Tag](./Tag.md)**  
  Labeling system for categorizing tasks.
- **[Attachment](./Attachment.md)**  
  Files related to tasks, comments, etc.
- **[Skill](./Skill.md)**  
  Used to describe required or available skills.

---

## Invoicing & Finance

- **[Invoice](./Invoice.md)**  
  Document representing a sale, linked to products and contractors.
- **[InvoiceItem](./InvoiceItem.md)**  
  Individual line item on an invoice.
- **[Contractor](./Contractor.md)**  
  Represents a client or partner company.
- **[Product](./Product.md)**  
  Goods or services offered in invoices.
- **[InvoiceNumberingTemplate](./InvoiceNumberingTemplate.md)**  
  Controls invoice numbering schemes.
- **[Exchange](./Exchange.md)**  
  Document of currency exchange.
- **[ExchangeRate](./ExchangeRate.md)**  
  Historical or live exchange rate info.

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

---

## Team Management

- **[Member](./Member.md)**  
  Represents a user within a tenant team.
- **[UserSetting](./UserSetting.md)**  
  Key-value settings per user.
- **[Notification](./Notification.md)**  
  Tracks system or user-triggered alerts.