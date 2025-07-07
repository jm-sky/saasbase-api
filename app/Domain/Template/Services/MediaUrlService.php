<?php

namespace App\Domain\Template\Services;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaUrlService
{
    /**
     * Get secure URL for media file.
     */
    public function getSecureUrl(Media $media): string
    {
        // For local files, use the URL method
        if ('local' === config('filesystems.default')) {
            return $media->getUrl();
        }

        // For S3 or other cloud storage, generate temporary signed URL
        return $media->getTemporaryUrl(now()->addHours(24));
    }

    /**
     * Get public URL for media file (non-secured).
     */
    public function getPublicUrl(Media $media): string
    {
        return $media->getUrl();
    }

    /**
     * Get full URL for media file with fallback.
     */
    public function getUrlWithFallback(Media $media, ?string $fallbackUrl = null): string
    {
        try {
            return $this->getSecureUrl($media);
        } catch (\Exception $e) {
            return $fallbackUrl ?: '';
        }
    }
}
