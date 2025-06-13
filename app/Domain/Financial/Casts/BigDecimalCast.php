<?php

namespace App\Domain\Financial\Casts;

use Brick\Math\BigDecimal;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class BigDecimalCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?BigDecimal
    {
        if (is_null($value)) {
            return null;
        }

        return BigDecimal::of($value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if (is_null($value)) {
            return null;
        }

        if ($value instanceof BigDecimal) {
            return $value->__toString();
        }

        return (string) $value;
    }
}
