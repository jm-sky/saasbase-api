<?php

namespace App\Domain\Auth\Controllers;

use App\Domain\Auth\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;

class VerifyEmailController extends Controller
{
    public function __construct()
    {
        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }

    /**
     * Mark the authenticated user's email address as verified.
     */
    public function verify(Request $request, string $id): JsonResponse
    {
        /** @var User|null $user */
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        if (!hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
            return response()->json([
                'message' => 'Invalid verification link.',
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email already verified.',
            ], Response::HTTP_OK);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return response()->json([
            'message' => 'Email verified successfully.',
        ], Response::HTTP_OK);
    }

    /**
     * Resend the email verification notification.
     */
    public function resend(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email already verified.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Verification link sent.',
        ], Response::HTTP_OK);
    }
}
