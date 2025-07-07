<?php

namespace App\Domain\Template\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class TemplateSettingsCast implements CastsAttributes
{
    public function get(Model $model, string $key, $value, array $attributes): array
    {
        return json_decode($value, true) ?? [];
    }

    public function set(Model $model, string $key, $value, array $attributes): string
    {
        return json_encode($value);
    }
}
