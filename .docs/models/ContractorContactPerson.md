# ContractorContactPerson Model

Represents a person of contact associated with a contractor, such as account managers or billing specialists.

## Attributes

- `id` (uuid) - Primary key
- `contractor_id` (uuid) - Reference to the contractor this person belongs to
- `name` (varchar) - Full name of the contact person
- `email` (varchar, nullable) - Contact email address
- `phone` (varchar, nullable) - Contact phone number
- `position` (varchar, nullable) - Job title or role within the contractor's organization
- `description` (text, nullable) - Additional notes or responsibilities
- `created_at` (timestamp) - Creation timestamp
- `updated_at` (timestamp) - Last update timestamp

## Relationships

- `contractor` - BelongsTo relationship to Contractor

## Business Rules

1. No uniqueness constraint on email or phone (can have duplicates if needed)
2. Multiple contact persons can be stored per contractor for different roles
3. At least one of email or phone should be provided for contact purposes

## Usage

The ContractorContactPerson model is used to:
- Maintain a list of contact persons for each contractor
- Specify points of contact for specific matters (finance, logistics, support)
- Enable efficient communication with contractor representatives
- Track roles and responsibilities of contractor contacts
- Support relationship management with contractors 
