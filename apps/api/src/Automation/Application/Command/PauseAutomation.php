<?php

declare(strict_types=1);

namespace App\Automation\Application\Command;

final readonly class PauseAutomation
{
    public function __construct(
        public string $automationId,
        public string $userId
    ) {
    }
}
