<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;

class BaseFormRequest extends FormRequest
{
    protected function mergeTenantId(): void
    {
        /** @var User $user */
        $user     = Auth::user();
        $tenantId = $this->input('tenantId');

        $this->merge([
            'tenantId' => $tenantId ?? $user->getTenantId(),
        ]);
    }

    protected function checkTenantId(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var User $user */
            $user     = Auth::user();
            $tenantId = $this->input('tenantId');

            if (!$user->isAdmin() && $tenantId !== $user->getTenantId()) {
                $validator->errors()->add('tenantId', 'You are not allowed to use this tenant ID.');
            }
        });
    }

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
