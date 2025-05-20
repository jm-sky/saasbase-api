<?php

namespace App\Domain\Auth\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Auth\Resources\UserResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class MeController extends Controller
{
    public function __invoke(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $user->load('roles');

        return new UserResource($user);
    }
}
