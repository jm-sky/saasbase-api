<?php

namespace App\Domain\EDoreczenia\Contracts;

use App\Domain\EDoreczenia\DTOs\CertificateInfoDto;
use App\Domain\EDoreczenia\DTOs\SendMessageDto;
use App\Domain\EDoreczenia\DTOs\SendResultDto;
use App\Domain\EDoreczenia\DTOs\SyncResultDto;

interface EDoreczeniaProviderInterface
{
    /**
     * Send a message through the provider.
     */
    public function send(SendMessageDto $message): SendResultDto;

    /**
     * Verify if the certificate is valid for this provider.
     */
    public function verifyCertificate(CertificateInfoDto $certificate): bool;

    /**
     * Get the provider's name.
     */
    public function getProviderName(): string;

    /**
     * Synchronize messages with the provider.
     * This will fetch new messages and update existing ones.
     */
    public function syncMessages(): SyncResultDto;
}
