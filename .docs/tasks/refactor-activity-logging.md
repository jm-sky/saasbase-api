# Refactor Activity Logging

> :white_check_mark: Status: Done

## Overview
We need to refactor the activity logging system to use our new `HasActivityLogging` trait. This will make the code more consistent, maintainable, and reduce duplication.

## Changes Required

### 1. Models to Add HasActivityLogging Trait
- [x] `app/Domain/Contractors/Models/Contractor.php`
- [x] `app/Domain/Contractors/Models/ContractorAddress.php`
- [x] `app/Domain/Contractors/Models/ContractorBankAccount.php`
- [x] `app/Domain/Contractors/Models/ContractorContactPerson.php`
- [x] `app/Domain/Products/Models/Product.php`
- [x] `app/Domain/Tenant/Models/Tenant.php`
- [x] `app/Domain/Common/Models/Comment.php`

### 2. Controllers to Update
- [x] `app/Domain/Contractors/Controllers/ContractorController.php`
- [x] `app/Domain/Contractors/Controllers/ContractorAddressController.php`
- [x] `app/Domain/Contractors/Controllers/ContractorBankAccountController.php`
- [x] `app/Domain/Contractors/Controllers/ContractorContactController.php`
- [x] `app/Domain/Contractors/Controllers/ContractorCommentsController.php`
- [x] `app/Domain/Products/Controllers/ProductController.php`
- [x] `app/Domain/Products/Controllers/ProductCommentsController.php`
- [x] `app/Domain/Products/Controllers/ProductTagsController.php`
- [x] `app/Domain/Tenant/Controllers/TenantController.php`
- [x] `app/Domain/Tenant/Controllers/TenantAddressController.php`
- [x] `app/Domain/Tenant/Controllers/TenantInvitationController.php`

### 3. Enums to Update
- [x] Add `label()` method to all activity type enums:
  - [x] `app/Domain/Contractors/Enums/ContractorActivityType.php`
  - [x] `app/Domain/Products/Enums/ProductActivityType.php`
  - [x] `app/Domain/Tenant/Enums/TenantActivityType.php`

## Implementation Steps

1. For each model:
   - Add `use HasActivityLogging;` trait
   - Remove existing activity logging code from `booted()` method
   - Update any direct activity logging calls to use the trait methods

2. For each controller:
   - Replace `activity()->performedOn()->withProperties()->event()->log()` chains with `logModelActivity()`
   - Use one-liner format for cleaner code
   - Remove redundant ID properties since they're handled by the trait

3. For each enum:
   - Add `label()` method returning human-readable descriptions
   - Use these labels in activity logging calls

## Example Changes

### Before
```php
activity()
    ->performedOn($contractor)
    ->withProperties([
        'tenant_id' => request()->user()?->getTenantId(),
        'contractor_id' => $contractor->id,
    ])
    ->event(ContractorActivityType::Created->value)
    ->log('Contractor created')
;
```

### After
```php
$contractor->logModelActivity(ContractorActivityType::Created->value, $contractor);
```

## Benefits
- Reduced code duplication
- Consistent activity logging across the application
- Better type safety with enum labels
- Cleaner, more maintainable code
- Automatic handling of foreign key names
