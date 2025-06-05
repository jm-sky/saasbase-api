# Subscription Payments

## About

### What I want

I want to allow users and tenants to buy something in my app. It could be a one-time addon (i.e.extra GB storage), or recurring plans (i.e. Basic, Pro, Enterprise, FinancialBasic, ProjectsBasic). Buying would involve online payments with Stripe.

### Description

This module supports both **tenant-scoped** and **user-scoped** billing via Stripe.  
It allows:
- Subscriptions tied to either a `User` or `Tenant`
- One-time and recurring addon purchases
- Invoices and billing information for legal compliance
- Stripe customer and subscription tracking

Billing is polymorphic via a shared `billable_type` + `billable_id` pattern.

---

### `BillingCustomer`

**Stores Stripe customer ID for any billable entity (User or Tenant).**
- `id`: UUID  
- `billable_type`: string (`User`, `Tenant`)  
- `billable_id`: UUID  
- `stripe_customer_id`: string  
- `created_at`, `updated_at`

---

### `BillingInfo`

**Billing address and tax info for invoices and legal compliance.**
- `id`: UUID  
- `billable_type`: string (`User`, `Tenant`)  
- `billable_id`: UUID  
- `name`: string  
- `address_line1`: string  
- `address_line2`: string (nullable)  
- `postal_code`: string  
- `city`: string  
- `state`: string (nullable)  
- `country`: string (ISO 3166-1 alpha-2)  
- `vat_id`: string (nullable)  
- `tax_id`: string (nullable)  
- `email_for_billing`: string (nullable)  
- `note`: string (nullable)  
- `created_at`, `updated_at`

---

### `SubscriptionPlan`

**Available subscription plans (e.g. “Pro”, “Enterprise”).**
- `id`: UUID  
- `name`: string  
- `stripe_product_id`: string  
- `stripe_price_id`: string  
- `interval`: enum (`monthly`, `yearly`)  
- `price`: decimal – display-only  
- `features`: JSON or separate table  

---

### `Subscription`

**Active subscription for a User or Tenant.**
- `id`: UUID  
- `billable_type`: string (`User`, `Tenant`)  
- `billable_id`: UUID  
- `subscription_plan_id`: UUID (nullable)  
- `stripe_subscription_id`: string  
- `status`: string (`trialing`, `active`, `past_due`, etc.)  
- `current_period_start`: datetime  
- `current_period_end`: datetime  
- `ends_at`: datetime (nullable)  
- `cancel_at_period_end`: bool  
- `created_at`, `updated_at`

---

### `AddonPackage`

**Optional on-demand or recurring upgrade packages (e.g. “+10 GB Storage”).**
- `id`: UUID  
- `name`: string  
- `stripe_price_id`: string  
- `description`: string  
- `type`: enum (`one_time`, `recurring`)  
- `price`: decimal – optional display price  
- `created_at`, `updated_at`

---

### `AddonPurchase`

**Tracks addon purchases for a User or Tenant.**
- `id`: UUID  
- `billable_type`: string (`User`, `Tenant`)  
- `billable_id`: UUID  
- `addon_package_id`: UUID  
- `stripe_invoice_item_id`: string (nullable)  
- `purchased_at`: datetime  
- `expires_at`: datetime (nullable)  
- `created_at`, `updated_at`

---

### `SubscriptionInvoice`

**Mirror of Stripe invoices, used for display, history, and compliance.**
- `id`: UUID  
- `billable_type`: string (`User`, `Tenant`)  
- `billable_id`: UUID  
- `stripe_invoice_id`: string  
- `amount_due`: decimal  
- `status`: string (`draft`, `open`, `paid`, etc.)  
- `hosted_invoice_url`: string  
- `pdf_url`: string  
- `issued_at`: datetime  
- `paid_at`: datetime (nullable)  
- `created_at`, `updated_at`


# Stripe Billing Flow (User or Tenant)

This flow describes how to integrate Stripe for both `User` and `Tenant` billable entities in a polymorphic billing system.

---

## 1. Create Stripe Customer

When initiating billing for a new `Tenant` or `User`:

- Call Stripe API: `POST /v1/customers`
- Store the `stripe_customer_id` in your `BillingCustomer` model
- This customer ID is used for subscriptions, invoices, and payment methods

---

## 2. Update Billing Information

Optionally, update the Stripe Customer with billing details:

- Use data from your internal `BillingInfo` model
- Stripe API: `POST /v1/customers/{id}`
- Include:
  - Name (company or individual)
  - Address
  - Email
  - Tax ID / VAT ID
  - Country

This ensures that invoices contain the correct legal info.

---

## 3. Create Subscription Plans

To define plans (e.g. “Pro”, “Enterprise”):

- Call Stripe API: `POST /v1/products`  
  → Create a product for each plan
- Call Stripe API: `POST /v1/prices`  
  → Define pricing (amount, interval, currency) and link to product
- Store:
  - `stripe_product_id`
  - `stripe_price_id`
  in your `SubscriptionPlan` model

---

## 4. Create Addon Packages

To support on-demand or recurring addons (e.g. “+10 GB Storage”):

- Create a **Product** for the addon
- Create a **Price** (either one-time or recurring)
- Store the `stripe_price_id` in your `AddonPackage` model

---

## 5. Create Subscription

To activate a subscription for a `User` or `Tenant`:

- Use the saved `stripe_customer_id`
- Call Stripe API: `POST /v1/subscriptions` with the desired `price_id`
- Store the `stripe_subscription_id` in your `Subscription` model

---

## 6. Handle Invoices

Stripe automatically generates invoices for subscriptions and purchases.

- You can listen for `invoice.created`, `invoice.paid`, etc. (via webhooks) 
- Store a local copy of relevant invoice data in your `SubscriptionInvoice` model
  - `stripe_invoice_id`
  - `amount_due`
  - `status`
  - `pdf_url`
  - `issued_at`, `paid_at`

---

## 7. Handle One-Time Addons

For one-time purchases (e.g. extra storage):

- Use `POST /v1/invoiceitems` to add the addon to the customer
- Create and finalize an invoice (`POST /v1/invoices`)
- Optionally collect payment immediately
- Save the record in your `AddonPurchase` model


# Available Subscription Plans and Addons

---

## Tenant Plans

### Basic
- Limit: 10 invoices / month
- Storage: 1 GB
- Users: 5
- GUS/VIES requests: 5

### Pro
- Limit: 100 invoices / month
- Storage: 10 GB
- Users: 10
- GUS/VIES requests: 10

---

## Tenant Addons

### Plus 1 User
- Dodaje 1 dodatkowego użytkownika do planu

### Plus 5 GB Storage
- Dodaje 5 GB dodatkowej przestrzeni dyskowej

### KSEF Integration
- Dostęp do integracji z Krajowym Systemem e-Faktur

### E-doreczenia Integration
- Dostęp do integracji z systemem e-Doręczenia

### Unlimited GUS/VIES Requests
- Nielimitowane zapytania do GUS/VIES

---

## User Plans

### Personal Storage 10 GB
- 10 GB przestrzeni dyskowej na potrzeby indywidualne użytkownika

---

## Uwagi do implementacji

- Plany podstawowe (Basic, Pro) to `SubscriptionPlan`
- Dodatki (Plus 1 user, Plus 5 GB, integracje, unlimited requests) to `AddonPackage` (mogą być jednorazowe lub cykliczne)
- Limitacje (liczba faktur, użytkowników, zapytań) egzekwujemy po stronie aplikacji, bazując na aktywnych planach i dodatkach
- Integracje (KSEF, e-Doreczenia) można trzymać jako flagi lub uprawnienia powiązane z aktywnym subskrypcją lub dodatkiem


## ✅ TODO: Migrate `features` from array → relation `plan_features`

### 🎯 Kontekst  
Obecnie model Plan posiada pole `features` jako tablicę (array/json), np.:

```
{
  "max_users": 5,
  "storage_gb": 10,
  "api_calls": 1000,
  "custom_domain": false
}
```

### ⚠️ Problem  
- Trudność w agregacji (np. COUNT, JOIN, filtrowanie).  
- Brak elastyczności typów i opisów funkcji.  
- Utrudnione dodawanie nowych funkcji bez zmian w kodzie i bazie.  
- Brak możliwości wersjonowania, notatek czy warunków (np. reset miesięczny).  

### ✅ Cel  
Zamiana pola `features` na relację:  
- Tabela `features` jako katalog funkcji.  
- Tabela `plan_features` jako przypisanie wartości do danego planu.  

### 🧩 Zadania  
- [ ] Stworzyć tabele `features` i `plan_features`.  
- [ ] Przenieść dane z obecnego pola `features` na modelu `Plan` do nowej relacji.  
- [ ] Zmodyfikować kod aplikacji – np. zamiast `plan.features['max_users']` używać helpera `plan.getFeature('max_users')`.  
- [ ] (Opcjonalnie) Usunąć stare pole `features` lub oznaczyć je jako `@deprecated`.  

### 💡 Uwagi  
- Uzgodnić format wartości:  
  - -1 lub "unlimited" jako nielimitowane.  
  - true/false dla funkcji binarnych.  
- To przygotowanie pod rozbudowę planów i dynamiczne pakiety w przyszłości (np. dla klientów Enterprise).