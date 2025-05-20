<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class BaseFormRequest extends FormRequest
{
    public function validated($key = null, $default = null): mixed
    {
        $validated = parent::validated();

        $snakeCaseFields = collect($validated)->mapWithKeys(function ($value, $key) {
            return [Str::snake($key) => $value];
        })->toArray();

        if (null === $key) {
            return $snakeCaseFields;
        }

        $key = Str::snake($key);

        return $snakeCaseFields[$key] ?? $default;
    }
}
