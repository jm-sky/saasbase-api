<?php

namespace App\Domain\Auth\Controllers;

use App\Domain\Auth\JwtHelper;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Services\TwoFactorAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorAuthController extends Controller
{
    public function __construct(
        private readonly TwoFactorAuthService $twoFactorAuthService
    ) {
    }

    public function setup(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $setup = $this->twoFactorAuthService->generateTwoFactorSetup($user);

        return response()->json([
            'secret'         => $setup['secret'],
            'qr_code_url'    => $setup['qr_code_url'],
            'recovery_codes' => $setup['recovery_codes'],
        ]);
    }

    public function enable(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        if (!$this->twoFactorAuthService->enableTwoFactor($user, $request->input('code'))) {
            return response()->json([
                'message' => 'Invalid authentication code.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json([
            'message' => 'Two factor authentication has been enabled.',
        ]);
    }

    public function disable(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $this->twoFactorAuthService->disableTwoFactor($user);

        return response()->json([
            'message' => 'Two factor authentication has been disabled.',
        ]);
    }

    public function verify(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (!$user->settings?->two_factor_enabled) {
            return response()->json([
                'message' => 'Two factor authentication is not enabled.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $code   = $request->input('code');
        $secret = decrypt($user->settings->two_factor_secret);

        $isValidCode         = $this->twoFactorAuthService->verifyCode($secret, $code);
        $isValidRecoveryCode = $this->twoFactorAuthService->verifyRecoveryCode($user, $code);

        if (!$isValidCode && !$isValidRecoveryCode) {
            return response()->json([
                'message' => 'Invalid authentication code.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Generate new token with 2FA passed
        $token = JwtHelper::createTokenWithoutTenant($user, true);

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
        ]);
    }
}
