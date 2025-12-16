<?php

declare(strict_types=1);

namespace App\Risk\Application\Command;

final readonly class ActivateGlobalKillSwitch
{
    public function __construct(
        public string $userId,
        public string $reason
    ) {
    }
}
