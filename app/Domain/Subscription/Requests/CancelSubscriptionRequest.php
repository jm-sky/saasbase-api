<?php

namespace App\Domain\Subscription\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CancelSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'at_period_end' => ['sometimes', 'boolean'],
        ];
    }
}
