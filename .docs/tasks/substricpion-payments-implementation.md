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
    - [x] Feature.php
    - [x] PlanFeature.php
- [x] Enums/
    - [x] FeatureName.php
    - [x] SubscriptionStatus.php
    - [x] AddonType.php
    - [x] BillingInterval.php
- [x] Services/
    - [x] StripeCustomerService.php
    - [x] StripeSubscriptionService.php
    - [x] StripeAddonService.php
    - [~] StripeInvoiceService.php (Partially implemented)
- [~] Actions/
    - [x] CreateSubscriptionAction.php
    - [x] UpdateSubscriptionAction.php
    - [x] CancelSubscriptionAction.php
    - [x] PurchaseAddonAction.php
- [x] Events/
    - [x] SubscriptionCreated.php
    - [x] SubscriptionUpdated.php
    - [x] SubscriptionCancelled.php
    - [x] AddonPurchased.php
- [~] Listeners/
    - [x] HandleStripeWebhook.php
    - [ ] SendSubscriptionNotification.php
    - [ ] UpdateBillingStatus.php
- [ ] Policies/
    - [ ] SubscriptionPolicy.php
    - [ ] AddonPolicy.php
- [x] Requests/
    - [x] StoreSubscriptionRequest.php
    - [x] UpdateSubscriptionRequest.php
    - [x] PurchaseAddonRequest.php
- [x] Resources/
    - [x] SubscriptionResource.php
    - [x] AddonResource.php
    - [x] InvoiceResource.php
    - [x] AddonPurchaseResource.php
- [ ] DTOs/
    - [ ] SubscriptionData.php
    - [ ] BillingInfoData.php
    - [ ] AddonPurchaseData.php

## 1. Database Schema Updates ✅

### New Tables
- [x] billing_customers
- [x] billing_info
- [x] subscription_plans
- [x] features
- [x] plan_features
- [x] subscriptions
- [x] addon_packages
- [x] addon_purchases
- [x] subscription_invoices

## 2. Features Migration ✅

### Step 1: Create New Tables ✅
- [x] Create migration for `features` table
- [x] Create migration for `plan_features` table
- [x] Add proper indexes and constraints

### Step 2: Data Migration ✅
- [x] Create seeder for core features
- [x] Create seeder for plan features
- [x] Write migration script to move data from JSON to relations

### Step 3: Code Updates ✅
- [x] Create Feature and PlanFeature models
- [x] Update SubscriptionPlan model to use new relationship
- [x] Add helper methods for feature access
- [x] Update existing code to use new feature system
- [x] Mark old features field as deprecated

## 3. Stripe Integration 🔄

### Configuration ✅
- [x] Add Stripe configuration to `.env`:
    - [x] STRIPE_KEY
    - [x] STRIPE_SECRET
    - [x] STRIPE_WEBHOOK_SECRET
- [x] Create Stripe service provider:
    - [x] Register Stripe client
    - [x] Configure webhook handling
    - [x] Set up error handling

### Core Services 🔄
- [x] StripeCustomerService
    - [x] Create/update Stripe customers
    - [x] Manage billing information
    - [x] Handle customer deletion
- [x] StripeSubscriptionService
    - [x] Create/manage subscriptions
    - [x] Handle plan changes
    - [x] Process cancellations
    - [x] Manage trial periods
- [x] StripeAddonService
    - [x] Process one-time purchases
    - [x] Handle recurring addons
    - [x] Manage addon expiration
- [~] StripeInvoiceService
    - [~] Sync Stripe invoices
    - [ ] Generate PDFs
    - [~] Handle payment status

## 4. API Endpoints 🔄

### Customer Management
- [ ] POST /api/v1/billing/customers
    - [ ] Create Stripe customer
    - [ ] Link to User/Tenant
- [ ] PUT /api/v1/billing/customers/{id}
    - [ ] Update billing information
    - [ ] Modify Stripe customer

### Subscription Management
- [x] GET /api/v1/subscription-plans
    - [x] List available plans
    - [x] Include pricing and features
- [x] POST /api/v1/subscriptions
    - [x] Create new subscription
    - [x] Handle trial periods
- [x] PUT /api/v1/subscriptions/{id}
    - [x] Update subscription
    - [x] Change plans
    - [x] Modify billing cycle
- [x] DELETE /api/v1/subscriptions/{id}
    - [x] Cancel subscription
    - [x] Handle immediate/end-of-period

### Addon Management
- [x] GET /api/v1/addon-packages
    - [x] List available addons
    - [x] Show pricing and details
- [ ] POST /api/v1/addon-purchases
    - [ ] Purchase addon
    - [ ] Handle one-time/recurring
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

## 5. Webhook Handling 🔄

### Required Webhooks
- [x] customer.subscription.created
    - [x] Create local subscription
    - [x] Set up trial period
- [x] customer.subscription.updated
    - [x] Update subscription status
    - [x] Handle plan changes
- [x] customer.subscription.deleted
    - [x] Mark subscription as cancelled
    - [x] Handle cleanup
- [x] invoice.created
    - [x] Create local invoice
    - [x] Set up payment tracking
- [x] invoice.paid
    - [x] Update invoice status
    - [x] Trigger post-payment actions
- [x] invoice.payment_failed
    - [x] Handle failed payments
    - [x] Send notifications

## 6. Frontend Components 🔄

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

## 7. Testing Strategy 🔄

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

## 8. Documentation 🔄

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

## 9. Deployment Checklist 🔄

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
