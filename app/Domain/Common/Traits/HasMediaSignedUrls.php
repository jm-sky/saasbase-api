<?php

namespace App\Domain\Common\Traits;

use App\Domain\Common\Models\Media;
use App\Domain\Common\Support\SignedImageUrlGenerator;
use Illuminate\Support\Str;

trait HasMediaSignedUrls
{
    public const TEMPORARY_URL_EXPIRATION_TIME = 15;

    public function getMediaSignedUrl(string $collectionName, ?string $conversionName = null): ?string
    {
        /** @var Media $media */
        $media = $this->getFirstMedia($collectionName);

        if (!$media) {
            return null;
        }

        $modelName = Str::of($this->getTable())->basename()->plural()->kebab()->value();

        return SignedImageUrlGenerator::generate(
            media: $media,
            modelName: $modelName,
            modelId: $this->id,
            fileName: $media->file_name,
            expiration: self::TEMPORARY_URL_EXPIRATION_TIME,
            params: [
                'conversion' => $conversionName,
            ],
        );
    }
}
