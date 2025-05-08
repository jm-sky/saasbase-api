<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class BaseFormRequest extends FormRequest
{
    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();

        $snakeCaseFields = collect($validated)->mapWithKeys(function ($value, $key) {
            return [Str::snake($key) => $value];
        })->toArray();

        return $snakeCaseFields;
    }
}
