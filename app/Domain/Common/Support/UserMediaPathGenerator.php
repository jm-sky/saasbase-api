<?php

namespace App\Domain\Common\Support;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class UserMediaPathGenerator implements PathGenerator
{
    public const GLOBAL_USER_ID = 'system';

    /*
     * Get the path for the given media, relative to the root storage path.
     */
    public function getPath(Media $media): string
    {
        return $this->getBasePath($media) . '/';
    }

    /*
     * Get the path for conversions of the given media, relative to the root storage path.
     */
    public function getPathForConversions(Media $media): string
    {
        return $this->getBasePath($media) . '/conversions/';
    }

    /*
     * Get the path for responsive images of the given media, relative to the root storage path.
     */
    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getBasePath($media) . '/responsive-images/';
    }

    /*
     * Get a unique base path for the given media.
     */
    protected function getBasePath(Media $media): string
    {
        $prefix   = config('media-library.prefix', '');
        $userId   = $media->model_id ?? self::GLOBAL_USER_ID;

        if ('' !== $prefix) {
            return $prefix . '/users/' . $userId . '/' . $media->getKey();
        }

        return 'users/' . $userId . '/' . $media->getKey();
    }
}
