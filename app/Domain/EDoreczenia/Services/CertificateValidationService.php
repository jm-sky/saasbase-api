<?php

namespace App\Domain\EDoreczenia\Services;

use App\Domain\EDoreczenia\DTOs\CertificateInfoDto;
use App\Domain\EDoreczenia\Models\EDoreczeniaCertificate;
use App\Domain\EDoreczenia\Providers\EDoreczeniaProviderManager;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class CertificateValidationService
{
    public function __construct(
        private readonly EDoreczeniaProviderManager $providerManager
    ) {
    }

    /**
     * Validate a certificate file and return its info.
     */
    public function validateCertificate(string $filePath, string $password): ?CertificateInfoDto
    {
        try {
            $certificate = openssl_x509_read(file_get_contents($filePath));

            if (!$certificate) {
                return null;
            }

            $certInfo = openssl_x509_parse($certificate);

            if (!$certInfo) {
                return null;
            }

            return new CertificateInfoDto(
                filePath: $filePath,
                password: $password,
                fingerprint: openssl_x509_fingerprint($certificate, 'sha1'),
                subjectCn: $certInfo['subject']['CN'] ?? '',
                validFrom: Carbon::createFromTimestamp($certInfo['validFrom_time_t']),
                validTo: Carbon::createFromTimestamp($certInfo['validTo_time_t'])
            );
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Check if a certificate is expired.
     */
    public function isExpired(EDoreczeniaCertificate $certificate): bool
    {
        return $certificate->valid_to->isPast();
    }

    /**
     * Check if a certificate is expiring soon (within given days).
     */
    public function isExpiringSoon(EDoreczeniaCertificate $certificate, int $days = 30): bool
    {
        return $certificate->valid_to->subDays($days)->isPast() && !$this->isExpired($certificate);
    }

    /**
     * Get all certificates that are expiring soon.
     */
    public function getExpiringCertificates(int $days = 30): Collection
    {
        return EDoreczeniaCertificate::query()
            ->where('is_valid', true)
            ->where('valid_to', '<=', now()->addDays($days))
            ->where('valid_to', '>', now())
            ->get()
        ;
    }

    /**
     * Verify certificate with provider.
     */
    public function verifyWithProvider(EDoreczeniaCertificate $certificate): bool
    {
        $provider = $this->providerManager->getProvider($certificate->provider);

        if (!$provider) {
            return false;
        }

        $certificateInfo = new CertificateInfoDto(
            filePath: Storage::path($certificate->file_path),
            password: '', // Password is not stored in the database for security
            fingerprint: $certificate->fingerprint,
            subjectCn: $certificate->subject_cn,
            validFrom: $certificate->valid_from,
            validTo: $certificate->valid_to
        );

        return $provider->verifyCertificate($certificateInfo);
    }

    /**
     * Validate and update certificate status.
     */
    public function validateAndUpdateStatus(EDoreczeniaCertificate $certificate): bool
    {
        // Check if certificate file exists
        if (!Storage::exists($certificate->file_path)) {
            $certificate->update(['is_valid' => false]);

            return false;
        }

        // Check if expired
        if ($this->isExpired($certificate)) {
            $certificate->update(['is_valid' => false]);

            return false;
        }

        // Verify with provider
        $isValid = $this->verifyWithProvider($certificate);
        $certificate->update(['is_valid' => $isValid]);

        return $isValid;
    }

    /**
     * Get all invalid certificates.
     */
    public function getInvalidCertificates(): Collection
    {
        return EDoreczeniaCertificate::query()
            ->where('is_valid', false)
            ->get()
        ;
    }

    /**
     * Get all valid certificates.
     */
    public function getValidCertificates(): Collection
    {
        return EDoreczeniaCertificate::query()
            ->where('is_valid', true)
            ->get()
        ;
    }
}
