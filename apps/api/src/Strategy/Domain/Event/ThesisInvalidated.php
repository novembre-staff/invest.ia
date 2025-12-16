<?php

declare(strict_types=1);

namespace App\Strategy\Domain\Event;

use App\Strategy\Domain\ValueObject\TradingStrategyId;

final readonly class ThesisInvalidated
{
    public function __construct(
        public TradingStrategyId $botId,
        public string $userId,
        public string $positionId,
        public array $reasons,
        public \DateTimeImmutable $occurredAt
    ) {
    }

    public static function now(
        TradingStrategyId $botId,
        string $userId,
        string $positionId,
        array $reasons
    ): self {
        return new self(
            botId: $botId,
            userId: $userId,
            positionId: $positionId,
            reasons: $reasons,
            occurredAt: new \DateTimeImmutable()
        );
    }
}
