<?php

namespace Tests\Unit\Domain\Auth;

use App\Domain\Auth\DTOs\UserDto;
use App\Domain\Auth\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class UserDtoTest extends TestCase
{
    public function test_can_create_user_dto_from_model(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'avatar_url' => 'https://example.com/avatar.jpg',
        ]);

        $dto = UserDto::fromModel($user);

        $this->assertEquals($user->id, $dto->id);
        $this->assertEquals($user->name, $dto->name);
        $this->assertEquals($user->email, $dto->email);
        $this->assertEquals($user->avatar_url, $dto->avatarUrl);
        $this->assertEquals($user->created_at, $dto->createdAt);
        $this->assertEquals($user->updated_at, $dto->updatedAt);
        $this->assertEquals($user->deleted_at, $dto->deletedAt);
    }

    public function test_can_convert_user_dto_to_array(): void
    {
        $now = Carbon::now();
        $dto = new UserDto(
            id: '123e4567-e89b-12d3-a456-426614174000',
            name: 'Test User',
            email: 'test@example.com',
            avatarUrl: 'https://example.com/avatar.jpg',
            createdAt: $now,
            updatedAt: $now,
            deletedAt: null,
        );

        $array = $dto->toArray();

        $this->assertEquals('123e4567-e89b-12d3-a456-426614174000', $array['id']);
        $this->assertEquals('Test User', $array['name']);
        $this->assertEquals('test@example.com', $array['email']);
        $this->assertEquals('https://example.com/avatar.jpg', $array['avatarUrl']);
        $this->assertEquals($now, $array['createdAt']);
        $this->assertEquals($now, $array['updatedAt']);
        $this->assertNull($array['deletedAt']);
    }
}
