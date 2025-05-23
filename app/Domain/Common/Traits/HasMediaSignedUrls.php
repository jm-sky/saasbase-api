<?php

namespace App\Domain\Common\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Domain\Common\Models\Media;
use App\Domain\Common\Support\SignedImageUrlGenerator;
use Spatie\MediaLibrary\HasMedia;

/**
 * @mixin HasMedia
 */
trait HasMediaSignedUrls
{
    public const TEMPORARY_URL_EXPIRATION_TIME = 60;

    public function getMediaSignedUrl(string $collectionName, ?string $conversionName = null): ?string
    {
        /** @var Media $media */
        $media = $this->getFirstMedia($collectionName);

        if (!$media) {
            return null;
        }

        $modelName = Str::of($this->getTable())->basename()->plural()->kebab()->value();

        // Optional: add a cache-friendly tag like media UUID or timestamp
        $cacheKey = sprintf(
            'media:signed-url:%s:%s:%s:%s:%s',
            $this->getTable(),
            $this->getKey(),
            $collectionName,
            $conversionName ?? 'original',
            $media->updated_at?->timestamp ?? '0'
        );

        return Cache::remember(
            $cacheKey,
            now()->addSeconds(self::TEMPORARY_URL_EXPIRATION_TIME / 2),
            function () use ($media, $modelName, $conversionName) {
                return SignedImageUrlGenerator::generate(
                    media: $media,
                    modelName: $modelName,
                    modelId: $this->getKey(),
                    fileName: $media->file_name,
                    expiration: self::TEMPORARY_URL_EXPIRATION_TIME,
                    params: [
                        'conversion' => $conversionName,
                    ],
                );
            }
        );
    }
}
