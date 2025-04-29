<?php

namespace App\Domain\Auth\Controllers;

use App\Domain\Auth\DTOs\UserDTO;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Transform the user model into a UserDTO
        $userDTO = UserDTO::fromModel($user);

        return response()->json([
            'user' => $userDTO,
        ]);
    }
}
