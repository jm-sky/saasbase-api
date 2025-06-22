<?php

namespace App\Domain\Auth\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Notifications\Notification;

/**
 * @mixin Notification
 *
 * @property string  $id
 * @property string  $type
 * @property array   $data
 * @property ?Carbon $read_at
 * @property Carbon  $created_at
 * @property Carbon  $updated_at
 */
class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            // 'type'      => Str::afterLast($this->type, '\\'),
            'data'      => $this->data,
            'readAt'    => $this->read_at,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}
