<?php

namespace App\Domain\Tenant\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Tenant\DTOs\TenantInvitationDTO;
use App\Domain\Tenant\Enums\InvitationStatus;
use App\Domain\Tenant\Enums\TenantActivityType;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Tenant\Models\TenantInvitation;
use App\Domain\Tenant\Models\UserTenant;
use App\Domain\Tenant\Notifications\TenantInvitationNotification;
use App\Domain\Tenant\Requests\SendInvitationRequest;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class TenantInvitationController extends Controller
{
    use AuthorizesRequests;

    public const TOKEN_EXPIRATION_DAYS = 7;

    public function index(Request $request, Tenant $tenant): JsonResponse
    {
        $this->authorize('view', $tenant);

        $invitations = $tenant->invitations()
            ->orderBy('created_at', 'desc')
            ->get()
        ;

        return response()->json([
            'data' => TenantInvitationDTO::collect($invitations),
        ]);
    }

    public function show($token): JsonResponse
    {
        $invitation = $this->getPendingInvitation($token);

        return response()->json([
            'data' => TenantInvitationDTO::fromModel($invitation),
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
        $expiresAt = now()->addDays(self::TOKEN_EXPIRATION_DAYS);

        $invitation = TenantInvitation::create([
            'tenant_id'   => $tenantId,
            'inviter_id'  => $user->id,
            'email'       => $request->input('email'),
            'role'        => $request->input('role'),
            'token'       => $token,
            'status'      => InvitationStatus::PENDING->value,
            'expires_at'  => $expiresAt,
        ]);

        // Send notification (email)
        $invitation->notify(new TenantInvitationNotification($invitation));

        $this->logActivity(
            tenant: Tenant::find($tenantId),
            invitation: $invitation,
            activityType: TenantActivityType::InvitationSent,
            email: $invitation->email,
            role: $invitation->role,
        );

        return response()->json([
            'data'    => TenantInvitationDTO::fromModel($invitation)->toArray(),
            'message' => 'Invitation sent.',
        ], Response::HTTP_CREATED);
    }

    /**
     * Cancel an invitation.
     */
    public function cancel(Request $request, Tenant $tenant, TenantInvitation $invitation): JsonResponse
    {
        $this->authorize('view', $tenant);
        abort_if($invitation->tenant_id !== $tenant->id, Response::HTTP_NOT_FOUND);
        abort_if($invitation->status !== InvitationStatus::PENDING->value, Response::HTTP_BAD_REQUEST, 'Only pending invitations can be canceled.');

        $invitation->update([
            'status' => InvitationStatus::CANCELED->value,
        ]);

        $this->logActivity(
            tenant: $tenant,
            invitation: $invitation,
            activityType: TenantActivityType::InvitationCanceled,
            email: $invitation->email,
            role: $invitation->role,
        );

        return response()->json([
            'message' => 'Invitation canceled.',
            'data'    => TenantInvitationDTO::fromModel($invitation)->toArray(),
        ]);
    }

    /**
     * Resend an invitation.
     */
    public function resend(Request $request, Tenant $tenant, TenantInvitation $invitation): JsonResponse
    {
        $this->authorize('view', $tenant);
        abort_if($invitation->tenant_id !== $tenant->id, Response::HTTP_NOT_FOUND);
        abort_if($invitation->status !== InvitationStatus::PENDING->value, Response::HTTP_BAD_REQUEST, 'Only pending invitations can be resent.');

        // Update expiration date
        $invitation->update([
            'expires_at' => now()->addDays(7),
        ]);

        // Resend notification
        $invitation->notify(new TenantInvitationNotification($invitation));

        $this->logActivity(
            tenant: $tenant,
            invitation: $invitation,
            activityType: TenantActivityType::InvitationResent,
            email: $invitation->email,
            role: $invitation->role,
        );

        return response()->json([
            'message' => 'Invitation resent.',
            'data'    => TenantInvitationDTO::fromModel($invitation)->toArray(),
        ]);
    }

    /**
     * Accept an invitation by token.
     */
    public function accept(Request $request, $tenant, $token): JsonResponse
    {
        abort_if(!$request->user(), Response::HTTP_UNAUTHORIZED, 'User not authenticated.');

        $user       = $request->user();
        $invitation = $this->getPendingInvitation($token);

        // Attach user to tenant with role if not already attached
        if (!$this->isUserMemberOfTenant($user, $invitation->tenant)) {
            UserTenant::create([
                'user_id'   => $user->id,
                'tenant_id' => $invitation->tenant_id,
                'role'      => $invitation->role,
            ]);
        }

        $invitation->update([
            'status'          => InvitationStatus::ACCEPTED->value,
            'invited_user_id' => $user->id,
            'accepted_at'     => now(),
        ]);

        if ($invitation->email === $user->email && !$user->email_verified_at) {
            $user->update([
                'email_verified_at' => now(),
            ]);
        }

        $this->logActivity(
            tenant: Tenant::find($invitation->tenant_id),
            invitation: $invitation,
            activityType: TenantActivityType::InvitationAccepted,
            user: $user,
        );

        return response()->json([
            'message' => 'Invitation accepted.',
            'data'    => TenantInvitationDTO::fromModel($invitation)->toArray(),
        ]);
    }

    /**
     * Accept an invitation by token.
     */
    public function reject(Request $request, $tenant, $token): JsonResponse
    {
        abort_if(!$request->user(), Response::HTTP_UNAUTHORIZED, 'User not authenticated.');

        $user       = $request->user();
        $invitation = $this->getPendingInvitation($token);

        $invitation->update([
            'status'      => InvitationStatus::REJECTED->value,
            'accepted_at' => now(),
        ]);

        $this->logActivity(
            tenant: Tenant::find($invitation->tenant_id),
            invitation: $invitation,
            activityType: TenantActivityType::InvitationRejected,
            user: $user,
        );

        return response()->json([
            'message' => 'Invitation rejected.',
        ]);
    }

    protected function getPendingInvitation(string $token): TenantInvitation
    {
        return TenantInvitation::where('token', $token)
            ->where('status', InvitationStatus::PENDING->value)
            ->where('expires_at', '>', now())
            ->firstOrFail()
        ;
    }

    protected function isUserMemberOfTenant(User $user, Tenant $tenant): bool
    {
        return UserTenant::where('user_id', $user->id)
            ->where('tenant_id', $tenant->id)
            ->exists()
        ;
    }

    protected function logActivity(
        Tenant $tenant,
        TenantInvitation $invitation,
        TenantActivityType $activityType,
        ?User $user = null,
        ?string $email = null,
        ?string $role = null,
    ): void {
        $properties = [
            'tenant_id'     => $tenant->id,
            'invitation_id' => $invitation->id,
        ];

        if ($user) {
            $properties['user_id'] = $user->id;
        }

        if ($email) {
            $properties['email'] = $email;
        }

        if ($role) {
            $properties['role'] = $role;
        }

        activity()
            ->performedOn($tenant)
            ->withProperties($properties)
            ->event($activityType->value)
            ->log($activityType->label())
        ;
    }
}
