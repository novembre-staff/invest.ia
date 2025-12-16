<?php

declare(strict_types=1);

namespace App\Identity\Application\Command;

final readonly class AuthenticateUser
{
    public function __construct(
        public string $email,
        public string $password,
        public ?string $mfaCode = null
    ) {}
}
