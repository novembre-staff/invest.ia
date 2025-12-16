<?php

declare(strict_types=1);

namespace App\Identity\Application\Command;

final readonly class EnableMfa
{
    public function __construct(
        public string $userId
    ) {}
}
