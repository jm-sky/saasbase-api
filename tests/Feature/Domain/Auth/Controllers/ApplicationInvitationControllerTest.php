<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Auth\Controllers;

use App\Domain\Auth\Controllers\ApplicationInvitationController;
use App\Domain\Auth\Models\ApplicationInvitation;
use App\Domain\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;
use Tests\Traits\WithAuthenticatedUser;

/**
 * @internal
 */
#[CoversClass(ApplicationInvitationController::class)]
class ApplicationInvitationControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithAuthenticatedUser;

    public function testCanSendInvitation(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->authenticateUser(user: $user);

        $response = $this->postJson(
            '/api/v1/application/invitations',
            [
                'email' => 'invitee@example.com',
            ]
        );

        $response->assertCreated();
        $this->assertDatabaseHas('application_invitations', [
            'email'  => 'invitee@example.com',
            'status' => 'pending',
        ]);
    }

    public function testCanAcceptInvitation(): void
    {
        $user       = User::factory()->create();
        $token      = Str::ulid()->toString();
        $invitation = ApplicationInvitation::create([
            'inviter_id' => $user->id,
            'email'      => 'invitee@example.com',
            'token'      => $token,
            'status'     => 'pending',
            'expires_at' => now()->addDays(7),
        ]);

        /** @var User $invitee */
        $invitee = User::factory()->create(['email' => 'invitee@example.com']);
        $this->actingAs($invitee);

        $response = $this->postJson("/api/v1/application/invitations/{$token}/accept");
        $response->assertOk();
        $this->assertDatabaseHas('application_invitations', [
            'id'     => $invitation->id,
            'status' => 'accepted',
        ]);
    }

    public function testCanRejectInvitation(): void
    {
        $user       = User::factory()->create();
        $token      = Str::ulid()->toString();
        $invitation = ApplicationInvitation::create([
            'inviter_id' => $user->id,
            'email'      => 'invitee@example.com',
            'token'      => $token,
            'status'     => 'pending',
            'expires_at' => now()->addDays(7),
        ]);

        /** @var User $invitee */
        $invitee = User::factory()->create(['email' => 'invitee@example.com']);
        $this->actingAs($invitee);

        $response = $this->postJson("/api/v1/application/invitations/{$token}/reject");
        $response->assertOk();
        $this->assertDatabaseHas('application_invitations', [
            'id'     => $invitation->id,
            'status' => 'rejected',
        ]);
    }

    public function testCanCancelInvitation(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->authenticateUser(user: $user);

        $invitation = ApplicationInvitation::create([
            'inviter_id' => $user->id,
            'email'      => 'invitee@example.com',
            'token'      => Str::ulid()->toString(),
            'status'     => 'pending',
            'expires_at' => now()->addDays(7),
        ]);

        $response = $this->deleteJson("/api/v1/application/invitations/{$invitation->id}");
        $response->assertOk();
        $this->assertDatabaseHas('application_invitations', [
            'id'     => $invitation->id,
            'status' => 'canceled',
        ]);
    }

    public function testCanResendInvitation(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->authenticateUser(user: $user);

        $invitation = ApplicationInvitation::create([
            'inviter_id' => $user->id,
            'email'      => 'invitee@example.com',
            'token'      => Str::ulid()->toString(),
            'status'     => 'pending',
            'expires_at' => now()->addDays(7),
        ]);

        $response = $this->postJson("/api/v1/application/invitations/{$invitation->id}/resend");
        $response->assertOk();
        $this->assertDatabaseHas('application_invitations', [
            'id'     => $invitation->id,
            'status' => 'pending',
        ]);
    }
}
