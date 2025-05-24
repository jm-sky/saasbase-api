<?php

namespace App\Domain\Auth\Controllers;

use App\Domain\Auth\DTOs\UserDTO;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Requests\UpdateUserAccountRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UserAccountController extends Controller
{
    public function update(UpdateUserAccountRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        // TODO: Add password update
        // TODO: Email/Phone confirmation
        $user->update($request->validated());

        return response()->json([
            'user' => UserDTO::fromModel($user),
        ]);
    }
}
