<?php

namespace App\Domain\Common\DTOs;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class MediaMeta implements Castable
{
    public function __construct(
        public ?string $altText = null,
        public ?string $license = null,
    ) {
    }

    public static function castUsing(array $arguments): CastsAttributes
    {
        return new class() implements CastsAttributes {
            public function get($model, string $key, $value, array $attributes): ?MediaMeta
            {
                if (is_null($value)) {
                    return null;
                }

                $data = json_decode($value, true);

                return new MediaMeta(
                    altText: $data['altText'] ?? null,
                    license: $data['license'] ?? null,
                );
            }

            public function set($model, string $key, $value, array $attributes): ?string
            {
                if (is_null($value)) {
                    return null;
                }

                if (!$value instanceof MediaMeta) {
                    throw new \InvalidArgumentException('The given value is not a MediaMeta instance.');
                }

                return json_encode([
                    'altText' => $value->altText,
                    'license' => $value->license,
                ]);
            }
        };
    }
}
