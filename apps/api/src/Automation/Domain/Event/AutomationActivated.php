<?php

declare(strict_types=1);

namespace App\Automation\Domain\Event;

final readonly class AutomationActivated
{
    private function __construct(
        public string $automationId,
        public string $userId,
        public ?\DateTimeImmutable $nextExecutionAt,
        public \DateTimeImmutable $occurredAt
    ) {
    }

    public static function now(
        string $automationId,
        string $userId,
        ?\DateTimeImmutable $nextExecutionAt
    ): self {
        return new self(
            $automationId,
            $userId,
            $nextExecutionAt,
            new \DateTimeImmutable()
        );
    }
}
