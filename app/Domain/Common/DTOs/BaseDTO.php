<?php

namespace App\Domain\Common\DTOs;

use App\Domain\Common\Concerns\CreatedFromModelOrArray;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * @template TModel of Model
 *
 * @implements CreatedFromModelOrArray<TModel>
 */
abstract class BaseDTO implements Arrayable, \JsonSerializable, CreatedFromModelOrArray
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
     * Convert the DTO to an array that can be used to create a model.
     *
     * @return array<string,mixed>
     */
    public function toDbArray(): array
    {
        $array = $this->toArray();

        $array = collect($array)->mapWithKeys(function ($value, $key) {
            $key = Str::snake($key);

            return [$key => $value];
        })->toArray();

        return $array;
    }

    /**
     * Convert an array of items to DTOs.
     *
     * @param array<mixed> $items
     *
     * @return array<static>
     */
    public static function collect(array|Model|Collection|EloquentCollection|LengthAwarePaginator $items): array|Collection
    {
        if ($items instanceof Model) {
            return collect([static::fromModel($items)]);
        }

        if ($items instanceof Collection) {
            return $items->map(fn (Model $model) => static::fromModel($model));
        }

        if ($items instanceof EloquentCollection) {
            return $items->map(fn (Model $model) => static::fromModel($model));
        }

        if ($items instanceof LengthAwarePaginator) {
            return $items->items();
        }

        if (is_array($items)) {
            return collect($items)->map(fn (mixed $data) => static::from($data));
        }

        return collect([static::fromModel($items)]);
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
