# File Attachments Implementation

## Overview
The system implements file attachment support using Spatie Media Library, allowing models to handle various types of file attachments with features like collections, conversions, and MinIO integration.

## Implementation Components

### HasAttachments Trait
This trait provides common media handling functionality across models:

```php
namespace App\Domain\Common\Traits;

use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\HasMedia;

trait HasAttachments
{
    use InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        // Common collections
        $this->addMediaCollection('attachments')
            ->acceptsMimeTypes(['application/pdf', 'image/*', 'application/msword'])
            ->useDisk('minio');

        // Specific collection for profile images
        if (method_exists($this, 'registerProfileImageCollection')) {
            $this->registerProfileImageCollection();
        }
    }

    public function registerMediaConversions(Media $media = null): void
    {
        // Common conversions
        $this->addMediaConversion('thumbnail')
            ->width(200) // TODO: should be in a named variable or config
            ->height(200) // TODO: should be in a named variable or config
            ->performOnCollections('attachments');

        // Model-specific conversions
        if (method_exists($this, 'registerCustomConversions')) {
            $this->registerCustomConversions($media);
        }
    }
}
```

## Model Integration

### Supported Models
The following models support file attachments:
- User (profile images)
- Project (project files)
- Task (task attachments)
- Contractor (documents)
- Comment (attachments)
- Tenant (branding assets)
- Invoice (PDFs, scans)
- Product (images)

### Example Model Implementation
```php
use App\Domain\Common\Traits\HasAttachments;
use Spatie\MediaLibrary\HasMedia;

class Project implements HasMedia
{
    use HasAttachments;

    protected function registerProfileImageCollection(): void
    {
        $this->addMediaCollection('project_files')
            ->acceptsMimeTypes(['application/pdf', 'image/*'])
            ->useDisk('minio');
    }

    protected function registerCustomConversions($media): void
    {
        $this->addMediaConversion('preview')
            ->width(400) // TODO: should be in a named variable or config
            ->height(300) // TODO: should be in a named variable or config
            ->performOnCollections('project_files');
    }
}
```

## Storage Configuration

### MinIO Integration
```php
// config/filesystems.php
'disks' => [
    'minio' => [
        'driver' => 's3',
        'endpoint' => env('MINIO_ENDPOINT', 'http://minio:9000'),
        'use_path_style_endpoint' => true,
        'key' => env('MINIO_KEY'),
        'secret' => env('MINIO_SECRET'),
        'region' => env('MINIO_REGION'),
        'bucket' => env('MINIO_BUCKET'),
    ],
]
```

## API Endpoints

### Attachment Management
Each model with attachment support has its own controller for managing files:

```php
// Example routes
Route::prefix('api')->group(function () {
    Route::post('projects/{project}/attachments', [ProjectAttachmentsController::class, 'store']);
    Route::delete('projects/{project}/attachments/{media}', [ProjectAttachmentsController::class, 'destroy']);
});
```

### API Resources
Media URLs are not included in API responses


## Testing

1. Unit Tests:
   - Media collection registration
   - Conversion configuration
   - URL generation

2. Feature Tests:
   - File upload/deletion
   - Conversion generation
   - MinIO integration
   - API endpoints

## Security Considerations

1. File Type Validation:
   - Strict MIME type checking
   - Size limits per collection
   - Malware scanning (optional)

2. Access Control:
   - Tenant isolation for media
   - User permissions for operations
   - Signed URLs for temporary access 
