<?php

namespace App\Domain\Common\DTOs;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class TagMeta implements Castable
{
    public function __construct(
        public ?string $color = null,
    ) {
    }

    public static function castUsing(array $arguments): CastsAttributes
    {
        return new class() implements CastsAttributes {
            public function get($model, string $key, $value, array $attributes): ?TagMeta
            {
                if (is_null($value)) {
                    return null;
                }

                $data = json_decode($value, true);

                return new TagMeta(
                    color: $data['color'] ?? null,
                );
            }

            public function set($model, string $key, $value, array $attributes): ?string
            {
                if (is_null($value)) {
                    return null;
                }

                if (!$value instanceof TagMeta) {
                    throw new \InvalidArgumentException('The given value is not a TagMeta instance.');
                }

                return json_encode([
                    'color' => $value->color,
                ]);
            }
        };
    }
}
