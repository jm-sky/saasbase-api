<?php

namespace App\Services\KSeF\DTOs;

class CredentialsRoleDTO
{
    public function __construct(
        public readonly string $type,
        public readonly string $roleType,
        public readonly string $roleDescription
    ) {
    }
}
