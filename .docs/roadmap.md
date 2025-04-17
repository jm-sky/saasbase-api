# Project Roadmap

## 1. Planning and Requirements Definition
- [ ] **Understand project goals**:
  - Application with multi-tenancy, supporting users, projects, invoices, and more.
  - Integrations with Google and GitHub for registration/login, JWT authentication, 2FA support.
  - Premium extensions and online payments.
  - Chat functionality between team members and companies.
- [ ] **Define technologies**:
  - **Frontend**: Vue.js, TailwindCSS, TypeScript.
  - **Backend**: Laravel with JWT, Multi-Tenancy, integrations with Google/GitHub.
  - **Database**: PostgreSQL or MySQL.
  - **Authorization**: JWT, OAuth 2.0, 2FA.
  - **Payments**: Stripe, PayPal.
  - **Security**: TLS, password encryption, rate limiting.
- [ ] **Divide into stages**:
  - **MVP**: Basic version with user registration/login, JWT, 2FA, basic modules (projects, invoices).
  - **Scaling**: Add online payment functionality, premium extensions, chat.

---

## 2. Backend API – System Development
- [ ] **General Assumptions**:
  - **JWT & Authentication**: User registration, login, login with Google/GitHub.
  - **Multi-Tenancy**: Support for different types of tenants (company/team) and related data (projects, invoices, users).
  - **2FA**: Two-factor authentication for users.
- [ ] **Implementation**:
  - **Registration/Login**: JWT, Google/GitHub login, 2FA configuration.
  - **Invoice Module**: Add models for contractors, products, invoices, and attachments. Ability to generate and manage invoices.
  - **Project Module**: Create and manage projects and tasks.
  - **Online Payments**: Integrate payment gateways (e.g., Stripe) for premium extensions.
  - **Chat Module**: Real-time chat functionality.
- [ ] **Roles and Permissions**:
  - **Employees/Team Members**: Different roles depending on whether the tenant is a company or a team.
- [ ] **API Testing and Documentation**:
  - **Unit and Integration Tests**: Create tests for registration, login, 2FA, payments.
  - **API Documentation**: Generate API documentation for users and integrations.

---

## 3. Frontend – User Interface
- [ ] **Frontend Assumptions**:
  - **Registration/Login**: Forms for registration, Google/GitHub login, 2FA handling.
  - **Projects & Tasks**: Interfaces for creating and editing projects, assigning tasks.
  - **Invoice Module**: UI for managing contractors, products, generating invoices.
  - **Chat**: Interface for team/company communication.
- [ ] **State Management**:
  - **Pinia**: Managing app state (user sessions, project data, invoices) without Vuex.
- [ ] **Interacting with the API**:
  - **JWT**: Using JWT tokens for authentication.
  - **Online Payments**: UI for making payments via Stripe/PayPal.
- [ ] **Responsiveness & UI/UX**:
  - **UI Design**: Use tools like Figma for prototyping.
  - **TailwindCSS**: Ensure responsiveness and fast loading times.

---

## 4. Security and Scaling
- [ ] **App Security**:
  - **JWT & 2FA**: Secure JWT tokens, store them in httpOnly cookies, implement 2FA.
  - **Rate Limiting**: Prevent brute force attacks, limit login attempts.
  - **Protection Against Attacks**: CSRF, XSS, SQL Injection.
- [ ] **App Scaling**:
  - **Load Balancing**: Ability to scale the backend as the load grows.
  - **Caching**: Redis, Memcached for performance improvement.
  - **Database Sharding**: Split the database if scaling is required.

---

## 5. Testing and QA
- [ ] **Unit and Functional Tests**:
  - Test models (users, projects, invoices), controllers, middleware.
  - Test registration, login, 2FA, payment processes.
- [ ] **UI/UX Testing**:
  - Test the UI for functionality and usability.
  - E2E tests with Cypress, Selenium.
- [ ] **Security Testing**:
  - Security audits.
  - Test for vulnerabilities (OWASP Top 10).

---

## 6. Deployment and Maintenance
- [ ] **Deployment**:
  - Deploy backend API (Docker, Kubernetes, CI/CD pipelines).
  - Deploy frontend (Netlify, Vercel, or custom hosting).
- [ ] **Maintenance**:
  - Regular bug fixes, security updates, feature improvements.

---

## 7. Dictionary Implementation Roadmap

### 1. Core Dictionaries
- [ ] **Users**: User roles, statuses, etc.
- [ ] **Countries**: List of countries for selection (for contracts, invoices, etc.).
- [ ] **Contractors**: List of contractors (companies, individuals).
- [ ] **Contractor Addresses**: List of address types and formats for contractors.
- [ ] **Contractor Contacts**: Contacts related to contractors (phone numbers, emails, etc.).
- [ ] **Invoices**: Invoice statuses, payment methods, types, etc.

---

### 2. Business Dictionaries
- [ ] **Attachments**: Supported file types, maximum file sizes.
- [ ] **Comments**: Comment statuses, types of comments (internal, external).
- [ ] **Tags**: Tag categories for projects, tasks, invoices.
- [ ] **Projects**: Project statuses, types (internal, external, etc.).
- [ ] **Tasks**: Task statuses, priorities, types.

---

### 3. Future Dictionaries (if applicable)
- [ ] **Payment Methods**: Supported methods for payments (credit card, PayPal, etc.).
- [ ] **Roles**: Custom roles for users (admin, manager, user, etc.).

---

#### CRUD Module: [RESOURCE NAME] (e.g. Invoice)

- [ ] (P1) Create `[Resource]` model, migration and factory  
- [ ] (P1) Create DTOs: `[Resource]Data`, `[Resource]UpdateData`  
- [ ] (P1) Create request classes: `Store[Resource]Request`, `Update[Resource]Request` [depends on: DTOs]  
- [ ] (P1) Create `[Resource]Controller` with CRUD methods [depends on: requests]  
- [ ] (P1) Add `[resource]` API routes to `routes/api.php` [depends on: controller]  
- [ ] (P2) Write feature tests for `[resource]` API [depends on: controller, routes]  
- [ ] (P2) Add support for file attachments (if applicable)  
- [ ] (P2) Generate downloadable PDF (if applicable)  
- [ ] (P3) Connect `[resource]` with related models (e.g. contractors, products)