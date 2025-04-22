<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

class SetLocaleFromHeader
{
    public function handle(Request $request, \Closure $next)
    {
        // Get supported locales from config
        $supportedLocales = Config::get('app.supported_locales', ['en']);

        // Laravel helper to parse Accept-Language header
        $locale = $request->getPreferredLanguage($supportedLocales);

        if ($locale) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
