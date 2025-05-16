<?php

namespace App\Domain\Common\Support;

use App\Domain\Common\Models\Media;

class SignedImageUrlGenerator
{
    public static function generate(Media $media, $modelName, $modelId, string $fileName, int $expiration = 15): string
    {
        $params = [
            'modelName' => $modelName,
            'modelId'   => $modelId,
            'mediaId'   => $media->id,
            'fileName'  => $fileName,
        ];

        return RelativeUrlSigner::generate('images.show', parameters: $params);
    }
}
