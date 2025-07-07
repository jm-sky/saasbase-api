<?php

namespace App\Domain\Template\Exceptions;

class TemplateRenderingException extends \Exception
{
    public function __construct(string $message, ?\Throwable $previous = null)
    {
        parent::__construct("Template rendering failed: {$message}", 0, $previous);
    }
}
