# UserAddress Model

Represents a physical address associated with a user.

## Attributes

- `id` (uuid) - Primary key
- `user_id` (uuid) - Reference to the user
- `label` (varchar) - Address label/name (e.g., "Home", "Work")
- `country_code` (varchar) - ISO country code
- `street` (varchar) - Street address
- `city` (varchar) - City name
- `zip` (varchar, nullable) - Postal/ZIP code
- `is_default` (boolean) - Whether this is the user's default address
- `created_at` (timestamp) - Creation timestamp
- `updated_at` (timestamp) - Last update timestamp

## Relationships

- `user` - BelongsTo relationship to User

## Usage

The UserAddress model is used to:
- Store multiple addresses per user
- Manage default address selection
- Support address validation and formatting
- Provide address options for invoicing/shipping

## Business Rules

1. A user can have multiple addresses
2. Only one address can be marked as default per user
3. Country code must be a valid ISO code
4. Address label must be unique per user
5. Required fields: label, country_code, street, city
6. First address added is automatically set as default
7. When default address is deleted, another should be marked as default if exists

## API Endpoints

- `GET /api/user/addresses` - List user's addresses
- `POST /api/user/addresses` - Add new address
- `GET /api/user/addresses/{id}` - Get specific address
- `PUT /api/user/addresses/{id}` - Update address
- `DELETE /api/user/addresses/{id}` - Delete address
- `POST /api/user/addresses/{id}/default` - Set as default address 
