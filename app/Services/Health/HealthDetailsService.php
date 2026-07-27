<?php

namespace App\Services\Health;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class HealthDetailsService
{
    /** @var array<string, int> */
    private const STATUS_SEVERITY = [
        'ok' => 0,
        'degraded' => 1,
        'failed' => 2,
    ];

    /**
     * @return array<string, mixed>
     */
    public function build(): array
    {
        $components = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
            'frontend' => $this->checkFrontend(),
        ];

        $response = [
            'schema_version' => 1,
            'status' => $this->worstStatus(array_column($components, 'status')),
            'environment' => (string) config('app.env'),
            'components' => $components,
        ];

        $version = config('app.version');

        if (is_string($version) && $version !== '') {
            $response['version'] = $version;
        }

        return $response;
    }

    /**
     * @return array{status: string, reason?: string}
     */
    private function checkDatabase(): array
    {
        try {
            DB::select('SELECT 1');

            return ['status' => 'ok'];
        } catch (\Throwable $exception) {
            return [
                'status' => 'failed',
                'reason' => $exception->getMessage(),
            ];
        }
    }

    /**
     * @return array{status: string, reason?: string}
     */
    private function checkCache(): array
    {
        try {
            Redis::connection()->ping();

            return ['status' => 'ok'];
        } catch (\Throwable $exception) {
            return [
                'status' => 'failed',
                'reason' => $exception->getMessage(),
            ];
        }
    }

    /**
     * @return array{status: string, reason?: string}
     */
    private function checkStorage(): array
    {
        $disk = (string) config('media-library.disk_name', 's3');

        try {
            // Lightweight probe: exists() hits the disk/bucket without listing contents.
            Storage::disk($disk)->exists('__ops_monitor_health_probe__');

            return ['status' => 'ok'];
        } catch (\Throwable $exception) {
            return [
                'status' => 'failed',
                'reason' => $exception->getMessage(),
            ];
        }
    }

    /**
     * @return array{status: string, reason?: string}
     */
    private function checkFrontend(): array
    {
        $url = (string) config('app.frontend_url');

        if ($url === '') {
            return ['status' => 'ok'];
        }

        try {
            $response = Http::timeout($this->checkTimeoutSeconds())->get($url);

            if ($response->successful()) {
                return ['status' => 'ok'];
            }

            return [
                'status' => 'failed',
                'reason' => sprintf('HTTP %d from frontend origin', $response->status()),
            ];
        } catch (\Throwable $exception) {
            return [
                'status' => 'failed',
                'reason' => $exception->getMessage(),
            ];
        }
    }

    /**
     * @param  list<string>  $statuses
     */
    private function worstStatus(array $statuses): string
    {
        $worst = 'ok';

        foreach ($statuses as $status) {
            if ((self::STATUS_SEVERITY[$status] ?? 0) > (self::STATUS_SEVERITY[$worst] ?? 0)) {
                $worst = $status;
            }
        }

        return $worst;
    }

    private function checkTimeoutSeconds(): int
    {
        return max(1, (int) config('health.check_timeout_seconds', 3));
    }
}
