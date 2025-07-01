<?php

namespace App\Domain\Common\Traits;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Model
 */
trait HasActivityLogging
{
    public function logActivity(string $event, array $properties = [], ?string $description = null): void
    {
        $baseProperties = [
            'tenant_id' => request()->user()?->getTenantId(),
        ];

        activity()
            ->performedOn($this)
            ->causedBy(request()->user())
            ->withProperties(array_merge($baseProperties, $properties))
            ->event($event)
            ->log($description ?? $event)
        ;
    }

    public function logModelActivity(string $event, Model $model, array $additionalProperties = []): void
    {
        $properties = [
            $this->getForeignKey()  => $this->getKey(),
            $model->getForeignKey() => $model->getKey(),
        ];

        $this->logActivity($event, array_merge($properties, $additionalProperties));
    }
}
