<?php

namespace App\Domain\Subscription\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subscription_plan_id' => ['sometimes', 'uuid'],
            'payment_method'       => ['sometimes', 'string'],
            'status'               => ['sometimes', 'string'],
        ];
    }
}
