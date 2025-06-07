<?php

namespace App\Services\LogoFetcher\DTOs;

class LogoCandidate
{
    public function __construct(
        public string $url,
        public string $source, // e.g. "Clearbit", "Gravatar"
        public ?int $width = null,
        public ?int $height = null,
        public ?string $mime = null,
        public ?int $score = null,
    ) {
    }

    public function ratio(): ?float
    {
        return $this->width && $this->height
            ? $this->width / $this->height
            : null;
    }

    public function resolution(): ?int
    {
        return $this->width && $this->height
            ? $this->width * $this->height
            : null;
    }
}
