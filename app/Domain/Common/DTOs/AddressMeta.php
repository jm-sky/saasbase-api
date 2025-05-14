<?php

namespace App\Domain\Common\DTOs;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class AddressMeta implements Castable
{
    public function __construct(
        public bool $isVerified = false,
        public ?string $verificationDate = null,
    ) {
    }

    public static function castUsing(array $arguments): CastsAttributes
    {
        return new class() implements CastsAttributes {
            public function get($model, string $key, $value, array $attributes): ?AddressMeta
            {
                if (is_null($value)) {
                    return null;
                }

                $data = json_decode($value, true);

                return new AddressMeta(
                    isVerified: $data['isVerified'] ?? false,
                    verificationDate: $data['verificationDate'] ?? null,
                );
            }

            public function set($model, string $key, $value, array $attributes): ?string
            {
                if (is_null($value)) {
                    return null;
                }

                if (!$value instanceof AddressMeta) {
                    throw new \InvalidArgumentException('The given value is not an AddressMeta instance.');
                }

                return json_encode([
                    'isVerified'       => $value->isVerified,
                    'verificationDate' => $value->verificationDate,
                ]);
            }
        };
    }
}
