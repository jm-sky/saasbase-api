<?php

namespace App\Domain\ShareToken\Controllers;

use App\Domain\ShareToken\Models\ShareToken;
use App\Domain\ShareToken\Resources\ShareTokenResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ShareTokenController extends Controller
{
    /**
     * Display the shared resource by token.
     *
     * @return Response|\Illuminate\Http\JsonResponse
     */
    public function show(Request $request, string $token)
    {
        $shareToken = ShareToken::where('token', $token)->first();

        if (!$shareToken) {
            throw new NotFoundHttpException('Share token not found.');
        }

        // Check expiration
        if ($shareToken->expires_at && $shareToken->expires_at->isPast()) {
            throw new AccessDeniedHttpException('Share token expired.');
        }

        // Check usage limit
        if (null !== $shareToken->max_usage && $shareToken->usage_count >= $shareToken->max_usage) {
            throw new AccessDeniedHttpException('Share token usage limit reached.');
        }

        // Check authentication if required
        if ($shareToken->only_for_authenticated && !$request->user()) {
            throw new AccessDeniedHttpException('Authentication required.');
        }

        // Update usage
        $shareToken->last_used_at = now();
        ++$shareToken->usage_count;
        $shareToken->save();

        // Return the shareable resource (polymorphic)
        $shareable = $shareToken->shareable;

        if (!$shareable) {
            throw new NotFoundHttpException('Shared resource not found.');
        }

        // Optionally, you may want to return the shareable resource using its own Resource class
        // For now, return the ShareToken details
        return new ShareTokenResource($shareToken);
    }
}
