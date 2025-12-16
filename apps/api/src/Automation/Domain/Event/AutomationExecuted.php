<?php

declare(strict_types=1);

namespace App\Automation\Domain\Event;

final readonly class AutomationExecuted
{
    private function __construct(
        public string $automationId,
        public string $userId,
        public float $investedAmount,
        public float $profit,
        public int $executionCount,
        public \DateTimeImmutable $occurredAt
    ) {
    }

    public static function now(
        string $automationId,
        string $userId,
        float $investedAmount,
        float $profit,
        int $executionCount
    ): self {
        return new self(
            $automationId,
            $userId,
            $investedAmount,
            $profit,
            $executionCount,
            new \DateTimeImmutable()
        );
    }
}
