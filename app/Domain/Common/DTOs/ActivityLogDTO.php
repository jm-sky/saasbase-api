<?php

namespace App\Domain\Common\DTOs;

use App\Domain\Common\Models\Activity;
use App\Domain\Users\DTOs\PublicUserDTO;
use Illuminate\Support\Carbon;

class ActivityLogDTO
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $logName,
        public readonly string $description,
        public readonly ?string $subjectType,
        public readonly ?string $subjectId,
        public readonly ?string $causerType,
        public readonly ?string $causerId,
        public readonly ?string $event,
        public readonly ?string $batchUuid,
        public readonly ?array $properties,
        public readonly ?string $tenantId,
        public readonly ?Carbon $createdAt,
        public readonly ?Carbon $updatedAt,
        public readonly ?array $changes,
        public readonly ?PublicUserDTO $causer,
        public readonly mixed $subject,
    ) {
    }

    public static function from(Activity $activity): self
    {
        return new self(
            id: $activity->id,
            logName: $activity->log_name,
            description: $activity->description,
            subjectType: $activity->subject_type,
            subjectId: $activity->subject_id,
            causerType: $activity->causer_type,
            causerId: $activity->causer_id,
            event: $activity->event,
            batchUuid: $activity->batch_uuid,
            properties: $activity->properties?->toArray(),
            tenantId: $activity->tenant_id,
            createdAt: $activity->created_at,
            updatedAt: $activity->updated_at,
            changes: $activity->changes?->toArray(),
            causer: $activity->causer ? PublicUserDTO::from($activity->causer) : null,
            subject: $activity->subject,
        );
    }

    public static function collect($activities): array
    {
        return collect($activities)->map(fn ($activity) => self::from($activity))->all();
    }
}
