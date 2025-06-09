<?php

namespace App\Domain\ShareToken\Services;

use App\Domain\ShareToken\Models\ShareToken;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ShareTokenService
{
    /**
     * Generate a new share token for a model.
     */
    public function createToken(
        string $shareableType,
        string $shareableId,
        bool $onlyForAuthenticated = false,
        ?Carbon $expiresAt = null,
        ?int $maxUsage = null
    ): ShareToken {
        return ShareToken::create([
            'token'                  => Str::random(40),
            'shareable_type'         => $shareableType,
            'shareable_id'           => $shareableId,
            'only_for_authenticated' => $onlyForAuthenticated,
            'expires_at'             => $expiresAt,
            'max_usage'              => $maxUsage,
        ]);
    }

    /**
     * Validate a share token (expiration, usage, etc.).
     */
    public function validateToken(ShareToken $token): bool
    {
        if ($token->expires_at && $token->expires_at->isPast()) {
            return false;
        }

        if (null !== $token->max_usage && $token->usage_count >= $token->max_usage) {
            return false;
        }

        return true;
    }

    /**
     * Increment usage and update last_used_at.
     */
    public function incrementUsage(ShareToken $token): void
    {
        $token->last_used_at = now();
        ++$token->usage_count;
        $token->save();
    }
}
