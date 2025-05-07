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
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }

    /**
     * Mark the authenticated user's email address as verified.
     */
    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
        ]);

        /** @var User|null $user */
        $user = User::where('email', $request->email)->firstOrFail();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email already verified.',
            ], Response::HTTP_OK);
        }

        $token = $user->emailVerificationToken;

        if (!$token || $token->token !== $request->token) {
            return response()->json([
                'message' => 'Invalid verification token.',
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
            $token->delete(); // Remove the used token
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

        // Delete any existing token
        $user->emailVerificationToken()->delete();

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Verification link sent.',
        ], Response::HTTP_OK);
    }
}
