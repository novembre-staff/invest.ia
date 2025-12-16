<?php

declare(strict_types=1);

namespace App\Strategy\Application\Command;

final readonly class ActivateStrategy
{
    public function __construct(
        public string $strategyId,
        public string $userId
    ) {
    }
}
