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

## 2. [ ] Add seeders for all models. I need seeder for skill categories and skills, lets start with IT area. I want to have a demo with few users, few tanants, some contractors, products, projects & tasks.
## 3. [ ] Add countries to Country seeders JSON file (European countries, and most large countries)
## 4. [ ] Add routes & actions for current user - change settings, reset password etc. 
## 5. [ ] Add trait (BelongsToTenant?) tht apply global scope for models with tenant_id. We'll store tenant_id in session or jwt. Its for security.
## 6. [ ] Refactor foreign keys. i.e. refer country code (pl, de) instead of id. Analyse.