<?php

namespace App\Domain\Common\Support;

use Illuminate\Support\Str;

class RelativeRouteUrl
{
    public static function generate(string $routeName, array $parameters = []): string
    {
        $routeUrl = route($routeName, $parameters);

        if (config('app.trim_api_url')) {
            return Str::of($routeUrl)->replace(config('app.api_url'), '');
        }

        return $routeUrl;
    }
}
