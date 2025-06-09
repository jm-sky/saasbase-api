<?php

namespace App\Domain\Auth\Controllers;

use App\Domain\Auth\Actions\RegisterUserAction;
use App\Domain\Auth\DTOs\RegisterUserDTO;
use App\Domain\Auth\JwtHelper;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Requests\LoginRequest;
use App\Domain\Auth\Requests\RegisterRequest;
use App\Domain\Auth\Services\UserSessionService;
use App\Domain\Auth\Traits\RespondsWithToken;
use App\Http\Controllers\Controller;
use App\Services\ReCaptcha\Enums\ReCaptchaAction;
use App\Services\ReCaptcha\ReCaptchaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    use RespondsWithToken;

    public function __construct(
        private readonly RegisterUserAction $registerUserAction,
        private readonly ReCaptchaService $recaptchaService,
    ) {
    }

    public function login(LoginRequest $request, UserSessionService $userSessionService): JsonResponse
    {
        $credentials = $request->only(['email', 'password']);

        if (!$this->recaptchaService->verify($request->input('recaptchaToken'), ReCaptchaAction::LOGIN->value, 0.5)) {
            throw new BadRequestHttpException('Invalid recaptcha token');
        }

        if (!Auth::attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], Response::HTTP_UNAUTHORIZED);
        }

        /** @var User $user */
        $user  = Auth::user();

        if ($this->shouldChooseFirstTenant($user)) {
            $tenant = $user->tenants()->first();
            $token  = JwtHelper::createTokenWithTenant($user, $tenant->id);
        } else {
            $token = JwtHelper::createTokenWithoutTenant($user);
        }

        $userSessionService->createSession($user, $request, $token);

        return $this->respondWithToken($token, $user, remember: $request->boolean('remember'));
    }

    public function logout(UserSessionService $userSessionService): JsonResponse
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        $userSessionService->revokeCurrentSession();

        return response()->json(['message' => 'Logged out'])->withCookie(
            cookie()->forget('refresh_token')
        );
    }

    public function register(RegisterRequest $request, UserSessionService $userSessionService): JsonResponse
    {
        $validated = $request->validated();

        if (!$this->recaptchaService->verify($validated['recaptchaToken'], ReCaptchaAction::REGISTER->value, 0.6)) {
            throw new BadRequestHttpException('Invalid recaptcha token');
        }

        $dto = new RegisterUserDTO(
            firstName: $validated['first_name'],
            lastName: $validated['last_name'],
            email: $validated['email'],
            password: $validated['password'],
            description: $validated['description'] ?? null,
            birthDate: isset($validated['birth_date']) ? new \DateTime($validated['birthDate']) : null,
            phone: $validated['phone'] ?? null
        );

        /** @var User $user */
        $user = $this->registerUserAction->execute($dto);

        $token = JwtHelper::createTokenWithoutTenant($user);

        $userSessionService->createSession($user, $request, $token);

        return $this->respondWithToken($token, $user, remember: $request->boolean('remember'));
    }

    public function refresh(): JsonResponse
    {
        try {
            $refreshToken = request()->cookie('refresh_token');

            if (!$refreshToken) {
                throw new JWTException('Token not provided');
            }

            // Set the refresh token for JWTAuth to use
            JWTAuth::setToken($refreshToken);

            // Authenticate the user using the refresh token
            $user = JWTAuth::authenticate();

            if (!$user) {
                throw new TokenInvalidException('User not found');
            }

            // Check if current token has tenant context
            $payload  = JWTAuth::payload();
            $tenantId = $payload->get('tid');

            // Generate new token with or without tenant context
            $newToken = $tenantId
                ? JwtHelper::createTokenWithTenant($user, $tenantId)
                : JwtHelper::createTokenWithoutTenant($user);

            // TODO: Implement $remember
            return $this->respondWithToken($newToken, $user, tenantId: $tenantId);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Invalid token'], Response::HTTP_UNAUTHORIZED);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Token not provided or expired'], Response::HTTP_UNAUTHORIZED);
        }
    }

    protected function shouldChooseFirstTenant(User $user): bool
    {
        return 1 === $user->tenants()->count();
    }
}
