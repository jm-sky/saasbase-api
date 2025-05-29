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
- [ ] Models/
    - [ ] BillingCustomer.php
    - [ ] BillingInfo.php
    - [ ] SubscriptionPlan.php
    - [ ] Subscription.php
    - [ ] AddonPackage.php
    - [ ] AddonPurchase.php
    - [ ] SubscriptionInvoice.php
- [ ] Services/
    - [ ] StripeCustomerService.php
    - [ ] StripeSubscriptionService.php
    - [ ] StripeAddonService.php
    - [ ] StripeInvoiceService.php
- [ ] Actions/
    - [ ] CreateSubscriptionAction.php
    - [ ] UpdateSubscriptionAction.php
    - [ ] CancelSubscriptionAction.php
    - [ ] PurchaseAddonAction.php
- [ ] Events/
    - [ ] SubscriptionCreated.php
    - [ ] SubscriptionUpdated.php
    - [ ] SubscriptionCancelled.php
    - [ ] AddonPurchased.php
- [ ] Listeners/
    - [ ] HandleStripeWebhook.php
    - [ ] SendSubscriptionNotification.php
    - [ ] UpdateBillingStatus.php
- [ ] Policies/
    - [ ] SubscriptionPolicy.php
    - [ ] AddonPolicy.php
- [ ] Requests/
    - [ ] StoreSubscriptionRequest.php
    - [ ] UpdateSubscriptionRequest.php
    - [ ] PurchaseAddonRequest.php
- [ ] Resources/
    - [ ] SubscriptionResource.php
    - [ ] AddonResource.php
    - [ ] InvoiceResource.php
- [ ] DTOs/
    - [ ] SubscriptionData.php
    - [ ] BillingInfoData.php
    - [ ] AddonPurchaseData.php
- [ ] Enums/
    - [ ] SubscriptionStatus.php
    - [ ] AddonType.php
    - [ ] BillingInterval.php

## 1. Database Schema Updates

### New Tables
- [ ] billing_customers
    - [ ] UUID primary key
    - [ ] Polymorphic billable relationship (uuidMorphs) (User/Tenant)
    - [ ] Stripe customer ID
    - [ ] Timestamps
- [ ] billing_info
    - [ ] UUID primary key
    - [ ] Polymorphic billable relationship (uuidMorphs)
    - [ ] Billing address fields
    - [ ] Tax/VAT information
    - [ ] Email and notes
    - [ ] Timestamps
- [ ] subscription_plans
    - [ ] UUID primary key
    - [ ] Name and description
    - [ ] Stripe product and price IDs
    - [ ] Interval (monthly/yearly)
    - [ ] Price (display)
    - [ ] Features (JSON)
    - [ ] Timestamps
- [ ] subscriptions
    - [ ] UUID primary key
    - [ ] Polymorphic billable relationship (uuidMorphs)
    - [ ] Plan reference
    - [ ] Stripe subscription ID
    - [ ] Status and period tracking
    - [ ] Timestamps
- [ ] addon_packages
    - [ ] UUID primary key
    - [ ] Name and description
    - [ ] Stripe price ID
    - [ ] Type (one-time/recurring)
    - [ ] Price (display)
    - [ ] Timestamps
- [ ] addon_purchases
    - [ ] UUID primary key
    - [ ] Polymorphic billable relationship (uuidMorphs)
    - [ ] Package reference
    - [ ] Stripe invoice item ID
    - [ ] Purchase and expiry dates
    - [ ] Timestamps
- [ ] subscription_invoices
    - [ ] UUID primary key
    - [ ] Polymorphic billable relationship (uuidMorphs)
    - [ ] Stripe invoice ID
    - [ ] Amount and status
    - [ ] URLs for hosted invoice and PDF
    - [ ] Issue and payment dates
    - [ ] Timestamps

## 2. Stripe Integration

### Configuration
- [ ] Add Stripe configuration to `.env`:
    - [ ] STRIPE_KEY
    - [ ] STRIPE_SECRET
    - [ ] STRIPE_WEBHOOK_SECRET
- [ ] Create Stripe service provider:
    - [ ] Register Stripe client
    - [ ] Configure webhook handling
    - [ ] Set up error handling

### Core Services
- [ ] StripeCustomerService
    - [ ] Create/update Stripe customers
    - [ ] Manage billing information
    - [ ] Handle customer deletion
- [ ] StripeSubscriptionService
    - [ ] Create/manage subscriptions
    - [ ] Handle plan changes
    - [ ] Process cancellations
    - [ ] Manage trial periods
- [ ] StripeAddonService
    - [ ] Process one-time purchases
    - [ ] Handle recurring addons
    - [ ] Manage addon expiration
- [ ] StripeInvoiceService
    - [ ] Sync Stripe invoices
    - [ ] Generate PDFs
    - [ ] Handle payment status

## 3. API Endpoints

### Customer Management
- [ ] POST /api/v1/billing/customers
    - [ ] Create Stripe customer
    - [ ] Link to User/Tenant
- [ ] PUT /api/v1/billing/customers/{id}
    - [ ] Update billing information
    - [ ] Modify Stripe customer

### Subscription Management
- [ ] GET /api/v1/subscription-plans
    - [ ] List available plans
    - [ ] Include pricing and features
- [ ] POST /api/v1/subscriptions
    - [ ] Create new subscription
    - [ ] Handle trial periods
- [ ] PUT /api/v1/subscriptions/{id}
    - [ ] Update subscription
    - [ ] Change plans
    - [ ] Modify billing cycle
- [ ] DELETE /api/v1/subscriptions/{id}
    - [ ] Cancel subscription
    - [ ] Handle immediate/end-of-period

### Addon Management
- [ ] GET /api/v1/addon-packages
    - [ ] List available addons
    - [ ] Show pricing and details
- [ ] POST /api/v1/addon-purchases
    - [ ] Purchase addon
    - [ ] Handle one-time/recurring
- [ ] GET /api/v1/addon-purchases
    - [ ] List active addons
    - [ ] Show expiration dates

### Invoice Management
- [ ] GET /api/v1/subscription-invoices
    - [ ] List invoices
    - [ ] Filter by status/date
- [ ] GET /api/v1/subscription-invoices/{id}
    - [ ] Get invoice details
    - [ ] Access PDF/hosted URL

## 4. Webhook Handling

### Required Webhooks
- [ ] customer.subscription.created
    - [ ] Create local subscription
    - [ ] Set up trial period
- [ ] customer.subscription.updated
    - [ ] Update subscription status
    - [ ] Handle plan changes
- [ ] customer.subscription.deleted
    - [ ] Mark subscription as cancelled
    - [ ] Handle cleanup
- [ ] invoice.created
    - [ ] Create local invoice
    - [ ] Set up payment tracking
- [ ] invoice.paid
    - [ ] Update invoice status
    - [ ] Trigger post-payment actions
- [ ] invoice.payment_failed
    - [ ] Handle failed payments
    - [ ] Send notifications

## 5. Frontend Components

### Subscription Management
1. Plan Selection
   - Display available plans
   - Show features and pricing
   - Handle plan comparison

2. Subscription Dashboard
   - Show current plan
   - Display usage metrics
   - Manage addons

3. Billing Information
   - Edit billing details
   - Update payment methods
   - View billing history

### Addon Management
1. Addon Catalog
   - Browse available addons
   - View pricing and details
   - Purchase flow

2. Active Addons
   - List purchased addons
   - Show expiration dates
   - Manage recurring addons

### Invoice Management
1. Invoice List
   - View all invoices
   - Filter and search
   - Download PDFs

2. Invoice Details
   - View invoice details
   - Access hosted invoice
   - Payment status

## 6. Testing Strategy

### Unit Tests
1. Service Layer
   - Stripe service mocks
   - Business logic validation
   - Error handling

2. Model Tests
   - Relationship validation
   - Attribute casting
   - Business rules

### Integration Tests
1. API Endpoints
   - Request validation
   - Response formatting
   - Error handling

2. Stripe Integration
   - Webhook handling
   - Customer management
   - Subscription lifecycle

### Feature Tests
1. Subscription Flow
   - Plan selection
   - Payment processing
   - Status updates

2. Addon Flow
   - Purchase process
   - Expiration handling
   - Recurring management

## 7. Documentation

### API Documentation
1. Endpoint Specifications
   - Request/response formats
   - Authentication
   - Error codes

2. Webhook Documentation
   - Event types
   - Payload structure
   - Testing setup

### User Documentation
1. Subscription Guide
   - Plan selection
   - Billing setup
   - Management tips

2. Addon Guide
   - Available addons
   - Purchase process
   - Usage instructions

## 8. Deployment Checklist

### Pre-deployment
1. Stripe Configuration
   - API keys
   - Webhook endpoints
   - Test mode setup

2. Database Migration
   - Schema updates
   - Data migration
   - Index optimization

### Post-deployment
1. Monitoring
   - Error tracking
   - Webhook monitoring
   - Performance metrics

2. Backup Strategy
   - Database backups
   - Stripe data sync
   - Recovery procedures
