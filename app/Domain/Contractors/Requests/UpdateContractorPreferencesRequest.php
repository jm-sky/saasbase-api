<?php

namespace App\Domain\Contractors\Requests;

use App\Http\Requests\BaseFormRequest;

class UpdateContractorPreferencesRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'defaultPaymentMethodId' => ['nullable', 'ulid', 'exists:payment_methods,id'],
            'defaultCurrency'        => ['nullable', 'string', 'max:10'],
            'defaultLanguage'        => ['nullable', 'string', 'max:10'],
            'defaultPaymentDays'     => ['nullable', 'integer', 'min:0'],
            'defaultTags'            => ['nullable', 'array'],
            'defaultTags.*'          => ['string', 'max:50'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'default_payment_method_id' => $this->input('defaultPaymentMethodId'),
            'default_currency'          => $this->input('defaultCurrency'),
            'default_language'          => $this->input('defaultLanguage'),
            'default_payment_days'      => $this->input('defaultPaymentDays'),
            'default_tags'              => $this->input('defaultTags'),
        ]);
    }
}
