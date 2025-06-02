<?php

namespace App\Rules;

use App\Services\ProfanityFilterService;
use Illuminate\Contracts\Validation\ValidationRule;

class NoProfanity implements ValidationRule
{
    protected ProfanityFilterService $profanityFilter;

    public function __construct()
    {
        $this->profanityFilter = app(ProfanityFilterService::class);
    }

    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        if (!$this->profanityFilter->hasProfanity($value)) {
            return;
        }

        $fail('The :attribute contains inappropriate content.');
    }
}
