<?php

namespace App\Domain\Contractors\Requests;

use App\Domain\Common\DTOs\AddressDTO;
use App\Domain\Common\Enums\AddressType;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class StoreContractorAddressRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'country'     => ['required', 'string', 'max:2', 'exists:countries,code'],
            'postalCode'  => ['nullable', 'string', 'max:20'],
            'city'        => ['required', 'string', 'max:255'],
            'street'      => ['nullable', 'string', 'max:255'],
            'building'    => ['nullable', 'string', 'max:20'],
            'flat'        => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string', 'max:1000'],
            'type'        => ['required', Rule::enum(AddressType::class)],
            'isDefault'   => ['boolean'],
        ];
    }

    public function toAddressDto(): AddressDTO
    {
        $validated = $this->validated();

        return AddressDTO::fromArray($validated);
    }
}
