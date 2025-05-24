<?php

namespace App\Domain\Auth\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Auth\Requests\UpdateUserProfileRequest;
use App\Domain\Users\Models\UserProfile;
use App\Domain\Users\Resources\UserProfileResource;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class UserProfileController extends Controller
{
    public function show(): UserProfileResource
    {
        $user = Auth::user();

        $user->profile ??= new UserProfile();

        return new UserProfileResource($user->profile);
    }

    public function update(UpdateUserProfileRequest $request): UserProfileResource
    {
        /** @var User $user */
        $user = Auth::user();

        $user->profile ??= UserProfile::create([
            'user_id' => $user->id,
        ]);

        $user->profile->update($request->validated());

        return new UserProfileResource($user->profile);
    }
}
