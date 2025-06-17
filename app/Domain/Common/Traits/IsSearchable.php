<?php

namespace App\Domain\Common\Traits;

use Laravel\Scout\ModelObserver;
use Laravel\Scout\Searchable;
use Laravel\Scout\SearchableScope;

trait IsSearchable
{
    use Searchable;

    public static function bootSearchable()
    {
        if (!config('scout.enabled')) {
            return;
        }

        static::addGlobalScope(new SearchableScope());

        static::observe(new ModelObserver());

        // @phpstan-ignore-next-line
        (new static())->registerSearchableMacros();
    }

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
