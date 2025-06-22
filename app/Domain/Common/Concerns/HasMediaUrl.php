<?php

namespace App\Domain\Common\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Model
 */
interface HasMediaUrl
{
    public function getMediaUrl(string $collectionName, string $fileName): string;

    public function getMediaSignedUrl(string $collectionName, ?string $conversionName = null): ?string;
}
