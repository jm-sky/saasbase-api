# 📨 E-Doręczenia Integration – Initial Implementation

## 🎯 Goal

Enable Tegan users to send and receive official electronic correspondence via selected e-Doręczenia providers (initially: eDO Post). Support certificate-based authentication, provider selection, message management, and on-demand synchronization.

---

## ✅ Features & Scope

### 1. Provider-Agnostic Integration

- [x] Create a generic `EDoreczeniaProviderInterface` with methods:
  - [x] `send(SendMessageDto): SendResultDto`
  - [x] `verifyCertificate(CertificateInfoDto): bool`
  - [x] `getProviderName(): string`
  - [x] `syncMessages(): SyncResultDto` (on-demand sync)

- [ ] Implement eDO Post provider:
  - [ ] Basic API client setup
  - [ ] Authentication handling
  - [ ] Message sending
  - [ ] Message receiving
  - [ ] On-demand sync implementation

- [ ] Create a `EDoreczeniaProviderManager` to resolve correct provider per tenant

---

### 2. Certificate Management

- [x] Allow tenant admin to upload digital certificates (e.g. `.p12`, `.pem`)
- [x] Extract metadata:
  - [x] Issuer
  - [x] Expiry date
  - [x] Subject CN
- [x] Validate and store securely
- [x] Encrypt at rest (Laravel encrypted storage)

---

### 3. Permissions

Define new permissions:

| Key                                | Description                          |
|------------------------------------|--------------------------------------|
| `edoreczenia.view`                | View messages and configuration      |
| `edoreczenia.send`                | Send new messages                    |
| `edoreczenia.manage_certificates` | Upload/manage authentication certs   |
| `edoreczenia.admin`               | Manage providers and sync settings   |

- [ ] Integrate with existing `Role`/`Permission` logic

---

### 4. Database Models

- [x] `edoreczenia_messages`
  - [x] `id`, `tenant_id`, `provider`, `message_id`, `subject`, `status`, `direction`, `sent_at`, `received_at`, `headers_json`, etc.

- [x] `edoreczenia_certificates`
  - [x] `id`, `tenant_id`, `provider`, `file_path`, `fingerprint`, `subject_cn`, `valid_from`, `valid_to`, `created_by`

- [x] `edoreczenia_message_attachments`
  - [x] `id`, `message_id`, `file_name`, `file_size`, `file_path`, `created_at`

---

### 5. Message Handling

#### Sending

- [x] Compose messages with:
  - [x] Subject
  - [x] Recipients
  - [x] Attachments
- [x] Store status as `pending`, then `sent`, `failed`, etc.

#### On-Demand Sync

- [x] Implement sync endpoint/command:
  - [x] Fetch message headers from eDO Post
  - [x] Update `edoreczenia_messages`
  - [x] Detect new messages (direction: `inbound`)
  - [x] Handle attachments

---

### 6. Basic Notifications

- [ ] Notify users on:
  - [ ] Message send success/failure
  - [ ] New incoming message
  - [ ] Certificate expiration (30 days before)

---

### 7. Error Handling

- [x] Implement basic error handling:
  - [x] API errors
  - [x] Certificate validation errors
  - [x] Message sending failures
  - [x] Sync failures

---

### 8. Testing

- [ ] Unit tests:
  - [ ] Provider implementation
  - [ ] Certificate handling
  - [ ] Message processing
- [ ] Integration tests:
  - [ ] eDO Post sandbox integration
  - [ ] End-to-end message flow

---

### 🛠️ Tech Notes

- [x] Use DTOs for input/output across providers
- [x] Use Laravel queues for async processing
- [x] API-only interface for SPA frontend (Vue)
- [x] Basic audit logging for message actions

---

### 📦 Future Extensions (Not in Initial Scope)

- [ ] Automatic periodic synchronization
- [ ] Advanced monitoring and metrics
- [ ] Support additional providers
- [ ] Message thread view
- [ ] Advanced search capabilities
- [ ] Rate limiting and circuit breaker
- [ ] Advanced error recovery
- [ ] Full message body fetching