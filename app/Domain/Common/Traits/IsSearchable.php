<?php

namespace App\Domain\Common\Traits;

use Laravel\Scout\Searchable;

trait IsSearchable
{
    use Searchable;

    public function toSearchableArray(): array
    {
        $array = $this->toArray();

        // Remove sensitive data
        unset($array['password'], $array['remember_token']);

        return $array;
    }

    public function shouldBeSearchable(): bool
    {
        return true;
    }
}
