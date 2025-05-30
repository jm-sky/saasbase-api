<?php

namespace App\Domain\EDoreczenia\Services;

use App\Domain\EDoreczenia\Contracts\EDoreczeniaProviderInterface;
use App\Domain\EDoreczenia\DTOs\CertificateInfoDto;
use App\Domain\EDoreczenia\DTOs\SendMessageDto;
use App\Domain\EDoreczenia\DTOs\SendResultDto;
use App\Domain\EDoreczenia\DTOs\SyncResultDto;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EDOPostProvider implements EDoreczeniaProviderInterface
{
    private string $baseUrl;

    private string $mailboxAddress;

    public function __construct(string $baseUrl, string $mailboxAddress)
    {
        $this->baseUrl        = rtrim($baseUrl, '/');
        $this->mailboxAddress = $mailboxAddress;
    }

    public function send(SendMessageDto $message): SendResultDto
    {
        try {
            // Create draft first
            $draftResponse = Http::withHeaders([
                'MailboxAddress' => $this->mailboxAddress,
            ])->post("{$this->baseUrl}/api/conversations");

            if (!$draftResponse->successful()) {
                Log::error('Failed to create draft', [
                    'response' => $draftResponse->json(),
                    'status'   => $draftResponse->status(),
                ]);

                return new SendResultDto(false, null, 'Failed to create draft');
            }

            $draftId = $draftResponse->json('id');

            // Add attachments if any
            foreach ($message->attachments as $attachment) {
                $attachmentResponse = Http::withHeaders([
                    'MailboxAddress' => $this->mailboxAddress,
                ])->attach('file', file_get_contents($attachment['path']), $attachment['name'])
                    ->post("{$this->baseUrl}/api/conversations/{$draftId}/drafts/{$draftId}/attachment")
                ;

                if (!$attachmentResponse->successful()) {
                    Log::error('Failed to add attachment', [
                        'response' => $attachmentResponse->json(),
                        'status'   => $attachmentResponse->status(),
                    ]);

                    return new SendResultDto(false, null, 'Failed to add attachment');
                }
            }

            // Send the message
            $sendResponse = Http::withHeaders([
                'MailboxAddress' => $this->mailboxAddress,
            ])->post("{$this->baseUrl}/api/send-messages", [
                'draftId'         => $draftId,
                'topic'           => $message->subject,
                'content'         => $message->content,
                'finalRecipients' => $message->recipients,
                'refToMessageId'  => $message->refToMessageId,
            ]);

            if (!$sendResponse->successful()) {
                Log::error('Failed to send message', [
                    'response' => $sendResponse->json(),
                    'status'   => $sendResponse->status(),
                ]);

                return new SendResultDto(false, null, 'Failed to send message');
            }

            return new SendResultDto(true, $draftId);
        } catch (\Exception $e) {
            Log::error('Exception while sending message', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return new SendResultDto(false, null, $e->getMessage());
        }
    }

    public function verifyCertificate(CertificateInfoDto $certificate): bool
    {
        // TODO: Implement certificate verification logic
        return true;
    }

    public function getProviderName(): string
    {
        return 'edo_post';
    }

    public function syncMessages(): SyncResultDto
    {
        try {
            // Trigger sync
            $syncResponse = Http::withHeaders([
                'MailboxAddress' => $this->mailboxAddress,
            ])->post("{$this->baseUrl}/api/messages-synchronization");

            if (!$syncResponse->successful()) {
                Log::error('Failed to trigger sync', [
                    'response' => $syncResponse->json(),
                    'status'   => $syncResponse->status(),
                ]);

                return new SyncResultDto(false, 0, 0, 'Failed to trigger sync');
            }

            // TODO: Implement proper sync status checking and message counting
            return new SyncResultDto(true, 0, 0);
        } catch (\Exception $e) {
            Log::error('Exception while syncing messages', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return new SyncResultDto(false, 0, 0, $e->getMessage());
        }
    }
}
