# Tenant Integrations Design (Multi-Tenant SaaS, Laravel 12)

## Table: tenant_integrations

- `id: uuid` – Primary key
- `tenant_id: uuid` – Foreign key referencing the tenants table
- `type: string` – Integration type identifier, e.g., 'azure_ai', 'regon', 'edoręczenia'
- `enabled: boolean` – Whether the integration is active
- `mode: string` – 'shared' or 'custom'; defines if global or tenant-specific credentials are used
- `credentials: jsonb` – Encrypted credentials for API access (API keys, tokens, etc.)
- `meta: jsonb` – Optional integration-specific settings or metadata
- `last_synced_at: timestamp` – Optional timestamp of last usage/synchronization
- `created_at: timestamp`
- `updated_at: timestamp`

## Behavior

- If `mode` is set to `'custom'`, the `credentials` field contains tenant-specific secrets.
- If `mode` is `'shared'`, the application falls back to global configuration (e.g., config files or env-based settings).
- The `credentials` field must be encrypted at rest using Laravel's custom cast or `Encryptable` attributes.

## Security

- Store API secrets in the `credentials` field as encrypted JSON.
- Use Laravel’s built-in encryption via custom Eloquent casts to automatically encrypt/decrypt on access.
- Apply validation rules to ensure correct credential structure.

## Scalability and Extensibility

- This structure supports adding more integrations without changing the schema.
- Enables per-tenant billing or feature toggling based on which integrations are active.
- Optional `status` and integration call logging (via separate table) can support analytics and auditing.

## Global Fallbacks

- Global/shared credentials are stored in Laravel config files (e.g., `config/integrations.php`) or `.env`.
- Integration resolution logic should prefer tenant-specific credentials when `mode = 'custom'`, and fall back otherwise.

## Caching

- Integration resolution results should be cached per tenant and integration type with TTL.
