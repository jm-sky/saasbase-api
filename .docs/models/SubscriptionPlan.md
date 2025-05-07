# SubscriptionPlan Model

Represents a subscription tier with specific features and pricing.

## Attributes

- `id` (uuid) - Primary key
- `name` (varchar) - Plan name
- `description` (text, nullable) - Plan description
- `price` (decimal) - Plan price
- `billing_period` (varchar) - Billing frequency ('monthly', 'yearly')
- `features` (json) - List of included features and limits
- `is_active` (boolean) - Whether the plan is available for purchase
- `created_at` (timestamp) - Creation timestamp
- `updated_at` (timestamp) - Last update timestamp

## Relationships

- `subscriptions` - HasMany relationship to [Subscription](./Subscription.md)
- `tenants` - HasManyThrough relationship to [Tenant](./Tenant.md) through [Subscription](./Subscription.md)

## Business Rules

1. Plan Features:
   - Defined as JSON structure
   - Can include boolean flags (feature on/off)
   - Can include numeric limits (e.g., max users)
   - Can include specific permissions

2. Pricing:
   - Monthly and yearly billing options
   - Yearly plans typically offer a discount
   - Price stored in smallest currency unit

3. Plan Management:
   - Plans can be deactivated but not deleted
   - Existing subscriptions remain valid
   - Price changes don't affect existing subscriptions

4. Feature Changes:
   - New features can be added
   - Features can be removed (with migration plan)
   - Limits can be adjusted

## Usage

```php
// Create a new plan
$plan = SubscriptionPlan::create([
    'name' => 'Professional',
    'description' => 'For growing teams',
    'price' => 2999, // $29.99
    'billing_period' => 'monthly',
    'features' => [
        'max_users' => 10,
        'max_projects' => 50,
        'feature_x' => true,
        'feature_y' => false
    ],
    'is_active' => true
]);

// Get active plans
$plans = SubscriptionPlan::where('is_active', true)
    ->orderBy('price')
    ->get();

// Check if plan includes feature
$hasFeature = $plan->features['feature_x'] ?? false;

// Get subscribers count
$subscriberCount = $plan->subscriptions()
    ->where('status', 'active')
    ->count();
``` 
