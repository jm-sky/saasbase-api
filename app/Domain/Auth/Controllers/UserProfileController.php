<?php

namespace App\Domain\Auth\Controllers;

use App\Domain\Auth\DTOs\UserDTO;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Requests\UpdateUserProfileRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UserProfileController extends Controller
{
    public function update(UpdateUserProfileRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $user->update($request->validated());

        return response()->json([
            'user' => UserDTO::fromModel($user),
        ]);
    }
}
