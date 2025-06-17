<?php

namespace App\Services\KSeF\DTOs;

final class SessionContextDTO
{
    public function __construct(
        public readonly ContextIdentifierDTO $contextIdentifier,
        public readonly ContextNameDTO $contextName,
        public readonly array $credentialsRoleList
    ) {
    }
}
