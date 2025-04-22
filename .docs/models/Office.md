## Description

An **Office** is a specialized type of Tenant that provides services to other tenants. Each Office is also a Tenant (linked via `tenant_id`) and may supervise resources such as projects, employees, and invoices belonging to other tenants.

## Key Concepts
- Has its own `tenant_id`.
- Offers services via `OfficeServiceTypes`.
- Manages agreements with other tenants.
- Has visibility and control over tenant data it supervises.

...