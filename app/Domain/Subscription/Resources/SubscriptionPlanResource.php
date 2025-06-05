<?php

namespace App\Domain\Subscription\Resources;

use App\Domain\Auth\Models\User;
use App\Domain\Billing\Resources\BillingPriceResource;
use App\Domain\Subscription\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

/**
 * @mixin SubscriptionPlan
 */
class SubscriptionPlanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var User $user */
        $user     = Auth::user();
        $tenantId = $user->getTenantId();

        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'description'     => $this->description,
            'stripeProductId' => $this->stripe_product_id,
            'prices'          => BillingPriceResource::collection($this->whenLoaded('prices')),
            'features'        => $this->whenLoaded('features', function () {
                return PlanFeatureResource::collection($this->features);
            }),
            'isActive'        => $this->is_active,
            'isCurrent'       => $this->isCurrent($tenantId),
            'createdAt'       => $this->created_at,
            'updatedAt'       => $this->updated_at,
        ];
    }
}
