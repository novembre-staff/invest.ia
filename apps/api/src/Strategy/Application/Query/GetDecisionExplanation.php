<?php

declare(strict_types=1);

namespace App\Strategy\Application\Query;

use App\Strategy\Domain\ValueObject\TradingStrategyId;

final readonly class GetDecisionExplanation
{
    public function __construct(
        public TradingStrategyId $botId,
        public string $decisionId
    ) {
    }
}
