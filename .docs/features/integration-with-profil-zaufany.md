## 🔐 Integration with Profil Zaufany (Trusted Profile)

### 📌 Purpose
Enable users to confirm their identity using the official Polish eID system (**Profil Zaufany**) via the **Login.gov.pl** national identity gateway. This is required for legal identity verification in systems handling sensitive or official data (e.g. e-signatures, government services, or trusted account creation).

### 🎯 Goals
- Authenticate users via state-issued digital identity.
- Receive verified user data (e.g., PESEL, full name, email).
- Store proof of verified identity for internal or legal use.

### 🧩 Integration Summary

**Identity Provider**: Węzeł Krajowy (Login.gov.pl)  
**Supported Protocols**: `OpenID Connect (OIDC)` _(recommended)_ or `SAML 2.0`  
**Access**: Requires registration and approval from Centralny Ośrodek Informatyki (COI)

---

### ⚙️ Steps to Integrate

1. **Register Service Provider**  
   - Contact Login.gov.pl to register your app.
   - Obtain credentials: `client_id`, `client_secret`, and endpoint URLs.

2. **Implement OIDC Flow** (recommended)  
   - Redirect user to authorization URL.
   - Handle redirect with `code`.
   - Exchange `code` for `access_token`.
   - Use token to retrieve user identity via `/userinfo`.

3. **Handle User Data**  
   - Extract verified personal data (e.g., PESEL, name).
   - Store information and track identity verification status.

4. **Ensure Security & Compliance**  
   - Use HTTPS, validate tokens, secure secrets.
   - Ensure GDPR compliance (user consent, data storage, retention).

---

### 📦 Tech Stack Hints

- **Backend**: Laravel + Socialite or SAML2 package.
- **Frontend**: Vue.js triggers flow, backend handles token exchange.
- **Docs & Access**: [https://www.login.gov.pl/integracja](https://www.login.gov.pl/integracja)

---

### ✅ Deliverables

- [ ] Registered and approved app on Login.gov.pl
- [ ] Working OIDC/SAML flow
- [ ] Verified user data stored securely
- [ ] User-facing flow with explanation & loader
