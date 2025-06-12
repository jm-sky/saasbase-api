<?php

namespace Tests\Feature\Auth;

use App\Domain\Auth\Controllers\UserSettingsController;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Models\UserSettings;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;
use Tests\Traits\WithAuthenticatedUser;

/**
 * @internal
 */
#[CoversClass(UserSettingsController::class)]
class UserSettingsTest extends TestCase
{
    use RefreshDatabase;
    use WithAuthenticatedUser;

    private User $user;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        $this->user   = $this->authenticateUser($this->tenant);
    }

    public function testUserCanGetSettings(): void
    {
        // Create settings
        UserSettings::create([
            'user_id'  => $this->user->id,
            'language' => 'en',
            'theme'    => 'light',
            'timezone' => 'UTC',
        ]);

        $response = $this->getJson('/api/v1/user/settings');

        $response->assertOk()
            ->assertJsonStructure([
                'id',
                'userId',
                'language',
                'theme',
                'timezone',
                'twoFactorEnabled',
                'twoFactorConfirmed',
                'preferences',
            ])
        ;
    }

    public function testUserCanUpdateSettings(): void
    {
        $response = $this->putJson('/api/v1/user/settings', [
            'language'    => 'pl',
            'theme'       => 'dark',
            'timezone'    => 'Europe/Warsaw',
            'preferences' => ['notifications' => 'email'],
        ]);

        $response->assertOk()
            ->assertJsonFragment([
                'language'    => 'pl',
                'theme'       => 'dark',
                'timezone'    => 'Europe/Warsaw',
                'preferences' => ['notifications' => 'email'],
            ])
        ;
    }

    public function testUserCanUpdateLanguage(): void
    {
        $response = $this->patchJson('/api/v1/user/settings/language', [
            'language' => 'de',
        ]);

        $response->assertOk()
            ->assertJsonFragment([
                'language' => 'de',
            ])
        ;
    }

    public function testUserCannotUpdateSettingsWithInvalidData(): void
    {
        $response = $this->putJson('/api/v1/user/settings', [
            'theme' => 'invalid-theme',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['theme'])
        ;
    }

    public function testUserCannotUpdateLanguageWithInvalidData(): void
    {
        $response = $this->patchJson('/api/v1/user/settings/language', [
            'language' => '',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['language'])
        ;
    }
}
