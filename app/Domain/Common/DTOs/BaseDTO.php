<?php

namespace App\Domain\Common\DTOs;

use App\Domain\Common\Concerns\CreatedFromModelOrArray;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 *
 * @implements CreatedFromModelOrArray<TModel>
 */
abstract class BaseDTO implements Arrayable, CreatedFromModelOrArray
{
    /**
     * Create a DTO instance from a model or array.
     *
     * @param TModel|array<string,mixed> $data
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

    /**
     * Create a DTO instance from a model.
     *
     * @param TModel $model
     */
    abstract public static function fromModel(Model $model): static;

    /**
     * Create a DTO instance from an array.
     *
     * @param array<string,mixed> $data
     */
    abstract public static function fromArray(array $data): static;

    /**
     * Convert an array of items to DTOs.
     *
     * @param array<mixed> $items
     *
     * @return array<static>
     */
    public static function collect(array $items): array
    {
        return $items;
    }
}
