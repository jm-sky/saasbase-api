<?php

namespace App\Domain\Common\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 */
interface CreatedFromModelOrArray
{
    /**
     * Create a DTO instance from a model or array.
     *
     * @param TModel|array<string,mixed> $data
     */
    public static function from(mixed $data): static;

    /**
     * Create a DTO instance from a model.
     *
     * @param TModel $data
     */
    public static function fromModel(Model $data): static;

    /**
     * Create a DTO instance from an array.
     *
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): static;
}
