<?php

namespace App\Services\LogoFetcher;

use App\Domain\Contractors\Enums\ContractorActivityType;
use App\Domain\Contractors\Models\Contractor;
use App\Services\LogoFetcher\DTOs\LogoCandidate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class LogoFetcherService
{
    const CACHING_DAYS = 7;
    protected bool $debug;
    protected bool $duckDuckGo = true;
    protected bool $clearbit = true;
    protected bool $gravatar = true;
    protected string $tempFile;
    protected ImageManager $imageManager;

    public function __construct()
    {
        $this->debug = config('app.env') === 'local';
        $this->imageManager = new ImageManager(new Driver());
    }

    public function fetchAndStore(Contractor $contractor, ?string $website, ?string $email): bool
    {
        $url = $this->getBestLogoUrl($website, $email);

        if (!$url) {
            return false;
        }

        try {
            $this->log('Fetching logo', ['url' => $url]);

            $response = Http::get($url);
            if (!$response->successful()) {
                $this->log('Logo not found', ['url' => $url]);
                return false;
            }

            $contractor->clearMediaCollection('logo');

            $this->saveLogoToTempFile($response->body(), $response->header('Content-Type'));

            $media = $contractor->addMedia($this->tempFile)->toMediaCollection('logo');

            $this->cleanUpTempFile();

            $contractor->logModelActivity(ContractorActivityType::LogoCreated->value, $media);

            return true;
        } catch (\Exception $e) {
            Log::error('[LogoFetcherService] Error fetching logo', ['error' => $e->getMessage()]);
        }

        return false;
    }

    public function getBestLogoUrl(?string $website, ?string $email): ?string
    {
        if (!$website && !$email) {
            return null;
        }

        $domain = $website
            ? parse_url($website, PHP_URL_HOST) ?: $website
            : null;

        $candidates = $this->fetchAllCandidates($domain, $email);

        $best = $this->scoreCandidates($candidates);

        if ($this->debug) {
            $this->log('Logo candidates', [
                'domain' => $domain,
                'email' => $email,
                'chosen' => $best?->url,
                'candidates' => array_map(fn($c) => [
                    'url' => $c->url,
                    'source' => $c->source,
                    'mime' => $c->mime,
                    'width' => $c->width,
                    'height' => $c->height,
                    'score' => $c->score,
                ], $candidates),
            ]);
        }

        return $best?->url;
    }

    protected function fetchAllCandidates(?string $domain, ?string $email): array
    {
        $cacheKey = "logo_candidates:" . md5("{$domain}|{$email}");

        return Cache::remember($cacheKey, now()->addDays(self::CACHING_DAYS), function () use ($domain, $email) {
            $urls = [];

            if ($this->clearbit && $domain) {
                $urls[] = ['url' => "https://logo.clearbit.com/{$domain}", 'source' => 'Clearbit'];
            }

            if ($this->duckDuckGo && $domain) {
                $urls[] = ['url' => "https://icons.duckduckgo.com/ip3/{$domain}.ico", 'source' => 'DuckDuckGo'];
            }

            if ($this->gravatar && $email) {
                $hash = md5(strtolower(trim($email)));
                $urls[] = ['url' => "https://www.gravatar.com/avatar/{$hash}?d=404", 'source' => 'Gravatar'];
            }

            $responses = Http::pool(fn ($pool) =>
                collect($urls)->map(fn ($item) => $pool->as($item['url'])->get($item['url']))
            );

            $candidates = [];

            foreach ($responses as $url => $response) {
                if (!$response->successful()) continue;

                try {
                    $image = $this->imageManager->readFromBuffer($response->body());
                    $mime = $response->header('Content-Type');

                    $candidates[] = new LogoCandidate(
                        url: $url,
                        source: collect($urls)->firstWhere('url', $url)['source'],
                        width: $image->width(),
                        height: $image->height(),
                        mime: $mime
                    );
                } catch (\Throwable $e) {
                    continue;
                }
            }

            return $candidates;
        });
    }

    protected function scoreCandidates(array $candidates): ?LogoCandidate
    {
        foreach ($candidates as $candidate) {
            $score = 0;

            $score += match (true) {
                str_contains($candidate->mime, 'png') => 50,
                str_contains($candidate->mime, 'jpeg') => 40,
                str_contains($candidate->mime, 'webp') => 40,
                str_contains($candidate->mime, 'ico') => -20,
                default => 0,
            };

            $score += min(100, (int) ($candidate->resolution() / 1000));

            $ratio = $candidate->ratio();
            if ($ratio && $ratio > 0.8 && $ratio < 1.2) {
                $score += 30;
            } elseif ($ratio && $ratio > 0.5 && $ratio < 2.0) {
                $score += 10;
            }

            $candidate->score = $score;
        }

        return collect($candidates)
            ->sortByDesc('score')
            ->first();
    }

    protected function saveLogoToTempFile(string $responseBody, string $contentType): void
    {
        $this->tempFile = $this->getTempFileName($contentType);

        file_put_contents($this->tempFile, $responseBody);

        if ($this->isIco($contentType)) {
            $this->convertIcoToPng();
        }
    }

    protected function getTempFileName(string $contentType): string
    {
        $tempDir = storage_path('app/public');
        $tempName = uniqid('logo_', true);
        $extension = $this->getExtensionFromMimeType($contentType);

        return "{$tempDir}/{$tempName}.{$extension}";
    }

    protected function getExtensionFromMimeType(string $mimeType): string
    {
        return match ($mimeType) {
            'image/jpeg'               => 'jpg',
            'image/png'                => 'png',
            'image/gif'                => 'gif',
            'image/webp'               => 'webp',
            'image/vnd.microsoft.icon' => 'ico',
            default                    => 'png',
        };
    }

    protected function isIco(string $contentType): bool
    {
        return str_contains($contentType, 'image/vnd.microsoft.icon');
    }

    protected function convertIcoToPng(): void
    {
        $pngFile = str_replace('.ico', '.png', $this->tempFile);
        $this->imageManager->read($this->tempFile)->save($pngFile);
        unlink($this->tempFile);
        $this->tempFile = $pngFile;
    }

    protected function cleanUpTempFile(): void
    {
        try {
            unlink($this->tempFile);
        } catch (\Exception $e) {
        }
    }

    protected function log(string $message, array $context = []): void
    {
        Log::info('[LogoFetcherService] ' . $message, $context);
    }
}
