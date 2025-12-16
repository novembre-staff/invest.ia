<?php

declare(strict_types=1);

namespace App\Strategy\Domain\Event;

use App\Strategy\Domain\ValueObject\TradingStrategyId;
use App\Strategy\Domain\ValueObject\BotActionType;

final readonly class BotActionProposed
{
    public function __construct(
        public TradingStrategyId $botId,
        public string $userId,
        public string $positionId,
        public BotActionType $actionType,
        public string $reasoning,
        public array $marketConditions,
        public ?float $targetPercentage,
        public bool $urgent,
        public \DateTimeImmutable $occurredAt
    ) {
    }

    public static function now(
        TradingStrategyId $botId,
        string $userId,
        string $positionId,
        BotActionType $actionType,
        string $reasoning,
        array $marketConditions,
        ?float $targetPercentage = null,
        bool $urgent = false
    ): self {
        return new self(
            botId: $botId,
            userId: $userId,
            positionId: $positionId,
            actionType: $actionType,
            reasoning: $reasoning,
            marketConditions: $marketConditions,
            targetPercentage: $targetPercentage,
            urgent: $urgent,
            occurredAt: new \DateTimeImmutable()
        );
    }
}
