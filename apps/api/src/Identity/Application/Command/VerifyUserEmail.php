<?php

declare(strict_types=1);

namespace App\Identity\Application\Command;

final class VerifyUserEmail
{
    public function __construct(
        public readonly string $userId,
        public readonly string $token
    ) {}
}
