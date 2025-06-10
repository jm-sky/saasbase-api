<?php

namespace App\Domain\Invoice\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;

/**
 * @property string   $language
 * @property string   $template
 * @property bool     $sendEmail
 * @property string[] $emailTo
 */
class InvoiceOptionsDTO extends BaseDataDTO
{
    public function __construct(
        public string $language,
        public string $template,
        public bool $sendEmail,
        public array $emailTo,
    ) {
    }

    public function toArray(): array
    {
        return [
            'language'  => $this->language,
            'template'  => $this->template,
            'sendEmail' => $this->sendEmail,
            'emailTo'   => $this->emailTo,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new static(
            language: $data['language'],
            template: $data['template'],
            sendEmail: $data['sendEmail'],
            emailTo: $data['emailTo'],
        );
    }
}
