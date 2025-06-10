## IdentityCheck

**Description**  
Represents a verification process used to confirm a user's identity or a tenant's (company's) official data or ownership. Each check records the verification target, the method used, purpose, result, and any related evidence or metadata. Useful for legal compliance, audit trails, and access control.

We assume that once confirmed identity stays confirmed. Only special situations can change that state.

### Model: `IdentityCheck`

- [x] Draft implementation: `app/Domain/IdentityCheck/Models/IdentityCheck.php`

- **id**: `UUID` — Unique identifier for the identity check.
- **verifiable_type**: `string` — Polymorphic target: `"User"` or `"Tenant"`.
- **verifiable_id**: `UUID` — ID of the user or tenant being verified.
- **purpose**: `string` — What is being verified: `identity`, `official_data`, `ownership`.
- **method**: `string` — How verification was performed (see "Verification Methods" below).
- **status**: `string` — Current status: `pending`, `verified`, `rejected`.
- **verified_at**: `timestamp|null` — Timestamp when verification was successfully completed.
- **verified_by**: `UUID|null` — ID of the admin who verified it (if applicable).
- **rejected_reason**: `text|null` — Reason for rejection, if verification failed.
- **data**: `jsonb` — Captured metadata or evidence (e.g. matched names, registry links, confidence scores).
- **created_at**: `timestamp` — When the check was initiated.
- **updated_at**: `timestamp` — Last modification timestamp.

### Verification Methods

#### For Users:
- `bank_transfer` — Verified using bank account name and number.
- `edoreczenia` — Verified by sending a secure message via e-Doręczenia.
- `epuap` — Verified via login or document issued through ePUAP.
- `manual` — Manually verified by platform staff using submitted documents.

#### Whats done:
- `POST /api/v1/identity/confirmation/template`
- `POST /api/v1/identity/confirmation/submit`
- `IdentityConfirmationController`

#### For Tenants (Companies):
- `bank_transfer` — Verified via bank account ownership matched with registry.
- `ceidg` — Company data verified against CEIDG registry, optionally matched to a user.
- `edoreczenia` — Verified via e-Doręczenia address associated with the tenant.
- `epuap` — Verified via company-related ePUAP document.
- `ksef_token` — Verified through successful authentication using KSeF token.
- `manual` — Verified manually based on uploaded or researched documents.

