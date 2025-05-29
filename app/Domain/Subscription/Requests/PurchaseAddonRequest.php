<?php

namespace App\Domain\Subscription\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseAddonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'billable_type'    => ['required', 'string'],
            'billable_id'      => ['required', 'uuid'],
            'addon_package_id' => ['required', 'uuid'],
            'recurring'        => ['sometimes', 'boolean'],
        ];
    }
}
