<?php

namespace App\Domain\Auth\Controllers;

use App\Domain\Auth\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class PasswordResetController extends Controller
{
    /**
     * Send a reset link to the given user.
     */
    public function sendResetLinkEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if (Password::RESET_LINK_SENT !== $status) {
            return response()->json([
                'message' => __($status),
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'message' => __($status),
        ]);
    }

    /**
     * Reset the given user's password.
     */
    public function reset(Request $request): JsonResponse
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password'       => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if (Password::PASSWORD_RESET !== $status) {
            return response()->json([
                'message' => __($status),
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'message' => __($status),
        ]);
    }
}
