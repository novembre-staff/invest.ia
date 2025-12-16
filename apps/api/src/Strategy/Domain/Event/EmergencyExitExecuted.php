<?php

declare(strict_types=1);

namespace App\Strategy\Domain\Event;

use App\Strategy\Domain\ValueObject\TradingStrategyId;

final readonly class EmergencyExitExecuted
{
    public function __construct(
        public TradingStrategyId $botId,
        public string $userId,
        public string $positionId,
        public string $reason,
        public array $triggerConditions,
        public \DateTimeImmutable $occurredAt
    ) {
    }

    public static function now(
        TradingStrategyId $botId,
        string $userId,
        string $positionId,
        string $reason,
        array $triggerConditions
    ): self {
        return new self(
            botId: $botId,
            userId: $userId,
            positionId: $positionId,
            reason: $reason,
            triggerConditions: $triggerConditions,
            occurredAt: new \DateTimeImmutable()
        );
    }
}
