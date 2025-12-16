<?php

declare(strict_types=1);

namespace App\Risk\Application\Command;

use App\Strategy\Domain\ValueObject\TradingStrategyId;

final readonly class ActivateBotKillSwitch
{
    public function __construct(
        public TradingStrategyId $botId,
        public string $userId,
        public string $reason
    ) {
    }
}
