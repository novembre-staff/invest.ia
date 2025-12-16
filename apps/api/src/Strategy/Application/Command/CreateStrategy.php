<?php

declare(strict_types=1);

namespace App\Strategy\Application\Command;

final readonly class CreateStrategy
{
    public function __construct(
        public string $userId,
        public string $name,
        public ?string $description,
        public string $type, // StrategyType value
        public array $symbols,
        public string $timeFrame, // TimeFrame value
        public array $indicators, // [['indicator' => 'rsi', 'parameters' => ['period' => 14]]]
        public array $entryRules, // [['field' => 'rsi', 'operator' => '<', 'value' => 30]]
        public array $exitRules,
        public ?float $positionSizePercent = 10.0,
        public ?float $maxDrawdownPercent = 20.0,
        public ?float $stopLossPercent = null,
        public ?float $takeProfitPercent = null
    ) {
    }
}
