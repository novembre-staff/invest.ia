<?php

declare(strict_types=1);

namespace App\Strategy\Application\Command;

use App\Strategy\Domain\ValueObject\TradingStrategyId;

final readonly class VerifyThesisValidity
{
    public function __construct(
        public TradingStrategyId $botId,
        public string $positionId,
        public array $currentMarketData
    ) {
    }
}
