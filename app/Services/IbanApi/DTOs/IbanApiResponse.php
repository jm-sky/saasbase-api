<?php

namespace App\Services\IbanApi\DTOs;

use App\Domain\Common\DTOs\BaseDataDTO;

class IbanApiResponse extends BaseDataDTO
{
    public function __construct(
        public int $result,
        public string $message,
        public array $validations,
        public int $expremental,
        public IbanDataDTO $data,
    ) {
    }

    public function toArray(): array
    {
        return [
            'result'      => $this->result,
            'message'     => $this->message,
            'validations' => $this->validations,
            'expremental' => $this->expremental,
            'data'        => $this->data->toArray(),
        ];
    }

    public static function fromArray(array $data): static
    {
        return new self(
            result: $data['result'],
            message: $data['message'],
            validations: $data['validations'],
            expremental: $data['expremental'],
            data: IbanDataDTO::fromArray($data['data']),
        );
    }
}
