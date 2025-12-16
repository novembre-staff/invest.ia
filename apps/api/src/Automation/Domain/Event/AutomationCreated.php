<?php

declare(strict_types=1);

namespace App\Automation\Domain\Event;

final readonly class AutomationCreated
{
    private function __construct(
        public string $automationId,
        public string $userId,
        public string $type,
        public string $symbol,
        public \DateTimeImmutable $occurredAt
    ) {
    }

    public static function now(
        string $automationId,
        string $userId,
        string $type,
        string $symbol
    ): self {
        return new self(
            $automationId,
            $userId,
            $type,
            $symbol,
            new \DateTimeImmutable()
        );
    }
}
