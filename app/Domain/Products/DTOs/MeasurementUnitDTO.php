<?php

namespace App\Domain\Products\DTOs;

use App\Domain\Common\Models\MeasurementUnit;
use Carbon\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;

/**
 * @property string  $id
 * @property string  $code
 * @property string  $name
 * @property ?Carbon $createdAt Internally Carbon, accepts/serializes ISO 8601
 * @property ?Carbon $updatedAt Internally Carbon, accepts/serializes ISO 8601
 */
class MeasurementUnitDTO extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly string $code,
        public readonly string $name,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?Carbon $createdAt = null,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?Carbon $updatedAt = null,
    ) {
    }

    /**
     * Create a new DTO from a model instance.
     */
    public static function fromModel(MeasurementUnit $model): self
    {
        return new self(
            id: $model->id,
            code: $model->code,
            name: $model->name,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
        );
    }
}
