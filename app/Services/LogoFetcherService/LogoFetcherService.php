<?php

namespace App\Services\LogoFetcherService;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class LogoFetcherService
{
    public function fetchAndStore(?string $website, ?string $email, string $storagePath): ?string
    {
        $url = $this->getBestLogoUrl($website, $email);

        if ($url) {
            try {
                $response = Http::get($url);

                if ($response->successful()) {
                    $filename = basename(parse_url($url, PHP_URL_PATH)) ?: 'logo.png';
                    $fullPath = "{$storagePath}/{$filename}";

                    Storage::put($fullPath, $response->body());

                    return Storage::url($fullPath); // Public URL (adjust if needed)
                }
            } catch (\Exception $e) {
                // Log and continue
            }
        }

        return null;
    }

    public function getBestLogoUrl(?string $website, ?string $email): ?string
    {
        if ($website) {
            $domain = parse_url($website, PHP_URL_HOST) ?: $website;

            // Option 1: DuckDuckGo favicon
            $duckDuckGo = "https://icons.duckduckgo.com/ip3/{$domain}.ico";
            if ($this->urlExists($duckDuckGo)) return $duckDuckGo;

            // Option 2: Clearbit (optional)
            $clearbit = "https://logo.clearbit.com/{$domain}";
            if ($this->urlExists($clearbit)) return $clearbit;
        }

        if ($email) {
            $hash = md5(strtolower(trim($email)));
            $gravatar = "https://www.gravatar.com/avatar/{$hash}?d=404";
            if ($this->urlExists($gravatar)) return $gravatar;
        }

        return null;
    }

    private function urlExists(string $url): bool
    {
        try {
            $response = Http::timeout(5)->head($url);
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
