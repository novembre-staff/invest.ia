<?php

declare(strict_types=1);

namespace App\Strategy\Application\Command;

use App\Strategy\Domain\ValueObject\TradingStrategyId;
use App\Strategy\Domain\ValueObject\BotActionType;

final readonly class ProposeBotAction
{
    public function __construct(
        public TradingStrategyId $botId,
        public string $positionId,
        public BotActionType $actionType,
        public string $reasoning,
        public array $marketConditions,
        public ?float $targetPercentage = null, // Pour REDUCE: quel % garder
        public bool $urgent = false
    ) {
    }
}
