<?php

namespace App\Domain\Ai\Services;

use GuzzleHttp\Client;

class OpenRouterService
{
    public const DEFAULT_OPENROUTER_URL = 'https://openrouter.ai/api/v1/chat/completions';

    public const DEFAULT_MODEL = 'openai/gpt-3.5-turbo';

    protected $guzzleClient;

    protected string $openRouterUrl;

    public string $model;

    protected string $apiKey;

    protected int $timeout = 60;

    protected int $connectTimeout = 10;

    public function __construct(
        ?string $model = null,
        ?string $openRouterUrl = null
    ) {
        $this->openRouterUrl = $openRouterUrl ?? config('services.openrouter.url', self::DEFAULT_OPENROUTER_URL);
        $this->model         = $model ?? config('services.openrouter.model', self::DEFAULT_MODEL);
        $this->apiKey        = config('services.openrouter.key');
        $this->guzzleClient  = new Client();
    }

    public function createStreamedResponse(array $messages)
    {
        return $this->guzzleClient->post($this->openRouterUrl, [
            'headers' => $this->getOpenRouterHeaders(),
            'json'    => [
                'model'    => $this->model,
                'messages' => $messages,
                'stream'   => true,
            ],
            'stream'          => true,
            'timeout'         => 60,
            'connect_timeout' => 10,
        ]);
    }

    public function createNonStreamedResponse(array $messages)
    {
        return $this->guzzleClient->post($this->openRouterUrl, [
            'headers' => $this->getOpenRouterHeaders(),
            'json'    => [
                'model'    => $this->model,
                'messages' => $messages,
                'stream'   => false,
            ],
            'timeout'         => 60,
            'connect_timeout' => 10,
        ]);
    }

    protected function getOpenRouterHeaders(): array
    {
        return [
            'Authorization' => "Bearer {$this->apiKey}",
            'Accept'        => 'text/event-stream',
        ];
    }
}
