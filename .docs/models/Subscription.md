# Subscription Model

Represents a tenant's subscription to a specific plan, tracking status and billing period.

## Attributes

- `id` (uuid) - Primary key
- `tenant_id` (uuid) - Reference to the tenant
- `plan_id` (uuid) - Reference to the subscription plan
- `status` (varchar) - Subscription status ('active', 'cancelled', 'expired')
- `trial_ends_at` (timestamp, nullable) - When the trial period ends
- `starts_at` (timestamp) - When the subscription begins
- `ends_at` (timestamp, nullable) - When the subscription ends
- `created_at` (timestamp) - Creation timestamp
- `updated_at` (timestamp) - Last update timestamp

## Relationships

- `tenant` - BelongsTo relationship to [Tenant](./Tenant.md)
- `plan` - BelongsTo relationship to [SubscriptionPlan](./SubscriptionPlan.md)
- `invoices` - HasMany relationship to [Invoice](./Invoice.md)

## Business Rules

1. Subscription Lifecycle:
   - New subscriptions start with optional trial
   - Can be cancelled but remain active until period ends
   - Auto-renews unless cancelled
   - Grace period after expiration

2. Status Management:
   - Active: Current and paid
   - Cancelled: Will end at period end
   - Expired: Past due or ended
   - Status changes trigger notifications

3. Trial Period:
   - Optional free trial period
   - Can be extended manually
   - Requires payment method on file
   - Converts to paid automatically

4. Plan Changes:
   - Can upgrade/downgrade during period
   - Prorated billing for upgrades
   - Downgrades take effect next period

## Usage

```php
// Create new subscription
$subscription = $tenant->subscriptions()->create([
    'plan_id' => $plan->id,
    'starts_at' => now(),
    'trial_ends_at' => now()->addDays(14)
]);

// Cancel subscription
$subscription->cancel(); // Ends at period end

// Cancel immediately
$subscription->cancelNow();

// Change plan
$subscription->changePlan($newPlan);

// Check status
if ($subscription->isActive()) {
    // Handle active subscription
}

// Check trial
if ($subscription->onTrial()) {
    $daysLeft = $subscription->trial_ends_at->diffInDays(now());
}

// Resume cancelled subscription
if ($subscription->cancelled() && !$subscription->ended()) {
    $subscription->resume();
}
``` 
