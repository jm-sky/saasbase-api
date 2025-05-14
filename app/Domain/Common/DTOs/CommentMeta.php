<?php

namespace App\Domain\Common\DTOs;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class CommentMeta implements Castable
{
    public function __construct(
        public bool $isPinned = false,
        public ?string $displayColor = null,
    ) {
    }

    public static function castUsing(array $arguments): CastsAttributes
    {
        return new class() implements CastsAttributes {
            public function get($model, string $key, $value, array $attributes): ?CommentMeta
            {
                if (is_null($value)) {
                    return null;
                }

                $data = json_decode($value, true);

                return new CommentMeta(
                    isPinned: $data['isPinned'] ?? false,
                    displayColor: $data['displayColor'] ?? null,
                );
            }

            public function set($model, string $key, $value, array $attributes): ?string
            {
                if (is_null($value)) {
                    return null;
                }

                if (!$value instanceof CommentMeta) {
                    throw new \InvalidArgumentException('The given value is not a CommentMeta instance.');
                }

                return json_encode([
                    'isPinned'     => $value->isPinned,
                    'displayColor' => $value->displayColor,
                ]);
            }
        };
    }
}
