<?php

namespace App\Services\SignatureBasedIdentityCheck;

use App\Domain\Auth\Models\User;
use App\Domain\IdentityCheck\Enums\IdentityCheckMethod;
use App\Domain\IdentityCheck\Enums\IdentityCheckPurpose;
use App\Domain\IdentityCheck\Enums\IdentityCheckStatus;
use App\Domain\IdentityCheck\Models\IdentityCheck;
use App\Services\Signatures\DTOs\GenericSignatureDetailsDTO;
use App\Services\Signatures\DTOs\GenericSignaturesVerificationResultDTO;
use App\Services\Signatures\SignatureFileDetectorService;
use App\Services\Signatures\SignatureVerifierDispatcher;

class SignatureBasedIdentityCheckService
{
    public function __construct(
        protected SignatureFileDetectorService $signatureFileDetectorService,
        protected SignatureVerifierDispatcher $signatureVerifierDispatcher,
    ) {
    }

    public function check(string $content): GenericSignaturesVerificationResultDTO
    {
        $detected = $this->signatureFileDetectorService->detect($content);

        /* @var GenericSignaturesVerificationResultDTO $verifyResult */
        return $this->signatureVerifierDispatcher->verify($content, $detected->signature);
    }

    public function createIdentityCheck(string $content, IdentityCheckPurpose $purpose, User $user): IdentityCheck
    {
        $detected = $this->check($content);

        $signature = $this->confirmedSignature($user, $detected);

        return IdentityCheck::create([
            'verifiable_type' => User::class,
            'verifiable_id'   => $user->id,
            'purpose'         => $purpose,
            'method'          => IdentityCheckMethod::Epuap,
            'status'          => $signature ? IdentityCheckStatus::Verified : IdentityCheckStatus::Rejected,
            'data'            => [
                'signature' => $signature,
            ],
        ]);
    }

    protected function confirmedSignature(User $user, GenericSignaturesVerificationResultDTO $verifyResult): ?GenericSignatureDetailsDTO
    {
        return collect($verifyResult->signatures)
            ->first(
                fn (GenericSignatureDetailsDTO $signature) => $signature->valid
                // First name and last name must match
                && $signature->signerIdentity->firstName === $user->first_name
                && $signature->signerIdentity->lastName === $user->last_name
                // PESEL is optional, but if it's present, it must match
                && ($user->personalData?->pesel ? $signature->signerIdentity?->pesel === $user->personalData?->pesel : true)
            )
        ;
    }
}
