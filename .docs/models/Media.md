# Media Model

Represents a media file that can be associated with various entities in the system through polymorphic relationships. Uses Spatie Media Library for handling file uploads and associations.

## Attributes

- `id` (uuid) - Primary key
- `tenant_id` (uuid, nullable) - Reference to the tenant (null for user-specific media)
- `model_type` (varchar) - Type of entity the media is attached to
- `model_id` (uuid) - ID of the entity the media is attached to
- `uuid` (varchar) - UUID for the media item
- `collection_name` (varchar) - Name of the media collection (e.g., 'avatar', 'logo', 'documents')
- `name` (varchar) - Original file name
- `file_name` (varchar) - Name of the file on disk
- `mime_type` (varchar) - File MIME type
- `disk` (varchar) - Storage disk identifier
- `conversions_disk` (varchar) - Disk for storing image conversions
- `size` (integer) - File size in bytes
- `manipulations` (json) - Image manipulation settings
- `custom_properties` (json) - Additional properties
- `generated_conversions` (json) - Status of image conversions
- `responsive_images` (json) - Responsive image data
- `order_column` (integer) - For custom ordering
- `created_at` (timestamp) - Creation timestamp
- `updated_at` (timestamp) - Last update timestamp

## Common Use Cases

1. User Profile Images:
   - Collection: 'avatar'
   - Stored without tenant_id
   - Typically one active avatar per user
   - Auto-generates different sizes/formats

2. Contractor Logos:
   - Collection: 'logo'
   - Tenant-scoped
   - One logo per contractor
   - Supports multiple formats/sizes

3. General Attachments:
   - Collection: 'documents', 'images', etc.
   - Can be tenant-scoped or user-specific
   - Multiple files per entity supported
   - Various file types allowed

## Relationships

- `tenant` - BelongsTo relationship to Tenant (only for tenant-scoped media)
- `model` - MorphTo relationship to the entity the file is attached to

## Usage

The Media model is used to:
- Store and manage file uploads via Spatie Media Library
- Handle image conversions and responsive images
- Associate files with various entities
- Track file metadata
- Support file versioning
- Enable file sharing and access control
- Manage storage allocation

## Business Rules

1. File size must be within allowed limits
2. File type must be allowed for the entity/collection
3. Storage quota must be respected
4. File names are automatically sanitized by Media Library
5. Duplicate files should be handled appropriately
6. Files should be stored securely
7. Access permissions should be enforced based on:
   - For tenant-scoped media: tenant access rules apply
   - For user-specific media: only the owning user has access
   - System administrators may have override access
8. Storage cleanup should handle orphaned files
9. Tenant scoping rules:
   - User-specific media are NOT tenant-scoped (tenant_id is null)
   - Media for tenant-owned entities (contractors, projects, etc.) ARE tenant-scoped
   - Access control and queries must respect this distinction
10. Image conversions:
    - Automatically generate required sizes/formats
    - Support responsive images when needed
    - Maintain conversion settings per collection

## API Endpoints

- `GET /api/{entity}/{id}/media` - List media for entity
- `POST /api/{entity}/{id}/media` - Upload new media
- `GET /api/media/{id}` - Get media details
- `GET /api/media/{id}/download` - Download file
- `PUT /api/media/{id}` - Update media metadata
- `DELETE /api/media/{id}` - Delete media
- `POST /api/media/bulk` - Bulk upload
- `GET /api/media/types` - List allowed file types

## Spatie Media Library Integration

This model uses the `HasMedia` and `InteractsWithMedia` traits from Spatie's Media Library package. For detailed usage examples and configuration options, refer to [Spatie Media Library Documentation](https://spatie.be/docs/laravel-medialibrary). 
