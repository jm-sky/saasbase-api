# Matrix of Possibilities for Tenant Context (`BelongsToTenant`)

## Overview
This matrix describes the various scenarios for authenticated users, tenant context in JWT, and behaviors during model creation or querying.

---

## Matrix

| Scenario | User Authentication | JWT Contains `tenant_id` | Model Creation / Querying | Expected Behavior |
|:---------|:---------------------|:-------------------------|:--------------------------|:------------------|
| 1 | Authenticated | Yes | Creating model with matching `tenant_id` | Success. Model assigned to tenant from JWT. |
| 2 | Authenticated | No | Creating model with matching `tenant_id` | Failure. Exception or validation error (no tenant context). |
| 3 | Authenticated | No | Creating model with not matching `tenant_id` | Failure. Exception thrown (invalid tenant context). |
| 4 | Authenticated | Yes | Creating model with custom `tenant_id` (admins only) | Success for admins. Admins can override tenant_id. |
| 5 | Not Authenticated | No | Creating model with matching `tenant_id` | Failure. Exception (no authentication). |
| 6 | Not Authenticated | No | Creating model with custom `tenant_id` | Failure. Exception (no authentication). |
| 7 | Authenticated | Yes | Querying models with matching `tenant_id` | Success. Data filtered by tenant from JWT. |
| 8 | Authenticated | Yes | Querying models with different `tenant_id` (admins only) | Success. Admins can query without tenant restriction. |
| 9 | Authenticated | No | Querying models | Failure or restricted results. No tenant context available. |
| 10 | Not Authenticated | No | Querying models | Failure. Unauthorized access. |

---

## Explanation

- **Authenticated + JWT tenant_id**:  
  - Normal users are restricted to models where `tenant_id` matches their JWT.
  - Admin users may override tenant context (e.g., passing custom `tenant_id` manually).

- **Authenticated without JWT tenant_id**:  
  - Users cannot create or query tenant-specific models.  
  - Typically a misconfiguration; should result in exception or empty result.

- **Not Authenticated**:  
  - No model creation or querying allowed. Should throw authentication error.

- **Admin-specific behavior**:
  - Admins may be allowed to specify a `tenant_id` manually during create/query operations.
  - This bypass requires a role/permission check (e.g., `is_admin` flag).

- **Validation**:
  - If a mismatch between the model's `tenant_id` and JWT `tenant_id` occurs, an exception must be thrown immediately.
  - This ensures strict tenant isolation.

- **Scope Handling**:
  - `BelongsToTenant` should automatically add a global query scope based on `tenant_id` from JWT.
  - Admins can optionally disable this scope when needed (e.g., `withoutTenantScope()`).