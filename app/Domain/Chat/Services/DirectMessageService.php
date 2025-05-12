<?php

namespace App\Domain\Chat\Services;

use App\Domain\Auth\Models\User;
use App\Domain\Chat\Models\ChatParticipant;
use App\Domain\Chat\Models\ChatRoom;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DirectMessageService
{
    /**
     * Find or create a direct message room for two users in a tenant.
     */
    public function findOrCreateRoom(?string $tenantId, User $userA, User $userB): ChatRoom
    {
        // Always order user IDs to ensure uniqueness
        $userIds = [$userA->id, $userB->id];
        sort($userIds);
        $roomName = 'dm:' . implode('-', $userIds);

        // Try to find existing room
        $room = ChatRoom::where('tenant_id', $tenantId)
            ->where('type', 'direct')
            ->where('name', $roomName)
            ->first()
        ;

        if ($room) {
            return $room;
        }

        // Create new room and participants transactionally
        return DB::transaction(function () use ($tenantId, $userA, $userB, $roomName) {
            $room = ChatRoom::create([
                'tenant_id'   => $tenantId,
                'name'        => $roomName,
                'type'        => 'direct',
                'description' => null,
            ]);

            foreach ([$userA, $userB] as $user) {
                ChatParticipant::firstOrCreate([
                    'chat_room_id' => $room->id,
                    'user_id'      => $user->id,
                ], [
                    'role'         => 'member',
                    'joined_at'    => Carbon::now(),
                    'last_read_at' => null,
                ]);
            }

            return $room;
        });
    }
}
