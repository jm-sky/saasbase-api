<?php

namespace App\Services\KSeF\Exceptions;

class KSeFException extends \Exception
{
    public function __construct(
        string $message = '',
        int $code = 0,
        ?\Exception $previous = null,
        public readonly ?string $serviceCtx = null,
        public readonly ?string $serviceCode = null,
        public readonly ?string $serviceName = null,
        public readonly ?string $referenceNumber = null,
        public readonly ?array $exceptionDetailList = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
