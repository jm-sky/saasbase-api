<?php

namespace App\Domain\Common\Traits;

use Illuminate\Database\Eloquent\Model;

trait FromModelOrArray
{
    /**
     * Create a DTO instance from a model or array.
     *
     * @template T of Model
     *
     * @param T|array<string,mixed> $data
     *
     * @throws \InvalidArgumentException
     */
    public static function from(mixed $data): static
    {
        if ($data instanceof Model) {
            return static::fromModel($data);
        }

        if (is_array($data)) {
            return static::fromArray($data);
        }

        throw new \InvalidArgumentException('Invalid data type');
    }
}
