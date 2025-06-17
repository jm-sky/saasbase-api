<?php

namespace App\Domain\Subscription\Resources;

use App\Domain\Subscription\Enums\SubscriptionStatus;
use App\Domain\Subscription\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Subscription
 */
class SubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'status'             => $this->status,
            'statusLabel'        => SubscriptionStatus::from($this->status)->label(),
            'plan'               => new SubscriptionPlanResource($this->whenLoaded('plan')),
            'currentPeriodStart' => $this->current_period_start,
            'currentPeriodEnd'   => $this->current_period_end,
            'cancelAtPeriodEnd'  => $this->cancel_at_period_end,
            'canceledAt'         => $this->canceled_at,
            // 'endedAt'            => $this->ended_at,
            // 'trialStart'         => $this->trial_start,
            // 'trialEnd'           => $this->trial_end,
            'isOnTrial'          => $this->isOnTrial(),
            'isActive'           => $this->isActive(),
            'isCanceled'         => $this->isCanceled(),
            'isPastDue'          => $this->isPastDue(),
            'latestInvoice'      => new SubscriptionInvoiceResource($this->whenLoaded('latestInvoice')),
            'addons'             => AddonPurchaseResource::collection($this->whenLoaded('addons')),
            'createdAt'          => $this->created_at,
            'updatedAt'          => $this->updated_at,
        ];
    }
}
