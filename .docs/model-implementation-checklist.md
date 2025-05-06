# Model Implementation Checklist

This document tracks the implementation status of our models and highlights any differences between documentation and implementation.

## Core Models

- [x] User
  - [x] Implementation in Domain/Auth/Models/User.php
  - [x] Relationships verified (settings, addresses, bankAccounts, etc.)
  - [x] Attributes match schema
  - [x] Spatie Media Library integration for avatar
  - [x] JWT integration
  - [x] Two-factor authentication support
  - [x] Email verification

- [x] UserSettings
  - [x] Implementation in Domain/Auth/Models/UserSettings.php
  - [x] JSON schema for settings verified
  - [x] Two-factor settings
  - [x] Default preferences

- [x] UserOAuthAccount
  - [x] Implementation in Domain/Auth/Models/OAuthAccount.php
  - [x] Provider support verified

- [x] Address (Polymorphic)
  - [x] Implementation in Domain/Common/Models/Address.php
  - [x] Polymorphic relationships working
  - [x] Tenant_id null for user addresses
  - [x] Specialized classes (UserAddress, TenantAddress)

- [ ] BankAccount (Polymorphic)
  - [ ] Implementation needs verification
  - [ ] Verify polymorphic relationships
  - [ ] Test tenant_id null for user accounts

## Organization Models

- [x] Tenant
  - [x] Implementation in Domain/Tenant/Models/Tenant.php
  - [x] Relationships set up (users, addresses)
  - [x] Multi-tenant support working
  - [x] Table migration exists
  - [x] Soft deletes enabled
  - [x] UUID support

- [ ] OrganizationUnit
  - [ ] Implementation missing
  - [ ] Migration needed

## Project Management Models

- [x] Project
  - [x] Implementation in Domain/Projects/Models/Project.php
  - [x] Relationships set up (users, tasks, comments, attachments)
  - [x] Status workflow implemented (ProjectStatus model)
  - [x] Multi-tenant support
  - [x] Soft deletes enabled
  - [x] Table migration exists

- [x] Task
  - [x] Implementation in Domain/Projects/Models/Task.php
  - [x] Relationships set up (project, assignee, watchers)
  - [x] Status and priority handling
  - [x] Comments and attachments support
  - [x] Multi-tenant support
  - [x] Table migration exists

## Contractor Models

- [x] Contractor
  - [x] Implementation in Domain/Contractors/Models/Contractor.php
  - [x] Polymorphic relationships (addresses)
  - [x] Multi-tenant support
  - [x] Soft deletes enabled
  - [x] Buyer/Supplier flags
  - [x] Table migration exists
  - [x] Factory implemented

## Product Models

- [x] Product
  - [x] Implementation in Domain/Products/Models/Product.php
  - [x] Relationships with Unit and VatRate set up
  - [x] Multi-tenant support
  - [x] Soft deletes enabled
  - [x] Factory implemented
  - [x] Price handling with decimal casting

- [x] VatRate
  - [x] Implementation in Domain/Common/Models/VatRate.php
  - [x] Basic attributes (name, rate)
  - [x] Decimal casting for rate
  - [x] Relationship with Product model
  - [x] Table migration exists
  - [x] Seeder implemented with test cases
  - [ ] API endpoints needed
  - [ ] DTO implementation needed

- [x] MeasurementUnit
  - [x] Implementation in Domain/Common/Models/MeasurementUnit.php
  - [x] Basic attributes (code, name)
  - [x] Relationship with Product model
  - [x] Table migration exists

## Exchange Models

- [x] Exchange
  - [x] Implementation in Domain/Exchanges/Models/Exchange.php
  - [x] Basic attributes (name, currency)
  - [x] Relationship with ExchangeRate set up
  - [x] Table migration exists
  - [x] Controller and DTOs implemented
  - [x] API endpoints working

- [x] ExchangeRate
  - [x] Implementation in Domain/Exchanges/Models/ExchangeRate.php
  - [x] Attributes (date, rate, table, source)
  - [x] Relationship with Exchange model
  - [x] Table migration exists
  - [x] Import service implemented
  - [x] NBP integration ready

## Chat Models

- [ ] ChatRoom
  - [ ] Implementation missing
  - [ ] Migration needed

- [ ] ChatMessage
  - [ ] Implementation missing
  - [ ] Migration needed

- [ ] ChatParticipant
  - [ ] Implementation missing
  - [ ] Migration needed

## Common Models

- [x] Country
  - [x] Implementation in Domain/Common/Models/Country.php
  - [x] Comprehensive country data fields
  - [x] ISO codes (2-letter, 3-letter, numeric)
  - [x] Currency information
  - [x] Regional data
  - [x] Emoji support

## Missing Models

The following models are documented but not yet implemented:

1. TimeEntry
2. Notification (using Laravel's native notification system)
3. SubscriptionPlan
4. Subscription
5. Invoice
6. Payment
7. PriceList
8. Discount

## Notes

1. BankAccount model needs to be implemented:
   - Schema exists in database-schema.dbml
   - Documentation exists but no model implementation found
   - Should support polymorphic relationships with User, Contractor, Tenant

2. Media implementation:
   - Successfully using Spatie Media Library
   - Configuration in place for S3 storage
   - Image conversions working
   - Profile image handling implemented

3. Address implementation:
   - Base polymorphic model working
   - Extended by specific models (User, Contractor, Tenant)
   - Proper tenant scoping implemented

4. Notification system:
   - Using Laravel's native notification system
   - No custom model needed
   - Documentation updated to reflect this
