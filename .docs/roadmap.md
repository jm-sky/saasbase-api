# SaaSBase API Roadmap

## Phase 1: Foundation
- [ ] **Core Authentication System**
  - [x] Basic JWT authentication
  - [x] Password reset functionality
  - [x] Two-factor authentication (2FA) with JWT, TOTP, recovery codes
  - [x] OAuth provider integration (Google, GitHub, etc.)
  - [x] User settings and preferences
  - [ ] Security hardening (rate limiting, session management)
  - [ ] Profil Zaufany (Polish eID) integration via OIDC/SAML
  - [ ] ActionLog and AuditLog for compliance and traceability

- [ ] **Multi-tenancy Implementation**
  - [x] Tenant model and migrations
  - [x] BelongsToTenant trait
  - [ ] Tenant isolation testing
  - [ ] Cross-tenant operations for admins
  - [ ] Resource allocation per tenant
  - [ ] Dictionary tables with string primary keys for VAT, countries, units
  - [ ] Exception handling for tenant context

- [ ] **User Management**
  - [x] Basic user CRUD
  - [ ] User profile management
  - [ ] Role-based access control (RBAC) with roles, permissions, policies
  - [ ] Permission system
  - [ ] User invitations
  - [ ] User settings and preferences
  - [ ] OAuth/social login (Google, GitHub, etc.)

## Phase 2: Document Management
- [ ] **Contractor Management**
  - [ ] Contractor profiles
  - [ ] Contact information system
  - [ ] Historical data tracking
  - [ ] Contractor categorization
  - [ ] Address management
  - [ ] Contractor Data Import
  - [ ] VAT Number Validation
  - [ ] Bank Account Validation

- [ ] **Basic Invoice System**
  - [ ] Invoice creation and templates
  - [ ] Status tracking
  - [ ] Payment tracking
  - [ ] Basic reporting
  - [ ] PDF generation
  - [ ] Invoice Generation Service
  - [ ] Invoice Numbering Service
  - [ ] Invoice PDF Generation Service

- [ ] **Tax Management**
  - [ ] Tax rates configuration
  - [ ] Tax calculations
  - [ ] Tax report generation
  - [ ] VAT handling
  - [ ] Tax rules by region

- [ ] **Document Storage**
  - [ ] File upload system (per-model attachments)
  - [ ] Document versioning
  - [ ] Access control
  - [ ] Preview generation
  - [ ] Search functionality
  - [ ] MinIO integration for file storage
  - [ ] Media conversions (thumbnails, previews)
  - [ ] Security: MIME type validation, size limits, tenant isolation
  - [ ] API endpoints for file management
  - [ ] Testing: upload, deletion, conversions, MinIO, API

## Phase 3: Enhanced Features
- [ ] **Exchange Rate Integration**
  - [ ] Multiple currency support
  - [ ] Automatic rate updates (NBP, ECB integration)
  - [ ] Historical rate tracking
  - [ ] Custom rate overrides
  - [ ] Currency conversion
  - [ ] Exchange Rate Import Service (scheduling, error handling, admin notifications)

- [ ] **Company Lookup Service**
  - [ ] Integration with external providers
  - [ ] Company data enrichment
  - [ ] Verification services
  - [ ] Data caching
  - [ ] Rate limiting

- [ ] **Advanced Invoice Features**
  - [ ] Custom templates
  - [ ] Recurring invoices
  - [ ] Batch operations
  - [ ] Advanced reporting
  - [ ] Export/import functionality

- [ ] **Import/Export System**
  - [ ] Multiple format support (PDF, CSV, XML)
  - [ ] Data validation
  - [ ] Error handling
  - [ ] Progress tracking
  - [ ] Scheduled imports/exports

## Phase 4: Project Management
- [ ] **Basic Project Structure**
  - [ ] Project CRUD operations
  - [ ] Team assignment
  - [ ] Project status tracking
  - [ ] Resource allocation
  - [ ] Project templates
  - [ ] Project Status Management Service

- [ ] **Task Management**
  - [ ] Task creation and assignment
  - [ ] Priority and status tracking
  - [ ] Time tracking
  - [ ] Dependencies management
  - [ ] Task templates
  - [ ] Task Assignment Service
  - [ ] Time Tracking Service

- [ ] **Team Collaboration**
  - [ ] Comments and discussions
  - [ ] Activity tracking
  - [ ] Notifications (Notification model, user preferences)
  - [ ] File sharing
  - [ ] @mentions

- [ ] **Workflow Automation**
  - [ ] Custom workflows
  - [ ] Automated actions
  - [ ] Status transitions
  - [ ] Notifications
  - [ ] Integration hooks

## API Features
- [ ] Standardized filtering and sorting for all endpoints (Spatie Query Builder, custom filters/operators, combo search)
- [ ] Consistent query parameter structure
- [ ] Extensible filtering and sorting per model

## System Logging & Auditing
- [ ] ActionLog and AuditLog for compliance and traceability

## Tagging & Skills
- [ ] Tag and Skill models for categorization

## Pricing & Discounts
- [ ] PriceList, Discount, MeasurementUnit models

## Dictionary/Customizable Values
- [ ] DictionaryEntry model

## Chat System
- [ ] ChatRoom, ChatMessage, ChatParticipant

## Employee Management
- [ ] Employee and EmployeeAgreement models

## Admin/Compliance
- [ ] Security audit
- [ ] Penetration testing
- [ ] Compliance checks
- [ ] Access control review
- [ ] Audit logs

## Notes
For detailed requirements and technical implementation, see the `.docs/features/`, `.docs/models/`, and `.docs/services/` directories. However, all major requirements are listed above for Task Master parsing.

## Module Template
For each new module, follow this checklist:

### CRUD Module: [RESOURCE_NAME]
1. [ ] **Data Layer**
   - [ ] Create model and migration
   - [ ] Implement BelongsToTenant
   - [ ] Add factory and seeder
   - [ ] Define relationships

2. [ ] **Business Logic**
   - [ ] Create DTOs (Data, UpdateData)
   - [ ] Implement service layer
   - [ ] Add validation rules
   - [ ] Handle tenant scope

3. [ ] **API Layer**
   - [ ] Create controller
   - [ ] Add API routes
   - [ ] Implement request classes
   - [ ] Add response transformers

4. [ ] **Testing**
   - [ ] Unit tests
   - [ ] Feature tests
   - [ ] Integration tests
   - [ ] Tenant isolation tests

5. [ ] **Documentation**
   - [ ] API documentation
   - [ ] Update models index
   - [ ] Code examples
   - [ ] Integration guide