<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\DTOs\RegisterUserDTO;
use App\Domain\Auth\Enums\UserStatus;
use App\Domain\Auth\Models\User;
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

        $status = $requiresApproval
            ? UserStatus::PENDING
            : UserStatus::ACTIVE;


        $user = User::create([
            'first_name'  => $dto->firstName,
            'last_name'   => $dto->lastName,
            'email'       => $dto->email,
            'password'    => Hash::make($dto->password),
            'description' => $dto->description,
            'birth_date'  => $dto->birthDate,
            'phone'       => $dto->phone,
            'status'      => $status,
        ]);


        $user->sendEmailVerificationNotification();

        return $user;
    }
}
