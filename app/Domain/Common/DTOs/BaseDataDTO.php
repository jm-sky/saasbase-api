<?php

namespace App\Domain\Common\DTOs;

use Illuminate\Contracts\Support\Arrayable;

abstract class BaseDataDTO implements Arrayable, \JsonSerializable
{
    abstract public static function fromArray(array $data): static;

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
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
