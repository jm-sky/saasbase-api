<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Auth\Controllers;

use App\Domain\Auth\Controllers\UserSessionController;
use App\Domain\Auth\JwtHelper;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Models\UserSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * @internal
 */
#[CoversClass(UserSessionController::class)]
class UserSessionControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{0: string, 1: UserSession}
     */
    private function createSessionForUser(User $user): array
    {
        $token = JwtHelper::createTokenWithoutTenant($user);
        // @phpstan-ignore-next-line
        $tokenId = Arr::get(JWTAuth::getJWTProvider()->decode($token), 'jti');

        $session = UserSession::create([
            'user_id' => $user->id,
            'token_id' => $tokenId,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'device_name' => 'Test Device',
            'last_active_at' => now(),
            'expires_at' => now()->addDay(),
        ]);

        return [$token, $session];
    }

    public function test_index_returns_real_sessions_without_fake_fallback(): void
    {
        $user = User::factory()->create();
        [$token] = $this->createSessionForUser($user);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/sessions');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $this->assertNotSame('current', $response->json('data.0.id'));
    }

    public function test_user_can_revoke_own_session(): void
    {
        $user = User::factory()->create();
        [$token] = $this->createSessionForUser($user);
        [, $otherSession] = $this->createSessionForUser($user);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson('/api/v1/sessions/'.$otherSession->id);

        $response->assertOk();
        $this->assertNotNull($otherSession->fresh()->revoked_at);
    }

    public function test_revoked_session_token_is_rejected_on_next_request(): void
    {
        $user = User::factory()->create();
        [$token] = $this->createSessionForUser($user);
        [$victimToken, $victimSession] = $this->createSessionForUser($user);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson('/api/v1/sessions/'.$victimSession->id)
            ->assertOk();

        // tymon/jwt-auth's JWT singleton caches the parsed token for its own
        // lifetime, which persists across multiple ->json() calls within a
        // single test regardless of Auth::forgetGuards()/unsetToken() — only
        // a full application rebuild reliably clears it so the next call
        // re-resolves against $victimToken instead of reusing $token.
        $this->refreshApplication();

        $this->withHeader('Authorization', 'Bearer '.$victimToken)
            ->getJson('/api/v1/sessions')
            ->assertUnauthorized();
    }

    public function test_user_cannot_revoke_another_users_session(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        [$token] = $this->createSessionForUser($user);
        [, $otherSession] = $this->createSessionForUser($otherUser);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson('/api/v1/sessions/'.$otherSession->id);

        $response->assertNotFound();
        $this->assertNull($otherSession->fresh()->revoked_at);
    }

    public function test_revoke_others_keeps_current_session_active(): void
    {
        $user = User::factory()->create();
        [$currentToken, $currentSession] = $this->createSessionForUser($user);
        [, $otherSession] = $this->createSessionForUser($user);

        $response = $this->withHeader('Authorization', 'Bearer '.$currentToken)
            ->postJson('/api/v1/sessions/revoke-others');

        $response->assertOk();
        $this->assertNull($currentSession->fresh()->revoked_at);
        $this->assertNotNull($otherSession->fresh()->revoked_at);

        $this->withHeader('Authorization', 'Bearer '.$currentToken)
            ->getJson('/api/v1/sessions')
            ->assertOk();
    }
}
