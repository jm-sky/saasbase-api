# SaaSBase API - Product Requirements Document

## Overview
SaaSBase API is a comprehensive backend service layer designed to support multi-tenant SaaS applications. It provides essential functionality for business applications including authentication, document/invoice management, project management capabilities, and advanced organizational features with cross-tenant interactions.

---

## API Features
- All list/index endpoints implement standardized filtering and sorting using Spatie Query Builder.
- Supported filtering operators: eq, ne, gt, gte, lt, lte, in, nin, between, like, nlike, startswith, endswith, null, notnull, nullish, regex.
- Combo search is available via the `search` parameter, searching across multiple fields.
- Sorting is available on all allowed fields, with ascending/descending order.
- Query parameter structure is consistent across all endpoints.
- Filtering and sorting can be extended per model as needed.

---

## Identity & Security
- Two-factor authentication (2FA) uses JWT-based temporary tokens and TOTP codes. If 2FA is enabled, users receive a temporary JWT and must verify with a TOTP code before receiving a full access token. Recovery codes are supported.
- Profil Zaufany (Polish eID) integration via OIDC/SAML allows users to verify their identity using the national Login.gov.pl gateway. The system stores verified user data (PESEL, name, etc.) and ensures GDPR compliance.
- Security hardening includes rate limiting on sensitive endpoints, session management, strong password policies, and regular audit logging of authentication and authorization events.

---

## Multi-tenancy
- All data is tenant-scoped using a global query scope. The `BelongsToTenant` trait ensures tenant isolation for all relevant models.
- Dictionary tables (e.g., VAT rates, countries, units) use meaningful string primary keys for clarity and maintainability.
- Custom exceptions are thrown if tenant context is missing or invalid, returning a 403 error.
- Tenant ID is stored in JWT claims and request context for secure, automatic scoping.
- Tenant Branding:
  - Customizable tenant logo and branding elements
  - Brand color schemes and theme customization
  - Custom domain support
  - Branded email templates
  - Customizable public profile page
- Tenant Public Profile:
  - Public-facing company information
  - Contact details and social media links
  - Company description and services
  - Team members showcase
  - Public portfolio and achievements
  - Custom URL slugs
  - SEO optimization

---

## System Logging & Auditing
- All critical actions (authentication, data changes, permission changes) are logged in the ActionLog and AuditLog models.
- Audit logs are immutable and used for compliance, security reviews, and troubleshooting.
- Logs include user, timestamp, action type, and before/after data where applicable.

---

## User & Access Management
- Role-based access control (RBAC) is implemented using Role and Permission models. Policies enforce access at the model and action level.
- OAuth/social login supports Google, GitHub, and other providers via OAuthProvider and UserOAuthAccount models.
- Users can be invited via email, with role assignment and invitation tracking.

---

## Employee Management
- Employees are managed via the Employee model, with onboarding workflows and contract management handled by the EmployeeAgreement model.
- Supports employment agreements, contract dates, and salary details.

---

## Tagging & Skills
- Tag and Skill models allow categorization of tasks, projects, and users for better organization and searchability.

---

## Pricing & Discounts
- PriceList and Discount models manage product/service pricing, promotional codes, and discount rules.
- MeasurementUnit model supports standard and custom units, with conversion logic.

---

## Dictionary/Customizable Values
- DictionaryEntry model allows for customizable system values (e.g., statuses, types) that can be managed by admins.

---

## Chat System
- Internal chat supports direct messages, group chats, and channels using ChatRoom, ChatMessage, and ChatParticipant models.
- Features include file sharing, message threading, markdown support, and chat search.
- AI Chat Integration:
  - OpenRouter integration for AI-powered conversations
  - Context-aware responses
  - Multi-model support
  - Conversation history management
  - AI assistant customization per tenant
  - Rate limiting and usage tracking
- Cross-tenant Chat:
  - Public chat rooms for inter-tenant communication
  - Verified business profiles
  - Professional networking features
  - Business opportunity discovery

---

## Notifications
- Notification model supports multiple delivery channels (email, in-app, etc.), template-based messages, and user preferences for notification types.
- Delivery tracking ensures reliable notification delivery and auditability.

---

## Admin/Compliance
- Regular security audits and penetration testing are performed.
- Compliance checks for GDPR and other regulations.
- Access control reviews and audit logs are maintained for all sensitive actions.

---

## Document Storage
- File attachments are supported on multiple models (User, Project, Task, Contractor, etc.) using Spatie Media Library.
- MinIO is used for S3-compatible object storage.
- Media conversions (thumbnails, previews) are generated for images and documents.
- Security: strict MIME type validation, file size limits, tenant isolation, signed URLs for access.
- API endpoints allow upload, deletion, and retrieval of files.
- Automated tests cover upload, deletion, conversion, and access control.

---

## Services
- **Exchange Rate Import Service**: Imports currency rates from NBP and ECB, with fallback, error handling, and admin notifications. Runs on a schedule.
- **Invoice Services**: Automated invoice generation, numbering, and PDF creation.
- **Project/Task/Time Services**: Project status management, task assignment, and time tracking are handled by dedicated services.
- **Contractor Services**: Data import, VAT number validation, and bank account validation for contractors.
- **Company Lookup Services**:
  - REGON Lookup Service: Integration with Polish National Business Registry
  - Ministry of Finance (MF) Lookup Service: Integration with Polish Ministry of Finance database
  - VIES Lookup Service: Integration with EU's VAT Information Exchange System
  - Company Data Auto-fill Service: Unified interface for fetching company information
  - Caching support with configurable duration
  - Comprehensive error handling
  - Command-line tools for testing and debugging
- **Company Services**
  - Company information lookup
    - REGON integration for Polish companies
    - Ministry of Finance integration
    - VIES integration for EU VAT validation
    - Auto-fill service for unified data fetching
  - Verification services
  - Company data enrichment
  - Logo fetching and management
  - IBAN validation and bank account verification

---

## Core Features (Legacy Section, for completeness)

### 1. Authentication Module
- **User Authentication**
  - Email/password authentication
  - Password reset functionality
  - Two-factor authentication (2FA)
  - OAuth integration for social logins
  - JWT-based authentication
- **User Settings & Profile Management**
  - User preferences
  - Profile information
  - Security settings
  - Notification preferences
- **Identity Verification**
  - Email address verification
  - Phone number verification
  - Legal identity verification (government database integration)
  - Company ownership verification
  - Document-based verification process

### 2. Multi-tenancy & Organization
- **Tenant Management**
  - Tenant isolation
  - Tenant-specific configurations
  - Resource allocation per tenant
  - Cross-tenant operations for admin users
- **Tenant Scoping**
  - Automatic tenant context in all operations
  - Tenant-specific data access controls
  - Multi-tenant database schema
- **Hierarchical Organization Structure**
  - Multiple organization units within tenant
  - Customizable hierarchy levels
  - Role inheritance through hierarchy
  - Unit-specific settings and configurations
- **Professional Office Services**
  - Professional service provider management (accounting offices, law firms, etc.)
  - Service contract management
    - Service package definitions
    - Pricing models
    - Contract terms and duration
  - Service delivery tracking
    - Task assignment and monitoring
    - Service level agreements (SLAs)
    - Performance metrics
  - Client (Tenant) Management
    - Multi-tenant service provision
    - Access level configuration
    - Client data management permissions
  - Billing and Invoicing
    - Automatic service billing
    - Usage-based pricing
    - Recurring payment handling
  - Service-specific Features
    - Accounting service workflows
    - Document processing queues
    - Client approval processes
    - Regulatory compliance tracking
  - Office-Tenant Communication
    - Dedicated communication channels
    - Document sharing
    - Task notifications
    - Status updates
- **Employee Management**
  - Employment agreements
  - Employee onboarding workflow
  - Contract management
  - Employee hierarchy

### 3. Invoice/Document Management
- **Contractor Management**
  - Contractor profiles
  - Contact information
  - Historical data
  - Categorization
- **Measurement Units**
  - Standard units
  - Custom units
  - Unit conversions
- **Invoice Management**
  - Invoice creation and numbering
    - Customizable numbering templates
    - Template-based generation
    - Multiple invoice types support
  - Financial tracking
    - Net, tax, and gross amount calculations
    - Multiple currency support
    - Exchange rate integration
    - Payment status tracking
  - Status management
    - Draft, issued, paid, cancelled states
    - Status change tracking
    - Payment date tracking
  - Integration features
    - Contractor association
    - Subscription billing support
    - Payment method tracking
  - OCR text recognition for scanned invoices
  - Periodic/cyclic invoices with configuration
    - Configurable billing cycles
    - Automatic generation based on schedule
    - Template-based generation
    - Custom pricing rules
- **Expense Management**
  - Hierarchical approval workflows
  - Multi-level acceptance paths
  - Delegation rules
  - Budget tracking
- **Tax Management**
  - Tax rates configuration
  - Tax calculations
  - Tax report generation
- **Import/Export**
  - Multiple format support (PDF, CSV, XML)
  - Batch operations
  - Data validation
  - OCR processing for scanned documents
- **Company Services**
  - Company information lookup
  - Verification services
  - Company data enrichment
- **Exchange Rates**
  - Multiple currency support
  - Automatic rate updates
  - Historical rate tracking
  - Custom rate overrides
- **Payment Processing**
  - Online payment integration
  - Multiple payment gateway support
  - Payment status tracking
  - Automatic reconciliation

### 4. Project Management
- **Project Handling**
  - Project creation and configuration
  - Team assignment
  - Project status tracking
  - Resource allocation
- **Task Management**
  - Task creation and assignment
  - Priority and status tracking
  - Time tracking
  - Dependencies management
- **JIRA-like Features**
  - Customizable workflows
  - Issue tracking
  - Sprint planning
  - Agile board support

### 5. Communication & Collaboration
- **Tenant-Scoped Feeds**
  - Announcement system
  - Post creation and management
  - Comment functionality
  - Attachment support
  - Notification system
- **Chat System**
  - Tenant-scoped internal chat
  - App-scoped public chat
  - Public user profiles
  - Chat history and search
  - File sharing
- **Cross-Tenant Interactions**
  - Friendship/contact system
  - Automatic invoice-to-expense conversion
  - Shared workspaces
  - Cross-tenant notifications

### 6. Subscription & Billing
- **Subscription Plans**
  - Tiered pricing models
  - Feature-based plans
  - Storage quotas
  - Usage tracking
  - Trial period support
  - Yearly/monthly billing options
- **Billing Management**
  - Automatic billing
  - Payment processing
  - Invoice generation
  - Usage reporting
  - Promotional discounts and offers
  - Price list management
  - Multiple payment gateway support

### 7. Administration & Tools
- **System Administration**
  - Global admin dashboard
  - Entity management
  - System health monitoring
  - Debug tools
- **Tenant Administration**
  - Tenant settings management
  - User management
  - Resource allocation
  - Usage monitoring
- **Invitation System**
  - Email-based invitations
  - Role assignment
  - Bulk invitations
  - Invitation tracking

---

## Technical Architecture

### System Components
1. **API Layer**
   - Laravel-based RESTful API
   - GraphQL support (optional)
   - API versioning
   - Rate limiting and security

2. **Authentication System**
   - JWT token management
   - OAuth provider integration
   - 2FA implementation
   - Password policies

3. **Database Layer**
   - Multi-tenant architecture
   - Data isolation
   - Performance optimization
   - Backup and recovery

4. **Integration Services**
   - Exchange rate services
   - Company lookup services
   - Email services
   - Payment gateways

### Data Models
- Detailed in `.docs/architecture/database-schema.dbml`
- Follows tenant isolation patterns
- Implements soft deletes
- Maintains audit logs

---

## Development Roadmap

### Phase 1: Foundation
1. Core authentication system with identity verification
2. Basic tenant management with hierarchy
3. User management and employee system
4. Role and permission system

### Phase 2: Document Management
1. Contractor management
2. Basic invoice system with OCR
3. Tax rate management
4. Document storage
5. Approval workflows

### Phase 3: Enhanced Features
1. Exchange rate integration
2. Company lookup service
3. Advanced invoice features
4. Import/export functionality
5. Online payment processing

### Phase 4: Project Management
1. Basic project structure
2. Task management
3. Team collaboration features
4. Workflow automation

### Phase 5: Communication
1. Feed system
2. Chat implementation
3. Cross-tenant interactions
4. Notification system

### Phase 6: Advanced Features
1. Subscription management
2. Advanced admin tools
3. Office management
4. Cross-tenant operations

## Logical Dependency Chain

1. **Foundation Layer**
   - Authentication system
   - Tenant management
   - User management
   - Role-based access control

2. **Core Business Logic**
   - Contractor management
   - Basic invoice system
   - Document management
   - Project structure

3. **Enhanced Features**
   - Integration services
   - Advanced features
   - Automation systems
   - Reporting capabilities

## Risks and Mitigations

### Technical Challenges
- **Multi-tenancy**: Implement strict data isolation
- **Performance**: Optimize for large datasets
- **Security**: Regular security audits
- **Scalability**: Design for horizontal scaling

### MVP Strategy
- Focus on core authentication and tenant management first
- Implement basic invoice management
- Add project management features incrementally
- Prioritize essential integrations

---

## Appendix

### Technical Specifications
- Laravel framework
- MySQL/PostgreSQL database
- RESTful API design
- JWT authentication
- Docker containerization

### Integration Requirements
- Exchange rate APIs
- Company lookup services
- OAuth providers
- Payment gateways

### Models & Database

#### Core Models
- **User**
  - Authentication & authorization
  - Profile management
  - Role-based access control
  - Two-factor authentication support
  - Password reset functionality
- **Tenant**
  - Multi-tenant support
  - Tenant-specific configuration
  - Resource isolation
- **Address** (polymorphic, nullable tenant_id)
  - Multiple address types
  - Address validation
  - Geocoding support
- **BankAccount** (polymorphic: Contractor, User, Tenant)
  - Account validation
  - Secure storage
  - Multiple account types

#### Media & Attachments
- **Media** (using Spatie Media Library)
  - File uploads
  - Image processing
  - Document management
  - Secure storage
  - Polymorphic associations
  - User-specific records support

#### Communication
- **Comment**
  - Threaded discussions (parent_id)
  - Edit tracking via updated_at
  - Markdown support
  - Polymorphic associations
- **ChatRoom**
  - Direct messages
  - Group chats
  - Channels
  - Room configuration
- **ChatMessage**
  - Markdown support
  - Threading support
  - Message types
- **ChatParticipant**
  - Room membership
  - Role management
  - Access control
- **Notification**
  - Multiple channels
  - Template support
  - Delivery tracking
  - User preferences

#### Time & Billing
- **TimeEntry**
  - Duration tracking
  - Project association
  - Billing integration
  - Reporting support
- **SubscriptionPlan**
  - Feature definitions
  - Pricing tiers
  - Trial configuration
- **Subscription**
  - Plan management
  - Trial handling
  - Status tracking
- **Invoice**
  - Line items
  - Tax calculation (net, tax, gross amounts)
  - Contractor and subscription support
  - Customizable numbering templates
  - Multiple currencies with exchange rates
  - Payment tracking
  - Cyclic configuration
  - Status workflow (draft, issued, paid, cancelled)
- **Payment**
  - Transaction processing
  - Gateway integration
  - Status tracking
- **PriceList**
  - Rate configuration
  - Currency support
  - Effective dates
- **Discount**
  - Promotional codes
  - Amount/percentage
  - Validity periods

#### Access Control
- **Invitation**
  - Role assignment
  - Expiration handling
  - Multi-tenant support 
