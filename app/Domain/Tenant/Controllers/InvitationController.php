<?php

namespace App\Domain\Tenant\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Tenant\DTOs\InvitationDTO;
use App\Domain\Tenant\Enums\InvitationStatus;
use App\Domain\Tenant\Enums\TenantActivityType;
use App\Domain\Tenant\Models\Invitation;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Models\UserTenant;
use App\Domain\Tenant\Notifications\InvitationNotification;
use App\Domain\Tenant\Requests\SendInvitationRequest;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class InvitationController extends Controller
{
    use AuthorizesRequests;

    /**
     * List all invitations for a tenant.
     */
    public function index(Request $request, Tenant $tenant): JsonResponse
    {
        $this->authorize('view', $tenant);

        $invitations = $tenant->invitations()
            ->orderBy('created_at', 'desc')
            ->get()
        ;

        return response()->json([
            'data' => InvitationDTO::collect($invitations),
        ]);
    }

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

        activity()
            ->performedOn(Tenant::find($tenantId))
            ->withProperties([
                'tenant_id'     => $tenantId,
                'invitation_id' => $invitation->id,
                'email'         => $invitation->email,
                'role'          => $invitation->role,
            ])
            ->event(TenantActivityType::InvitationSent->value)
            ->log('Tenant invitation sent')
        ;

        return response()->json([
            'data'    => InvitationDTO::fromModel($invitation)->toArray(),
            'message' => 'Invitation sent.',
        ], Response::HTTP_CREATED);
    }

    public function show(Request $request, $token): JsonResponse
    {
        $invitation = Invitation::where('token', $token)
            ->where('status', InvitationStatus::PENDING->value)
            ->where('expires_at', '>', now())
            ->firstOrFail()
        ;

        return response()->json([
            'data' => InvitationDTO::fromModel($invitation)->toArray(),
        ]);
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

        activity()
            ->performedOn(Tenant::find($invitation->tenant_id))
            ->withProperties([
                'tenant_id'     => $invitation->tenant_id,
                'invitation_id' => $invitation->id,
                'user_id'       => $user->id,
                'role'          => $invitation->role,
            ])
            ->event(TenantActivityType::InvitationAccepted->value)
            ->log('Tenant invitation accepted')
        ;

        return response()->json([
            'message' => 'Invitation accepted.',
            'data'    => InvitationDTO::fromModel($invitation)->toArray(),
        ]);
    }

    /**
     * Cancel an invitation.
     */
    public function cancel(Request $request, Tenant $tenant, Invitation $invitation): JsonResponse
    {
        $this->authorize('view', $tenant);
        abort_if($invitation->tenant_id !== $tenant->id, Response::HTTP_NOT_FOUND);
        abort_if($invitation->status !== InvitationStatus::PENDING->value, Response::HTTP_BAD_REQUEST, 'Only pending invitations can be canceled.');

        $invitation->update([
            'status' => InvitationStatus::CANCELED->value,
        ]);

        activity()
            ->performedOn($tenant)
            ->withProperties([
                'tenant_id'     => $tenant->id,
                'invitation_id' => $invitation->id,
                'email'         => $invitation->email,
                'role'          => $invitation->role,
            ])
            ->event(TenantActivityType::InvitationCanceled->value)
            ->log('Tenant invitation canceled')
        ;

        return response()->json([
            'message' => 'Invitation canceled.',
            'data'    => InvitationDTO::fromModel($invitation)->toArray(),
        ]);
    }

    /**
     * Resend an invitation.
     */
    public function resend(Request $request, Tenant $tenant, Invitation $invitation): JsonResponse
    {
        $this->authorize('view', $tenant);
        abort_if($invitation->tenant_id !== $tenant->id, Response::HTTP_NOT_FOUND);
        abort_if($invitation->status !== InvitationStatus::PENDING->value, Response::HTTP_BAD_REQUEST, 'Only pending invitations can be resent.');

        // Update expiration date
        $invitation->update([
            'expires_at' => now()->addDays(7),
        ]);

        // Resend notification
        $invitation->notify(new InvitationNotification($invitation));

        activity()
            ->performedOn($tenant)
            ->withProperties([
                'tenant_id'     => $tenant->id,
                'invitation_id' => $invitation->id,
                'email'         => $invitation->email,
                'role'          => $invitation->role,
            ])
            ->event(TenantActivityType::InvitationResent->value)
            ->log('Tenant invitation resent')
        ;

        return response()->json([
            'message' => 'Invitation resent.',
            'data'    => InvitationDTO::fromModel($invitation)->toArray(),
        ]);
    }
}
