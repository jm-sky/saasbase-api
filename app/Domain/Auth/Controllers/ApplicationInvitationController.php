<?php

namespace App\Domain\Auth\Controllers;

use App\Domain\Auth\Models\ApplicationInvitation;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Notifications\ApplicationInvitationNotification;
use App\Domain\Auth\Requests\SendApplicationInvitationRequest;
use App\Domain\Tenant\DTOs\ApplicationInvitationDTO;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ApplicationInvitationController extends Controller
{
    public const TOKEN_EXPIRATION_DAYS = 7;

    public function index(Request $request): JsonResponse
    {
        $invitations = ApplicationInvitation::query()
            ->orderBy('created_at', 'desc')
            ->get()
        ;

        return response()->json([
            'data' => $invitations,
        ]);
    }

    public function show($token): JsonResponse
    {
        $invitation = $this->getPendingInvitation($token);

        return response()->json([
            'data' => ApplicationInvitationDTO::fromModel($invitation),
        ]);
    }

    /**
     * Send an invitation to join the application.
     */
    public function send(SendApplicationInvitationRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $token     = Str::uuid()->toString();
        $expiresAt = now()->addDays(self::TOKEN_EXPIRATION_DAYS);

        $invitation = ApplicationInvitation::create([
            'inviter_id' => $user->id,
            'email'      => $request->input('email'),
            'token'      => $token,
            'status'     => 'pending',
            'expires_at' => $expiresAt,
        ]);

        // Send notification (email)
        $invitation->notify(new ApplicationInvitationNotification($invitation));

        return response()->json([
            'data'    => $invitation,
            'message' => 'Invitation sent.',
        ], Response::HTTP_CREATED);
    }

    /**
     * Cancel an invitation.
     */
    public function cancel(Request $request, ApplicationInvitation $invitation): JsonResponse
    {
        abort_if('pending' !== $invitation->status, Response::HTTP_BAD_REQUEST, 'Only pending invitations can be canceled.');

        $invitation->update([
            'status' => 'canceled',
        ]);

        return response()->json([
            'message' => 'Invitation canceled.',
            'data'    => $invitation,
        ]);
    }

    /**
     * Resend an invitation.
     */
    public function resend(Request $request, ApplicationInvitation $invitation): JsonResponse
    {
        abort_if('pending' !== $invitation->status, Response::HTTP_BAD_REQUEST, 'Only pending invitations can be resent.');

        // Update expiration date
        $invitation->update([
            'expires_at' => now()->addDays(7),
        ]);

        // Resend notification
        $invitation->notify(new ApplicationInvitationNotification($invitation));

        return response()->json([
            'message' => 'Invitation resent.',
            'data'    => $invitation,
        ]);
    }

    /**
     * Accept an invitation by token.
     */
    public function accept(Request $request, string $token): JsonResponse
    {
        abort_if(!$request->user(), Response::HTTP_UNAUTHORIZED, 'User not authenticated.');

        $user       = $request->user();
        $invitation = $this->getPendingInvitation($token);

        $invitation->update([
            'status'      => 'accepted',
            'accepted_at' => now(),
        ]);

        return response()->json([
            'message' => 'Invitation accepted.',
            'data'    => $invitation,
        ]);
    }

    /**
     * Reject an invitation by token.
     */
    public function reject(Request $request, string $token): JsonResponse
    {
        abort_if(!$request->user(), Response::HTTP_UNAUTHORIZED, 'User not authenticated.');

        $user       = $request->user();
        $invitation = $this->getPendingInvitation($token);

        $invitation->update([
            'status'      => 'rejected',
            'accepted_at' => now(),
        ]);

        return response()->json([
            'message' => 'Invitation rejected.',
        ]);
    }

    protected function getPendingInvitation(string $token): ApplicationInvitation
    {
        return ApplicationInvitation::where('token', $token)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->firstOrFail()
        ;
    }
}
