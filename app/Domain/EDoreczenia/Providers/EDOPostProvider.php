<?php

namespace App\Domain\EDoreczenia\Providers;

use App\Domain\EDoreczenia\Contracts\EDoreczeniaProviderInterface;
use App\Domain\EDoreczenia\DTOs\CertificateInfoDto;
use App\Domain\EDoreczenia\DTOs\SendMessageDto;
use App\Domain\EDoreczenia\DTOs\SendResultDto;
use App\Domain\EDoreczenia\DTOs\SyncResultDto;
use App\Domain\EDoreczenia\Models\EDoreczeniaMessage;
use App\Domain\EDoreczenia\Models\EDoreczeniaMessageAttachment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EDOPostProvider implements EDoreczeniaProviderInterface
{
    public const PROVIDER_NAME = 'edo_post';

    private string $apiKey;
    private string $apiSecret;
    private string $apiBaseUrl;

    public function __construct(string $apiKey, string $apiSecret, ?string $apiBaseUrl = null)
    {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->apiBaseUrl = $apiBaseUrl ?? config('edoreczenia.providers.edo_post.api_url');
    }

    public function send(SendMessageDto $message): SendResultDto
    {
        try {
            $response = Http::withHeaders($this->getAuthHeaders())
                ->post("{$this->apiBaseUrl}/messages", $message->toArray());

            if (!$response->successful()) {
                Log::error('Failed to send message via eDO Post', [
                    'error' => $response->json(),
                    'message' => $message->toArray(),
                ]);

                return new SendResultDto(
                    success: false,
                    messageId: null,
                    sentAt: null,
                    error: $response->json('message', 'Unknown error occurred')
                );
            }

            return new SendResultDto(
                success: true,
                messageId: $response->json('id'),
                sentAt: now(),
                error: null
            );
        } catch (\Exception $e) {
            Log::error('Exception while sending message via eDO Post', [
                'error' => $e->getMessage(),
                'message' => $message->toArray(),
            ]);

            return new SendResultDto(
                success: false,
                messageId: null,
                sentAt: null,
                error: $e->getMessage()
            );
        }
    }

    public function verifyCertificate(CertificateInfoDto $certificate): bool
    {
        try {
            $response = Http::withHeaders($this->getAuthHeaders())
                ->post("{$this->apiBaseUrl}/certificates/verify", $certificate->toArray());

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Exception while verifying certificate via eDO Post', [
                'error' => $e->getMessage(),
                'certificate' => $certificate->toArray(),
            ]);

            return false;
        }
    }

    public function getProviderName(): string
    {
        return self::PROVIDER_NAME;
    }

    public function syncMessages(): SyncResultDto
    {
        try {
            $response = Http::withHeaders($this->getAuthHeaders())
                ->get("{$this->apiBaseUrl}/messages");

            if (!$response->successful()) {
                Log::error('Failed to sync messages via eDO Post', [
                    'error' => $response->json(),
                ]);

                return new SyncResultDto(
                    success: false,
                    syncedAt: null,
                    error: $response->json('message', 'Unknown error occurred')
                );
            }

            $messages = collect($response->json('data', []));
            $this->processMessages($messages);

            return new SyncResultDto(
                success: true,
                syncedAt: now(),
                error: null
            );
        } catch (\Exception $e) {
            Log::error('Exception while syncing messages via eDO Post', [
                'error' => $e->getMessage(),
            ]);

            return new SyncResultDto(
                success: false,
                syncedAt: null,
                error: $e->getMessage()
            );
        }
    }

    private function getAuthHeaders(): array
    {
        return [
            'X-API-Key' => $this->apiKey,
            'X-API-Secret' => $this->apiSecret,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    private function processMessages(Collection $messages): void
    {
        $messages->each(function (array $messageData) {
            $message = EDoreczeniaMessage::updateOrCreate(
                ['external_id' => $messageData['id']],
                [
                    'tenant_id' => $messageData['tenant_id'],
                    'content' => $messageData['content'],
                    'created_by' => $messageData['created_by'] ?? null,
                ]
            );

            if (isset($messageData['attachments'])) {
                collect($messageData['attachments'])->each(function (array $attachmentData) use ($message) {
                    EDoreczeniaMessageAttachment::updateOrCreate(
                        [
                            'message_id' => $message->id,
                            'external_id' => $attachmentData['id'],
                        ],
                        [
                            'filename' => $attachmentData['filename'],
                            'mime_type' => $attachmentData['mime_type'],
                            'size' => $attachmentData['size'],
                            'url' => $attachmentData['url'],
                        ]
                    );
                });
            }
        });
    }
}
