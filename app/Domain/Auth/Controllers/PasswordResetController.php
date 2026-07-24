<?php

namespace App\Domain\Auth\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Auth\Notifications\PasswordChangedNotification;
use App\Domain\Auth\Requests\ResetPasswordRequest;
use App\Domain\Auth\Requests\SendResetLinkEmailRequest;
use App\Services\ReCaptcha\Enums\ReCaptchaAction;
use App\Services\ReCaptcha\ReCaptchaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PasswordResetController extends Controller
{
    public function __construct(
        private readonly ReCaptchaService $recaptchaService,
    ) {}

    /**
     * Send a reset link to the given user.
     */
    public function sendResetLinkEmail(SendResetLinkEmailRequest $request): JsonResponse
    {
        if (! $this->recaptchaService->verify($request->input('recaptchaToken'), ReCaptchaAction::FORGOT_PASSWORD->value, 0.6)) {
            throw new BadRequestHttpException('Invalid recaptcha token');
        }

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status !== Password::RESET_LINK_SENT) {
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
    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        if (! $this->recaptchaService->verify($request->input('recaptchaToken'), ReCaptchaAction::RESET_PASSWORD->value, 0.6)) {
            throw new BadRequestHttpException('Invalid recaptcha token');
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json([
                'message' => __($status),
            ], Response::HTTP_BAD_REQUEST);
        }

        $user = User::where('email', $request->email)->first();
        $user?->notify(new PasswordChangedNotification($user));

        if ($user) {
            activity('security')->causedBy($user)->withProperties(['ip' => $request->ip()])->event('password_change')->log('Password changed');
        }

        return response()->json([
            'message' => __($status),
        ]);
    }
}
