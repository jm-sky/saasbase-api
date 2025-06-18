<?php

namespace App\Helpers;

use Symfony\Component\Mime\MimeTypes;

class FileNames
{
    public static function getExtensionFromMimeType(string $mimeType, string $fallback = 'pdf'): string
    {
        $mimeTypes  = new MimeTypes();
        $extensions = $mimeTypes->getExtensions($mimeType);

        return $extensions[0] ?? $fallback;
    }
}
