# Tasks

## 1. [x] Add middleware to set locale based on Accept Language header. 

```
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

class SetLocaleFromHeader
{
    public function handle(Request $request, Closure $next)
    {
        // Pobierz listę z configu
        $supportedLocales = Config::get('app.supported_locales', ['en']);

        // Laravelowy helper do analizy Accept-Language
        $locale = $request->getPreferredLanguage($supportedLocales);

        if ($locale) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
```
-----------------------------------------------------

## 2. [ ] Add seeders for all models 
## 3. [ ] Add countries to Country seeders JSON file (European countries, and most large countries)
## 4. [ ] Add routes & actions for current user - change settings, reset password etc. 