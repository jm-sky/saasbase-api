<?php

namespace Tests\Unit\Domain\Auth;

use App\Domain\Auth\DTOs\UserDTO;
use App\Domain\Auth\Models\User;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\CoversNothing;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class UserDTOTest extends TestCase
{
    public function testCanCreateUserDtoFromModel(): void
    {
        $user = User::factory()->create([
            'first_name'  => 'John',
            'last_name'   => 'Doe',
            'email'       => 'test@example.com',
            'avatar_url'  => 'https://example.com/avatar.jpg',
            'description' => 'Test description',
            'birth_date'  => '1990-01-01',
            'phone'       => '+1234567890',
            'is_admin'    => true,
        ]);

        $dto = UserDTO::fromModel($user);

        $this->assertEquals($user->id, $dto->id);
        $this->assertEquals($user->first_name, $dto->firstName);
        $this->assertEquals($user->last_name, $dto->lastName);
        $this->assertEquals($user->email, $dto->email);
        $this->assertEquals($user->avatar_url, $dto->avatarUrl);
        $this->assertEquals($user->description, $dto->description);
        $this->assertEquals($user->birth_date, $dto->birthDate);
        $this->assertEquals($user->phone, $dto->phone);
        $this->assertEquals($user->is_admin, $dto->isAdmin);
        $this->assertEquals($user->created_at?->toIso8601String(), $dto->createdAt->toIso8601String());
        $this->assertEquals($user->updated_at?->toIso8601String(), $dto->updatedAt?->toIso8601String());
        $this->assertEquals($user->deleted_at?->toIso8601String(), $dto->deletedAt?->toIso8601String());
    }

    public function testCanConvertUserDtoToArray(): void
    {
        $now    = Carbon::now();
        $isoNow = $now->toIso8601String();

        $dto = new UserDTO(
            firstName: 'John',
            lastName: 'Doe',
            email: 'test@example.com',
            id: '123e4567-e89b-12d3-a456-426614174000',
            avatarUrl: 'https://example.com/avatar.jpg',
            description: 'Test description',
            birthDate: '1990-01-01',
            phone: '+1234567890',
            isAdmin: true,
            createdAt: $now,
            updatedAt: $now,
            deletedAt: null,
        );

        $array = $dto->toArray();

        $this->assertEquals('123e4567-e89b-12d3-a456-426614174000', $array['id']);
        $this->assertEquals('John', $array['firstName']);
        $this->assertEquals('Doe', $array['lastName']);
        $this->assertEquals('test@example.com', $array['email']);
        $this->assertEquals('https://example.com/avatar.jpg', $array['avatarUrl']);
        $this->assertEquals('Test description', $array['description']);
        $this->assertEquals('1990-01-01', $array['birthDate']);
        $this->assertEquals('+1234567890', $array['phone']);
        $this->assertTrue($array['isAdmin']);
        $this->assertEquals($isoNow, $array['createdAt']);
        $this->assertEquals($isoNow, $array['updatedAt']);
        $this->assertNull($array['deletedAt']);
    }
}
