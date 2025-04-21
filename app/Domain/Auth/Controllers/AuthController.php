<?php

namespace App\Domain\Auth\Controllers;

use App\Domain\Auth\Actions\RegisterUserAction;
use App\Domain\Auth\DTOs\RegisterUserDTO;
use App\Domain\Auth\Requests\RegisterRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class AuthController extends Controller
{
    public function __construct(
        private readonly RegisterUserAction $registerUserAction
    ) {
    }

    public function login(Request $request)
    {
        $credentials = $request->only(['email', 'password']);

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function register(RegisterRequest $request)
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

        $user = $this->registerUserAction->execute($dto);

        $token = auth()->login($user);

        return $this->respondWithToken($token);
    }

    public function refresh()
    {
        try {
            $newToken = auth()->refresh();

            return $this->respondWithToken($newToken);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Invalid token'], 401);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Token not provided or expired'], 401);
        }
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token'  => $token,
            'refresh_token' => $token, // Optional: replace with separate logic if you want long-lived refresh tokens
            'token_type'    => 'bearer',
            'expires_in'    => auth()->factory()->getTTL() * 60,
            'user'          => auth()->user(),
        ]);
    }
}