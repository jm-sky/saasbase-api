<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Tenant\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Tenant\Enums\InvitationStatus;
use App\Domain\Tenant\Models\Invitation;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class InvitationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testCanSendInvitation(): void
    {
        /** @var User $user */
        $user   = User::factory()->create();
        $tenant = Tenant::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson(
            "/api/v1/tenants/{$tenant->id}/invite",
            [
                'email' => 'invitee@example.com',
                'role'  => 'member',
            ]
        );

        $response->assertCreated();
        $this->assertDatabaseHas('invitations', [
            'tenant_id' => $tenant->id,
            'email'     => 'invitee@example.com',
            'role'      => 'member',
            'status'    => InvitationStatus::PENDING->value,
        ]);
    }

    public function testCanAcceptInvitation(): void
    {
        $user       = User::factory()->create();
        $tenant     = Tenant::factory()->create();
        $token      = Str::uuid()->toString();
        $invitation = Invitation::create([
            'tenant_id'   => $tenant->id,
            'inviter_id'  => $user->id,
            'email'       => 'invitee@example.com',
            'role'        => 'member',
            'token'       => $token,
            'status'      => InvitationStatus::PENDING->value,
            'expires_at'  => now()->addDays(7),
        ]);

        /** @var User $invitee */
        $invitee = User::factory()->create(['email' => 'invitee@example.com']);
        $this->actingAs($invitee);

        $response = $this->getJson("/api/v1/invitations/{$token}");
        $response->assertOk();
        $this->assertDatabaseHas('invitations', [
            'id'     => $invitation->id,
            'status' => InvitationStatus::ACCEPTED->value,
        ]);
        $this->assertDatabaseHas('user_tenants', [
            'user_id'   => $invitee->id,
            'tenant_id' => $tenant->id,
            'role'      => 'member',
        ]);
    }
}
