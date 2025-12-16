<?php

declare(strict_types=1);

namespace App\Automation\Application\Command;

final readonly class CreateAutomation
{
    public function __construct(
        public string $userId,
        public string $name,
        public string $type,
        public string $symbol,
        public ?string $interval = null,
        public ?array $dcaConfig = null,
        public ?array $gridConfig = null,
        public array $parameters = []
    ) {
    }
}
