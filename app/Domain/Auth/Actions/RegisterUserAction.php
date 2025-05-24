<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\DTOs\RegisterUserDTO;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Notifications\WelcomeNotification;
use App\Domain\Users\Models\UserPreference;
use App\Domain\Users\Models\UserProfile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;

class RegisterUserAction
{
    /**
     * Execute the action.
     */
    public function execute(RegisterUserDTO $dto): User
    {
        $requiresApproval = Config::get('users.registration.require_admin_approval', true);

        $user = User::create([
            'first_name'  => $dto->firstName,
            'last_name'   => $dto->lastName,
            'email'       => $dto->email,
            'password'    => Hash::make($dto->password),
            'phone'       => $dto->phone,
            'is_active'   => !$requiresApproval,
        ]);

        // Create user profile
        UserProfile::create([
            'user_id'    => $user->id,
            'birth_date' => $dto->birthDate,
        ]);

        // Create user preferences with defaults
        UserPreference::create([
            'user_id' => $user->id,
        ]);

        $user->sendEmailVerificationNotification();
        $user->notify(new WelcomeNotification($user));

        return $user;
    }
}
