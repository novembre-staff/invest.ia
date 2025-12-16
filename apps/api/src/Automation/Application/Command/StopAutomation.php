<?php

declare(strict_types=1);

namespace App\Automation\Application\Command;

final readonly class StopAutomation
{
    public function __construct(
        public string $automationId,
        public string $userId
    ) {
    }
}
