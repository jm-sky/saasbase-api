<?php

namespace App\Domain\Common\Requests;

use App\Domain\Common\Models\Country;
use Illuminate\Foundation\Http\FormRequest;

class CountryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string|array<string>>>
     */
    public function rules(): array
    {
        /** @var ?Country $country */
        $country = $this->route('country');
        $countryId = $country?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'size:2', 'unique:countries,code,' . $countryId],
            'code3' => ['required', 'string', 'size:3', 'unique:countries,code3,' . $countryId],
            'numeric_code' => ['required', 'string', 'size:3', 'unique:countries,numeric_code,' . $countryId],
            'phone_code' => ['required', 'string', 'max:10'],
            'capital' => ['nullable', 'string', 'max:255'],
            'currency' => ['nullable', 'string', 'max:255'],
            'currency_code' => ['nullable', 'string', 'max:3'],
            'currency_symbol' => ['nullable', 'string', 'max:5'],
            'tld' => ['nullable', 'string', 'max:10'],
            'native' => ['nullable', 'string', 'max:255'],
            'region' => ['nullable', 'string', 'max:255'],
            'subregion' => ['nullable', 'string', 'max:255'],
            'emoji' => ['nullable', 'string', 'max:10'],
            'emojiU' => ['nullable', 'string', 'max:20'],
        ];
    }
}
