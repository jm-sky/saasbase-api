<?php

namespace App\Domain\Template\Exceptions;

class TemplateNotFoundException extends \Exception
{
    public function __construct(string $templateName)
    {
        parent::__construct("Template '{$templateName}' not found");
    }
}
