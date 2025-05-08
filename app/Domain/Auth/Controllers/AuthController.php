<?php

namespace App\Domain\Auth\Controllers;

use App\Domain\Auth\Actions\RegisterUserAction;
use App\Domain\Auth\DTOs\RegisterUserDTO;
use App\Domain\Auth\JwtHelper;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Requests\RegisterRequest;
use App\Domain\Auth\Traits\RespondsWithToken;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    use RespondsWithToken;

    public function __construct(
        private readonly RegisterUserAction $registerUserAction
    ) {
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->only(['email', 'password']);

        if (!Auth::attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        /** @var User $user */
        $user  = Auth::user();
        $token = JwtHelper::createTokenWithoutTenant($user);

        return $this->respondWithToken($token, $user, remember: $request->boolean('remember'));
    }

    public function logout(): JsonResponse
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json(['message' => 'Logged out'])->withCookie(
            cookie()->forget('refresh_token')
        );
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $dto = new RegisterUserDTO(
            firstName: $validated['firstName'],
            lastName: $validated['lastName'],
            email: $validated['email'],
            password: $validated['password'],
            description: $validated['description'] ?? null,
            birthDate: isset($validated['birthDate']) ? new \DateTime($validated['birthDate']) : null,
            phone: $validated['phone'] ?? null
        );

        /** @var User $user */
        $user = $this->registerUserAction->execute($dto);

        $token = JwtHelper::createTokenWithoutTenant($user);

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
}
