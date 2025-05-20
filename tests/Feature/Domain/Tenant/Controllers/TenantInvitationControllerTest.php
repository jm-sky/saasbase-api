<?php

declare(strict_types=1);

namespace Tests\Feature\Domain\Tenant\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Tenant\Enums\InvitationStatus;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Models\TenantInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithAuthenticatedUser;

/**
 * @internal
 *
 * @coversNothing
 */
class TenantInvitationControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithAuthenticatedUser;

    public function testCanSendInvitation(): void
    {
        /** @var User $user */
        $user   = User::factory()->create();
        $tenant = Tenant::factory()->create();

        $this->authenticateUser($tenant, $user);

        $response = $this->postJson(
            "/api/v1/tenants/{$tenant->id}/invitations",
            [
                'email' => 'invitee@example.com',
                'role'  => 'member',
            ]
        );

        $response->assertCreated();
        $this->assertDatabaseHas('tenant_invitations', [
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
        $token      = 'e93cae90-1111-2222-3333-c1059f16a997';
        $invitation = TenantInvitation::create([
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

        $response = $this->postJson("/api/v1/tenants/{$tenant->id}/invitations/{$token}/accept");
        $response->assertOk();
        $this->assertDatabaseHas('tenant_invitations', [
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
