# e-Doręczenia Integration

## Overview
This document outlines the implementation of e-Doręczenia (electronic delivery) integration in our system.

## Tasks

### Database Schema
- [x] Create `edoreczenia_certificates` table
  - [x] Add UUID support
  - [x] Add tenant relationship
  - [x] Add certificate fields
  - [x] Add validation fields
  - [x] Add timestamps
- [x] Create `edoreczenia_messages` table
  - [x] Add UUID support
  - [x] Add tenant relationship
  - [x] Add message fields
  - [x] Add timestamps
- [x] Create `edoreczenia_message_attachments` table
  - [x] Add UUID support
  - [x] Add message relationship
  - [x] Add attachment fields
  - [x] Add timestamps

### Models
- [x] Create `EDoreczeniaCertificate` model
  - [x] Add UUID support
  - [x] Add tenant relationship
  - [x] Add certificate fields
  - [x] Add validation fields
  - [x] Add timestamps
- [x] Create `EDoreczeniaMessage` model
  - [x] Add UUID support
  - [x] Add tenant relationship
  - [x] Add message fields
  - [x] Add timestamps
- [x] Create `EDoreczeniaMessageAttachment` model
  - [x] Add UUID support
  - [x] Add message relationship
  - [x] Add attachment fields
  - [x] Add timestamps

### DTOs
- [x] Create `CertificateInfoDto`
  - [x] Add certificate fields
  - [x] Add validation fields
  - [x] Add toArray method
- [x] Create `SendMessageDto`
  - [x] Add message fields
  - [x] Add recipient fields
  - [x] Add attachment fields
  - [x] Add toArray method
- [x] Create `SendResultDto`
  - [x] Add success field
  - [x] Add messageId field
  - [x] Add sentAt field
  - [x] Add error field
  - [x] Add toArray method
- [x] Create `SyncResultDto`
  - [x] Add success field
  - [x] Add syncedAt field
  - [x] Add error field
  - [x] Add toArray method

### Providers
- [x] Create `EDoreczeniaProviderInterface`
  - [x] Add send method
  - [x] Add verifyCertificate method
  - [x] Add getProviderName method
  - [x] Add syncMessages method
- [x] Create `EDOPostProvider`
  - [x] Add send implementation
  - [x] Add verifyCertificate implementation
  - [x] Add getProviderName implementation
  - [x] Add syncMessages implementation
- [x] Create `EDoreczeniaProviderManager`
  - [x] Add provider registration
  - [x] Add provider resolution
  - [x] Add tenant provider resolution
- [x] Create `EDoreczeniaServiceProvider`
  - [x] Add configuration merging
  - [x] Add provider manager registration
  - [x] Add default provider binding
  - [x] Add configuration publishing

### Configuration
- [x] Create `edoreczenia.php` config file
  - [x] Add provider settings
  - [x] Add default provider setting
  - [x] Add certificate settings
  - [x] Add message settings

### Next Steps
- [ ] Create controllers for certificate management
- [ ] Create controllers for message management
- [ ] Add certificate validation service
- [ ] Add message synchronization service
- [ ] Add certificate expiration notification service
- [ ] Add tests for all components 
