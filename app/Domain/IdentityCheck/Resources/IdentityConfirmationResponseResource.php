<?php

namespace App\Domain\IdentityCheck\Resources;

use App\Domain\IdentityCheck\DTOs\IdentityConfirmationResponseDTO;
use App\Domain\IdentityCheck\DTOs\IdentityConfirmationResponseStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property IdentityConfirmationResponseStatus $status
 * @property bool                               $confirmed
 * @property ?array                             $errors
 * @property ?array                             $signatureInfo
 *
 * @mixin IdentityConfirmationResponseDTO
 */
class IdentityConfirmationResponseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'status'        => $this->status->value,
            'confirmed'     => $this->confirmed,
            'errors'        => $this->errors ? ['file' => $this->errors] : null,
            'signatureInfo' => $this->signatureInfo,
        ];
    }
}
