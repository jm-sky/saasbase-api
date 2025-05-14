<?php

namespace App\Domain\Tenant\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Tenant\DTOs\InvitationDTO;
use App\Domain\Tenant\Enums\InvitationStatus;
use App\Domain\Tenant\Models\Invitation;
use App\Domain\Tenant\Models\UserTenant;
use App\Domain\Tenant\Notifications\InvitationNotification;
use App\Domain\Tenant\Requests\SendInvitationRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class InvitationController extends Controller
{
    /**
     * Send an invitation to join a tenant.
     */
    public function send(SendInvitationRequest $request): JsonResponse
    {
        /** @var User $user */
        $user     = $request->user();
        $tenantId = $user->getTenantId();
        // TODO: Add authorization check (policy)

        $token     = Str::uuid()->toString();
        $expiresAt = now()->addDays(7);

        $invitation = Invitation::create([
            'tenant_id'   => $tenantId,
            'inviter_id'  => $user->id,
            'email'       => $request->input('email'),
            'role'        => $request->input('role'),
            'token'       => $token,
            'status'      => InvitationStatus::PENDING->value,
            'expires_at'  => $expiresAt,
        ]);

        // Send notification (email)
        $invitation->notify(new InvitationNotification($invitation));

        return response()->json([
            'data'    => InvitationDTO::fromModel($invitation)->toArray(),
            'message' => 'Invitation sent.',
        ], Response::HTTP_CREATED);
    }

    /**
     * Accept an invitation by token.
     */
    public function accept(Request $request, $token): JsonResponse
    {
        $invitation = Invitation::where('token', $token)
            ->where('status', InvitationStatus::PENDING->value)
            ->where('expires_at', '>', now())
            ->firstOrFail()
        ;

        // If user is authenticated, link to existing user; else, register new user
        $user = $request->user();

        if (!$user) {
            // Optionally, handle registration flow here (not implemented)
            return response()->json([
                'message' => 'User registration required to accept invitation.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Attach user to tenant with role if not already attached
        $alreadyMember = UserTenant::where('user_id', $user->id)
            ->where('tenant_id', $invitation->tenant_id)
            ->exists()
        ;

        if (!$alreadyMember) {
            UserTenant::create([
                'user_id'   => $user->id,
                'tenant_id' => $invitation->tenant_id,
                'role'      => $invitation->role,
            ]);
        }

        $invitation->update([
            'status'      => InvitationStatus::ACCEPTED->value,
            'accepted_at' => now(),
        ]);

        return response()->json([
            'message' => 'Invitation accepted.',
            'data'    => InvitationDTO::fromModel($invitation)->toArray(),
        ]);
    }
}
