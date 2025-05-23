<?php

namespace App\Domain\Common\DTOs;

use Illuminate\Contracts\Support\Arrayable;

abstract class BaseDataDTO implements Arrayable, \JsonSerializable
{
    public static function fromArray(array $data): static
    {
        return new static(...$data);
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public static function collect(?array $data = null): array
    {
        $data ??= [];

        return array_map(
            fn (array $item) => static::fromArray($item),
            $data
        );
    }
}
