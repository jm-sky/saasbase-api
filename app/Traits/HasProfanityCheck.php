<?php

namespace App\Traits;

use App\Jobs\DetectProfanityJob;
use App\Services\ProfanityFilterService;

trait HasProfanityCheck
{
    public const PROFANITY_CHECK_FIELDS = ['content', 'description', 'comment'];

    protected static function bootHasProfanityCheck(): void
    {
        static::saved(function ($model) {
            foreach ($model->getProfanityCheckFields() as $field) {
                DetectProfanityJob::dispatch($model, $field);
            }
        });
    }

    public function getProfanityCheckFields(): array
    {
        return $this->profanityCheckFields ?? self::PROFANITY_CHECK_FIELDS;
    }

    public function hasProfanity(string $text): bool
    {
        return app(ProfanityFilterService::class)->hasProfanity($text);
    }

    public function filterProfanity(string $text, string $replacement = '***'): string
    {
        return app(ProfanityFilterService::class)->filterText($text, $replacement);
    }
}
