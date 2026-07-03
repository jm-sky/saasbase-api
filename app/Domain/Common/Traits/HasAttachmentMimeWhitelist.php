<?php

namespace App\Domain\Common\Traits;

/**
 * Restricts generic attachment uploads to common business document/image
 * types. Deliberately excludes SVG, HTML and other script-capable formats —
 * attachment controllers serve files with Content-Disposition: inline for
 * previews, so an unrestricted upload allows stored XSS (e.g. an SVG with
 * an embedded <script> rendered inline in the app's own origin).
 */
trait HasAttachmentMimeWhitelist
{
    protected function allowedAttachmentMimes(): array
    {
        return [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/webp',
            'image/gif',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/csv',
            'text/plain',
            'application/zip',
        ];
    }
}
