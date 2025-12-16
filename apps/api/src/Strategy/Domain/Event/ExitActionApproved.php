<?php

declare(strict_types=1);

namespace App\Strategy\Domain\Event;

use App\Strategy\Domain\ValueObject\TradingStrategyId;

final readonly class ExitActionApproved
{
    public function __construct(
        public TradingStrategyId $botId,
        public string $userId,
        public string $positionId,
        public string $proposalId,
        public \DateTimeImmutable $occurredAt
    ) {
    }

    public static function now(
        TradingStrategyId $botId,
        string $userId,
        string $positionId,
        string $proposalId
    ): self {
        return new self(
            botId: $botId,
            userId: $userId,
            positionId: $positionId,
            proposalId: $proposalId,
            occurredAt: new \DateTimeImmutable()
        );
    }
}
