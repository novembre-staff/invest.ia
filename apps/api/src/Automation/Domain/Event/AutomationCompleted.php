<?php

declare(strict_types=1);

namespace App\Automation\Domain\Event;

final readonly class AutomationCompleted
{
    private function __construct(
        public string $automationId,
        public string $userId,
        public int $totalExecutions,
        public float $totalInvested,
        public float $totalProfit,
        public float $roi,
        public \DateTimeImmutable $occurredAt
    ) {
    }

    public static function now(
        string $automationId,
        string $userId,
        int $totalExecutions,
        float $totalInvested,
        float $totalProfit,
        float $roi
    ): self {
        return new self(
            $automationId,
            $userId,
            $totalExecutions,
            $totalInvested,
            $totalProfit,
            $roi,
            new \DateTimeImmutable()
        );
    }
}
