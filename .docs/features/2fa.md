# 2FA Implementation with Temporary JWT (DropSaaS)

Context:  
Laravel API-only (v12), JWT-based auth (access + refresh), no sessions, Vue frontend.

---

Flow Overview

1. Login Request
- User submits email and password.
- Backend verifies credentials.

2. If 2FA Disabled
- Issue standard access token and refresh token.
- User is fully authenticated.

3. If 2FA Enabled
- Issue a temporary JWT, e.g.:

  {
    "sub": "user_id",
    "status": "pending-2fa",
    "exp": "5min"
  }

- Return response:
  {
    "2fa_required": true,
    "token": "TEMP_JWT"
  }

4. Frontend
- Detects 2fa_required, prompts user for TOTP code.
- Submits 2FA code to /api/auth/verify-2fa using TEMP_JWT in Authorization header.

5. Verify 2FA
- Backend validates TOTP for the user.
- If valid:
  - Issue real access & refresh tokens.
- If invalid:
  - Return error (with retry logic and throttling).

6. Protecting Routes
- Use middleware to reject requests with:
  - status === "pending-2fa" on JWT
  - or missing verification
---

Security Notes

- Encrypt TOTP secret in DB (Crypt::encrypt()).
- Throttle TOTP attempts.
- Use short TTL for temporary JWT.
- Optionally support recovery codes.