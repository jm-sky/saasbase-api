<?php

namespace App\Services\LogoFetcherService;

use App\Domain\Contractors\Enums\ContractorActivityType;
use App\Domain\Contractors\Models\Contractor;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class LogoFetcherService
{
    protected string $tempFile;

    protected ImageManager $imageManager;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
    }

    public function fetchAndStore(Contractor $contractor, ?string $website, ?string $email): bool
    {
        $url = $this->getBestLogoUrl($website, $email);

        if ($url) {
            try {
                $response = Http::get($url);

                if ($response->successful()) {
                    $contractor->clearMediaCollection('logo');

                    // Create a temporary file
                    $this->saveLogoToTempFile($response->body(), $response->header('Content-Type'));

                    // Add the temporary file to media collection
                    $media = $contractor->addMedia($this->tempFile)->toMediaCollection('logo');

                    // Clean up the temporary file
                    $this->cleanUpTempFile();

                    $contractor->logModelActivity(ContractorActivityType::LogoCreated->value, $media);

                    return true;
                }
            } catch (\Exception $e) {
                Log::error('[LogoFetcherService] Error fetching logo', ['error' => $e->getMessage()]);
            }
        }

        return false;
    }

    public function getBestLogoUrl(?string $website, ?string $email): ?string
    {
        if ($website) {
            $domain = parse_url($website, PHP_URL_HOST) ?: $website;

            // Option 1: DuckDuckGo favicon
            $duckDuckGo = "https://icons.duckduckgo.com/ip3/{$domain}.ico";

            if ($this->urlExists($duckDuckGo)) {
                return $duckDuckGo;
            }

            // Option 2: Clearbit (optional)
            $clearbit = "https://logo.clearbit.com/{$domain}";

            if ($this->urlExists($clearbit)) {
                return $clearbit;
            }
        }

        if ($email) {
            $hash     = md5(strtolower(trim($email)));
            $gravatar = "https://www.gravatar.com/avatar/{$hash}?d=404";

            if ($this->urlExists($gravatar)) {
                return $gravatar;
            }
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

    protected function getTempFileName(string $contentType): string
    {
        $tempDir   = storage_path('app/public');
        $tempName  = uniqid('logo_', true);
        $extension = $this->getExtensionFromMimeType($contentType);

        return $tempDir . '/' . $tempName . '.' . $extension;
    }

    private function saveLogoToTempFile(string $responseBody, string $contentType): void
    {
        $this->tempFile = $this->getTempFileName($contentType);

        file_put_contents($this->tempFile, $responseBody);

        if ($this->isIco($contentType)) {
            $this->convertIcoToPng($responseBody);
        }
    }

    private function isIco(string $contentType): bool
    {
        return str_contains($contentType, 'image/vnd.microsoft.icon');
    }

    private function getExtensionFromMimeType(string $mimeType): string
    {
        return match ($mimeType) {
            'image/jpeg'               => 'jpg',
            'image/png'                => 'png',
            'image/gif'                => 'gif',
            'image/webp'               => 'webp',
            'image/vnd.microsoft.icon' => 'ico',
            default                    => 'png', // fallback to png if unknown
        };
    }

    private function convertIcoToPng(string $responseBody): void
    {
        $pngFile = str_replace('.ico', '.png', $this->tempFile);
        $this->imageManager->read($this->tempFile)->save($pngFile);
        unlink($this->tempFile); // Remove original ICO file
        $this->tempFile = $pngFile;
    }

    private function cleanUpTempFile(): void
    {
        try {
            unlink($this->tempFile);
        } catch (\Exception $e) {
        }
    }
}
