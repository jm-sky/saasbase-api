<?php

namespace App\Domain\Auth\Casts;

use App\Domain\Auth\ValueObjects\UserConfig;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class UserConfigCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?UserConfig
    {
        if (is_null($value)) {
            return null;
        }

        return UserConfig::fromArray(json_decode($value, true));
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value instanceof UserConfig) {
            return json_encode($value->toArray());
        }

        if (is_array($value)) {
            return json_encode($value);
        }

        return $value;
    }
}
