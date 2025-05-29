<?php

namespace App\Domain\Subscription\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'billable_type'        => ['required', 'string'],
            'billable_id'          => ['required', 'uuid'],
            'subscription_plan_id' => ['required', 'uuid'],
            'payment_method'       => ['required', 'string'],
        ];
    }
}
