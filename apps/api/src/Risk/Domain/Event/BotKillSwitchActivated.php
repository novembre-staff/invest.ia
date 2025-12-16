<?php

declare(strict_types=1);

namespace App\Risk\Domain\Event;

use App\Strategy\Domain\ValueObject\TradingStrategyId;

final readonly class BotKillSwitchActivated
{
    public function __construct(
        public TradingStrategyId $botId,
        public string $userId,
        public string $reason,
        public \DateTimeImmutable $occurredAt
    ) {
    }

    public static function now(
        TradingStrategyId $botId,
        string $userId,
        string $reason
    ): self {
        return new self(
            botId: $botId,
            userId: $userId,
            reason: $reason,
            occurredAt: new \DateTimeImmutable()
        );
    }
}
