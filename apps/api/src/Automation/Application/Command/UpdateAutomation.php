<?php

declare(strict_types=1);

namespace App\Automation\Application\Command;

final readonly class UpdateAutomation
{
    public function __construct(
        public string $automationId,
        public string $userId,
        public string $name,
        public ?string $interval = null,
        public ?array $dcaConfig = null,
        public ?array $gridConfig = null,
        public array $parameters = []
    ) {
    }
}
