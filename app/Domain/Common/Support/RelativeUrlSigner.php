<?php

namespace App\Domain\Common\Support;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class RelativeUrlSigner
{
    public static function generate($routeName, $parameters = [], int $expiration = 15): string
    {
        $ourTemporaryUrl = URL::signedRoute(
            $routeName,
            parameters: $parameters,
            expiration: now()->addMinutes($expiration),
        );

        if (config('app.trim_api_url')) {
            return Str::of($ourTemporaryUrl)->replace(config('app.api_url'), '');
        }

        return $ourTemporaryUrl;
    }
}
