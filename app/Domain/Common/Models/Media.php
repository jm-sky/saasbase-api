<?php

namespace App\Domain\Common\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\MediaCollections\Models\Media as MediaLibraryMedia;

/**
 * @property string     $uuid
 * @property string     $tenant_id
 * @property string     $model_type
 * @property string|int $model_id
 * @property string     $collection_name
 * @property string     $name
 * @property string     $file_name
 * @property string     $mime_type
 * @property string     $disk
 * @property string     $conversions_disk
 * @property string     $type
 * @property string     $extension
 * @property string     $humanReadableSize
 * @property string     $preview_url
 * @property string     $original_url
 * @property int        $size
 * @property ?int       $order_column
 * @property array      $manipulations
 * @property array      $custom_properties
 * @property array      $generated_conversions
 * @property array      $responsive_images
 * @property array      $meta
 * @property ?Carbon    $created_at
 * @property ?Carbon    $updated_at
 */
class Media extends MediaLibraryMedia
{
    use HasUuids;

    protected $fillable = [
        'tenant_id',
    ];
}
