<?php

declare(strict_types=1);

namespace App\Risk\Domain\Event;

final readonly class GlobalKillSwitchActivated
{
    public function __construct(
        public string $userId,
        public string $reason,
        public int $stoppedBots,
        public int $cancelledOrders,
        public \DateTimeImmutable $occurredAt
    ) {
    }

    public static function now(
        string $userId,
        string $reason,
        int $stoppedBots,
        int $cancelledOrders
    ): self {
        return new self(
            userId: $userId,
            reason: $reason,
            stoppedBots: $stoppedBots,
            cancelledOrders: $cancelledOrders,
            occurredAt: new \DateTimeImmutable()
        );
    }
}
