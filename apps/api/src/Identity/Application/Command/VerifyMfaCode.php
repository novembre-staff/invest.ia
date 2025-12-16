<?php

declare(strict_types=1);

namespace App\Identity\Application\Command;

final readonly class VerifyMfaCode
{
    public function __construct(
        public string $userId,
        public string $code
    ) {}
}
