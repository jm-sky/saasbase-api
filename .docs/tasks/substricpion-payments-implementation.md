# Subscription Payments Implementation Plan

Implementation plan for `.docs/tasks/substricpion-payments.md`

## Domain Structure

The implementation should follow the existing domain-driven structure in app/Domain/. For the subscription payments feature, we'll create a new domain module:

```text
app/Domain/Subscription/
├── Actions/          # Business logic actions
├── Casts/            # Custom attribute casts
├── DTOs/             # Data Transfer Objects
├── Enums/            # Enumerations
├── Events/           # Domain events
├── Exceptions/       # Custom exceptions
├── Listeners/        # Event listeners
├── Models/           # Eloquent models
├── Notifications/    # Notification classes
├── Policies/         # Authorization policies
├── Requests/         # Form requests
├── Resources/        # API resources
├── Rules/            # Custom validation rules
├── Services/         # Domain services
└── Traits/           # Reusable traits
```

**Key files to implement:**
- [x] Models/
    - [x] BillingCustomer.php
    - [x] BillingInfo.php
    - [x] SubscriptionPlan.php
    - [x] Subscription.php
    - [x] AddonPackage.php
    - [x] AddonPurchase.php
    - [x] SubscriptionInvoice.php
- [x] Services/
    - [x] StripeCustomerService.php
    - [x] StripeSubscriptionService.php
    - [x] StripeAddonService.php
    - [x] StripeInvoiceService.php
- [x] Actions/
    - [x] CreateSubscriptionAction.php
    - [x] UpdateSubscriptionAction.php
    - [x] CancelSubscriptionAction.php
    - [x] PurchaseAddonAction.php
- [ ] Events/
    - [ ] SubscriptionCreated.php  # TODO: NotImplementedException
    - [ ] SubscriptionUpdated.php  # TODO: NotImplementedException
    - [ ] SubscriptionCancelled.php  # TODO: NotImplementedException
    - [ ] AddonPurchased.php  # TODO: NotImplementedException
- [ ] Listeners/
    - [ ] HandleStripeWebhook.php  # TODO: NotImplementedException
    - [ ] SendSubscriptionNotification.php  # TODO: NotImplementedException
    - [ ] UpdateBillingStatus.php  # TODO: NotImplementedException
- [ ] Policies/
    - [ ] SubscriptionPolicy.php  # TODO: NotImplementedException
    - [ ] AddonPolicy.php  # TODO: NotImplementedException
- [x] Requests/
    - [x] StoreSubscriptionRequest.php
    - [x] UpdateSubscriptionRequest.php
    - [x] PurchaseAddonRequest.php
- [ ] Resources/
    - [ ] SubscriptionResource.php  # TODO: NotImplementedException
    - [ ] AddonResource.php  # TODO: NotImplementedException
    - [ ] InvoiceResource.php  # TODO: NotImplementedException
- [ ] DTOs/
    - [ ] SubscriptionData.php  # TODO: NotImplementedException
    - [ ] BillingInfoData.php  # TODO: NotImplementedException
    - [ ] AddonPurchaseData.php  # TODO: NotImplementedException
- [ ] Enums/
    - [ ] SubscriptionStatus.php  # TODO: NotImplementedException
    - [ ] AddonType.php  # TODO: NotImplementedException
    - [ ] BillingInterval.php  # TODO: NotImplementedException

## 1. Database Schema Updates

### New Tables
- [x] billing_customers
    - [x] UUID primary key
    - [x] Polymorphic billable relationship (uuidMorphs) (User/Tenant)
    - [x] Stripe customer ID
    - [x] Timestamps
- [x] billing_info
    - [x] UUID primary key
    - [x] Polymorphic billable relationship (uuidMorphs)
    - [x] Billing address fields
    - [x] Tax/VAT information
    - [x] Email and notes
    - [x] Timestamps
- [x] subscription_plans
    - [x] UUID primary key
    - [x] Name and description
    - [x] Stripe product and price IDs
    - [x] Interval (monthly/yearly)
    - [x] Price (display)
    - [x] Features (JSON)
    - [x] Timestamps
- [x] subscriptions
    - [x] UUID primary key
    - [x] Polymorphic billable relationship (uuidMorphs)
    - [x] Plan reference
    - [x] Stripe subscription ID
    - [x] Status and period tracking
    - [x] Timestamps
- [x] addon_packages
    - [x] UUID primary key
    - [x] Name and description
    - [x] Stripe price ID
    - [x] Type (one-time/recurring)
    - [x] Price (display)
    - [x] Timestamps
- [x] addon_purchases
    - [x] UUID primary key
    - [x] Polymorphic billable relationship (uuidMorphs)
    - [x] Package reference
    - [x] Stripe invoice item ID
    - [x] Purchase and expiry dates
    - [x] Timestamps
- [x] subscription_invoices
    - [x] UUID primary key
    - [x] Polymorphic billable relationship (uuidMorphs)
    - [x] Stripe invoice ID
    - [x] Amount and status
    - [x] URLs for hosted invoice and PDF
    - [x] Issue and payment dates
    - [x] Timestamps

## 2. Stripe Integration

### Configuration
- [ ] Add Stripe configuration to `.env`:
    - [ ] STRIPE_KEY  # TODO: NotImplementedException
    - [ ] STRIPE_SECRET  # TODO: NotImplementedException
    - [ ] STRIPE_WEBHOOK_SECRET  # TODO: NotImplementedException
- [ ] Create Stripe service provider:
    - [ ] Register Stripe client  # TODO: NotImplementedException
    - [ ] Configure webhook handling  # TODO: NotImplementedException
    - [ ] Set up error handling  # TODO: NotImplementedException

### Core Services
- [x] StripeCustomerService
    - [ ] Create/update Stripe customers  # TODO: NotImplementedException
    - [ ] Manage billing information  # TODO: NotImplementedException
    - [ ] Handle customer deletion  # TODO: NotImplementedException
- [x] StripeSubscriptionService
    - [ ] Create/manage subscriptions  # TODO: NotImplementedException
    - [ ] Handle plan changes  # TODO: NotImplementedException
    - [ ] Process cancellations  # TODO: NotImplementedException
    - [ ] Manage trial periods  # TODO: NotImplementedException
- [x] StripeAddonService
    - [ ] Process one-time purchases  # TODO: NotImplementedException
    - [ ] Handle recurring addons  # TODO: NotImplementedException
    - [ ] Manage addon expiration  # TODO: NotImplementedException
- [x] StripeInvoiceService
    - [ ] Sync Stripe invoices  # TODO: NotImplementedException
    - [ ] Generate PDFs  # TODO: NotImplementedException
    - [ ] Handle payment status  # TODO: NotImplementedException

## 3. API Endpoints

### Customer Management
- [ ] POST /api/v1/billing/customers
    - [ ] Create Stripe customer  # TODO: NotImplementedException
    - [ ] Link to User/Tenant  # TODO: NotImplementedException
- [ ] PUT /api/v1/billing/customers/{id}
    - [ ] Update billing information  # TODO: NotImplementedException
    - [ ] Modify Stripe customer  # TODO: NotImplementedException

### Subscription Management
- [x] GET /api/v1/subscription-plans
    - [x] List available plans
    - [x] Include pricing and features
- [x] POST /api/v1/subscriptions
    - [x] Create new subscription
    - [ ] Handle trial periods  # TODO: NotImplementedException
- [x] PUT /api/v1/subscriptions/{id}
    - [x] Update subscription
    - [ ] Change plans  # TODO: NotImplementedException
    - [ ] Modify billing cycle  # TODO: NotImplementedException
- [x] DELETE /api/v1/subscriptions/{id}
    - [x] Cancel subscription
    - [ ] Handle immediate/end-of-period  # TODO: NotImplementedException

### Addon Management
- [x] GET /api/v1/addon-packages
    - [x] List available addons
    - [x] Show pricing and details
- [x] POST /api/v1/addon-purchases
    - [x] Purchase addon
    - [ ] Handle one-time/recurring  # TODO: NotImplementedException
- [x] GET /api/v1/addon-purchases
    - [x] List active addons
    - [x] Show expiration dates

### Invoice Management
- [x] GET /api/v1/subscription-invoices
    - [x] List invoices
    - [x] Filter by status/date
- [x] GET /api/v1/subscription-invoices/{id}
    - [x] Get invoice details
    - [x] Access PDF/hosted URL

## 4. Webhook Handling

### Required Webhooks
- [ ] customer.subscription.created
    - [ ] Create local subscription  # TODO: NotImplementedException
    - [ ] Set up trial period  # TODO: NotImplementedException
- [ ] customer.subscription.updated
    - [ ] Update subscription status  # TODO: NotImplementedException
    - [ ] Handle plan changes  # TODO: NotImplementedException
- [ ] customer.subscription.deleted
    - [ ] Mark subscription as cancelled  # TODO: NotImplementedException
    - [ ] Handle cleanup  # TODO: NotImplementedException
- [ ] invoice.created
    - [ ] Create local invoice  # TODO: NotImplementedException
    - [ ] Set up payment tracking  # TODO: NotImplementedException
- [ ] invoice.paid
    - [ ] Update invoice status  # TODO: NotImplementedException
    - [ ] Trigger post-payment actions  # TODO: NotImplementedException
- [ ] invoice.payment_failed
    - [ ] Handle failed payments  # TODO: NotImplementedException
    - [ ] Send notifications  # TODO: NotImplementedException

## 5. Frontend Components

### Subscription Management
1. Plan Selection
   - Display available plans  # TODO: NotImplementedException
   - Show features and pricing  # TODO: NotImplementedException
   - Handle plan comparison  # TODO: NotImplementedException

2. Subscription Dashboard
   - Show current plan  # TODO: NotImplementedException
   - Display usage metrics  # TODO: NotImplementedException
   - Manage addons  # TODO: NotImplementedException

3. Billing Information
   - Edit billing details  # TODO: NotImplementedException
   - Update payment methods  # TODO: NotImplementedException
   - View billing history  # TODO: NotImplementedException

### Addon Management
1. Addon Catalog
   - Browse available addons  # TODO: NotImplementedException
   - View pricing and details  # TODO: NotImplementedException
   - Purchase flow  # TODO: NotImplementedException

2. Active Addons
   - List purchased addons  # TODO: NotImplementedException
   - Show expiration dates  # TODO: NotImplementedException
   - Manage recurring addons  # TODO: NotImplementedException

### Invoice Management
1. Invoice List
   - View all invoices  # TODO: NotImplementedException
   - Filter and search  # TODO: NotImplementedException
   - Download PDFs  # TODO: NotImplementedException

2. Invoice Details
   - View invoice details  # TODO: NotImplementedException
   - Access hosted invoice  # TODO: NotImplementedException
   - Payment status  # TODO: NotImplementedException

## 6. Testing Strategy

### Unit Tests
1. Service Layer
   - Stripe service mocks  # TODO: NotImplementedException
   - Business logic validation  # TODO: NotImplementedException
   - Error handling  # TODO: NotImplementedException

2. Model Tests
   - Relationship validation  # TODO: NotImplementedException
   - Attribute casting  # TODO: NotImplementedException
   - Business rules  # TODO: NotImplementedException

### Integration Tests
1. API Endpoints
   - Request validation  # TODO: NotImplementedException
   - Response formatting  # TODO: NotImplementedException
   - Error handling  # TODO: NotImplementedException

2. Stripe Integration
   - Webhook handling  # TODO: NotImplementedException
   - Customer management  # TODO: NotImplementedException
   - Subscription lifecycle  # TODO: NotImplementedException

### Feature Tests
1. Subscription Flow
   - Plan selection  # TODO: NotImplementedException
   - Payment processing  # TODO: NotImplementedException
   - Status updates  # TODO: NotImplementedException

2. Addon Flow
   - Purchase process  # TODO: NotImplementedException
   - Expiration handling  # TODO: NotImplementedException
   - Recurring management  # TODO: NotImplementedException

## 7. Documentation

### API Documentation
1. Endpoint Specifications
   - Request/response formats  # TODO: NotImplementedException
   - Authentication  # TODO: NotImplementedException
   - Error codes  # TODO: NotImplementedException

2. Webhook Documentation
   - Event types  # TODO: NotImplementedException
   - Payload structure  # TODO: NotImplementedException
   - Testing setup  # TODO: NotImplementedException

### User Documentation
1. Subscription Guide
   - Plan selection  # TODO: NotImplementedException
   - Billing setup  # TODO: NotImplementedException
   - Management tips  # TODO: NotImplementedException

2. Addon Guide
   - Available addons  # TODO: NotImplementedException
   - Purchase process  # TODO: NotImplementedException
   - Usage instructions  # TODO: NotImplementedException

## 8. Deployment Checklist

### Pre-deployment
1. Stripe Configuration
   - API keys  # TODO: NotImplementedException
   - Webhook endpoints  # TODO: NotImplementedException
   - Test mode setup  # TODO: NotImplementedException

2. Database Migration
   - Schema updates  # TODO: NotImplementedException
   - Data migration  # TODO: NotImplementedException
   - Index optimization  # TODO: NotImplementedException

### Post-deployment
1. Monitoring
   - Error tracking  # TODO: NotImplementedException
   - Webhook monitoring  # TODO: NotImplementedException
   - Performance metrics  # TODO: NotImplementedException

2. Backup Strategy
   - Database backups  # TODO: NotImplementedException
   - Stripe data sync  # TODO: NotImplementedException
   - Recovery procedures  # TODO: NotImplementedException
