<?php

namespace App\Services;

use ConsoleTVs\Profanity\Facades\Profanity;

class ProfanityFilterService
{
    protected array $languages = ['en', 'pl', 'ru', 'ua'];

    public function __construct()
    {
        $this->loadDictionary();
    }

    protected function loadDictionary(): void
    {
        $dictionaryPath = storage_path('app/profanity/dictionary.json');

        if (file_exists($dictionaryPath)) {
            Profanity::dictionary($dictionaryPath);
        }
    }

    public function hasProfanity(string $text): bool
    {
        return !Profanity::blocker($text, languages: $this->languages)->clean();
    }

    public function filterText(string $text, string $replacement = '***'): string
    {
        return Profanity::blocker($text, $replacement, languages: $this->languages)->filter();
    }
}
