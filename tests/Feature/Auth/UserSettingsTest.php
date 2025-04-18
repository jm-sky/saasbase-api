<?php

namespace Tests\Feature\Auth;

use App\Domain\Auth\Models\User;
use App\Domain\Auth\Models\UserSettings;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithAuthenticatedUser;

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
        $this->user = $this->authenticateUser($this->tenant);
    }

    public function test_user_can_get_settings(): void
    {
        // Create settings
        UserSettings::create([
            'user_id' => $this->user->id,
            'language' => 'en',
            'theme' => 'light',
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
            ]);
    }

    public function test_user_can_update_settings(): void
    {
        $response = $this->putJson('/api/v1/user/settings', [
            'language' => 'pl',
            'theme' => 'dark',
            'timezone' => 'Europe/Warsaw',
            'preferences' => ['notifications' => 'email'],
        ]);

        $response->assertOk()
            ->assertJsonFragment([
                'language' => 'pl',
                'theme' => 'dark',
                'timezone' => 'Europe/Warsaw',
                'preferences' => ['notifications' => 'email'],
            ]);
    }

    public function test_user_can_update_language(): void
    {
        $response = $this->patchJson('/api/v1/user/settings/language', [
            'language' => 'de',
        ]);

        $response->assertOk()
            ->assertJsonFragment([
                'language' => 'de',
            ]);
    }

    public function test_user_cannot_update_settings_with_invalid_data(): void
    {
        $response = $this->putJson('/api/v1/user/settings', [
            'theme' => 'invalid-theme',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['theme']);
    }

    public function test_user_cannot_update_language_with_invalid_data(): void
    {
        $response = $this->patchJson('/api/v1/user/settings/language', [
            'language' => '',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['language']);
    }
}
