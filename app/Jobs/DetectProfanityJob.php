<?php

namespace App\Jobs;

use App\Services\ProfanityFilterService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DetectProfanityJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected Model $model;

    protected string $field;

    public function __construct(Model $model, string $field)
    {
        $this->model = $model;
        $this->field = $field;
    }

    public function handle(ProfanityFilterService $profanityFilter): void
    {
        $text = $this->model->getAttribute($this->field);

        if ($profanityFilter->hasProfanity($text)) {
            $filteredText = $profanityFilter->filterText($text);
            $this->model->update([
                $this->field => $filteredText,
                'is_flagged' => true,
            ]);
        }
    }
}
