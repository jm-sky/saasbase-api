<?php

namespace App\Http\Controllers;

use App\Domain\Ai\Services\AiChatService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HealthController extends Controller
{
    public function health()
    {
        $results = [];

        $results['redis']      = $this->getRedis();
        $results['s3']         = $this->getS3();
        $results['minio']      = $this->getMinio();
        $results['soketi']     = $this->getSoketi();
        $results['openrouter'] = $this->getOpenRouter();

        return response()->json([
            'status'   => 'ok',
            'services' => $results,
        ]);
    }

    // Redis (Upstash)
    protected function getRedis(): array
    {
        $status = [];

        try {
            Redis::connection()->ping();
            $status['status'] = 'ok';
        } catch (\Exception $e) {
            $status['status'] = 'error: ' . $e->getMessage();
        }

        if ($this->showDetails()) {
            $status['url'] = config('database.redis.default.host');
        }

        return $status;
    }

    // S3 (Minio)
    protected function getMinio(): array
    {
        $status = [];

        try {
            Storage::disk('minio')->exists('/');
            $status['status'] = 'ok';
        } catch (\Exception $e) {
            $status['status'] = 'error: ' . $e->getMessage();
        }

        if ($this->showDetails()) {
            $status['url'] = config('filesystems.disks.minio.endpoint');
        }

        return $status;
    }

    // S3 (Scaleway)
    protected function getS3(): array
    {
        $status = [];

        try {
            Storage::disk('s3')->exists('/');
            $status['status'] = 'ok';
        } catch (\Exception $e) {
            $status['status'] = 'error: ' . $e->getMessage();
        }

        if ($this->showDetails()) {
            $status['url'] = config('filesystems.disks.s3.endpoint');
        }

        return $status;
    }

    // Soketi
    protected function getSoketi(): array
    {
        $status = [];

        try {
            $url              = Str::of(config('broadcasting.connections.pusher.options.host'))->replace('wss://', 'https://');
            $response         = Http::timeout(2)->get($url);
            $status['status'] = $response->successful() ? 'ok' : 'error: bad response';
        } catch (\Exception $e) {
            $status['status'] = 'error: ' . $e->getMessage();
        }

        if ($this->showDetails()) {
            $status['url'] = $url;
        }

        return $status;
    }

    // OpenRouter
    protected function getOpenRouter(): array
    {
        $status = [];

        try {
            $url              = AiChatService::getOpenRouterUrl();
            $response         = Http::timeout(2)->get($url);
            $status['status'] = $response->successful() ? 'ok' : 'error: bad response';
        } catch (\Exception $e) {
            $status['status'] = 'error: ' . $e->getMessage();
        }

        if ($this->showDetails()) {
            $status['url'] = $url;
        }

        return $status;
    }

    protected function showDetails(): bool
    {
        if (app()->environment('local')) {
            return true;
        }

        if (request()->user()?->isAdmin()) {
            return true;
        }

        return false;
    }
}
