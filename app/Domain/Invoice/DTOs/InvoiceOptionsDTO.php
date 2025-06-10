<?php

namespace App\Domain\Invoice\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;

/**
 * @property ?string  $language
 * @property ?string  $template
 * @property bool     $sendEmail
 * @property string[] $emailTo
 */
class InvoiceOptionsDTO extends BaseDataDTO
{
    public function __construct(
        public ?string $language = null,
        public ?string $template = null,
        public bool $sendEmail = false,
        public array $emailTo = [],
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
            language: isset($data['language']) ? $data['language'] : null,
            template: isset($data['template']) ? $data['template'] : null,
            sendEmail: isset($data['sendEmail']) ? $data['sendEmail'] : false,
            emailTo: isset($data['emailTo']) ? $data['emailTo'] : [],
        );
    }
}
