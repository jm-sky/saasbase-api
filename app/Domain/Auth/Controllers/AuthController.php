<?php

namespace App\Domain\Auth\Controllers;

use App\Domain\Auth\Actions\RegisterUserAction;
use App\Domain\Auth\DTOs\RegisterUserDTO;
use App\Domain\Auth\DTOs\UserDTO;
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

    // New getUser method to return authenticated user's data
    public function getUser()
    {
        $user = auth()->user(); // Get the authenticated user

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Transform the user model into a UserDTO
        $userDTO = UserDTO::fromModel($user);

        return response()->json([
            'user' => $userDTO,
        ]);
    }

    protected function respondWithToken($token)
{
    return response()
        ->json([
            'accessToken' => $token,
            'tokenType'   => 'bearer',
            'expiresIn'   => auth()->factory()->getTTL() * 60,
            'user'        => auth()->user(),
        ])
        ->withCookie(cookie(
            'refresh_token',
            $token,
            60 * 24 * 7, // 7 days
            '/',         // path
            null,        // domain (set if needed)
            true,        // secure
            true,        // httpOnly
            false,       // raw
            'Strict'     // SameSite
        ));
    }
}
