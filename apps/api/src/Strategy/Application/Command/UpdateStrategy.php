<?php

declare(strict_types=1);

namespace App\Strategy\Application\Command;

final readonly class UpdateStrategy
{
    public function __construct(
        public string $strategyId,
        public string $userId,
        public string $name,
        public ?string $description,
        public array $symbols,
        public string $timeFrame,
        public array $indicators,
        public array $entryRules,
        public array $exitRules,
        public ?float $positionSizePercent,
        public ?float $maxDrawdownPercent,
        public ?float $stopLossPercent,
        public ?float $takeProfitPercent
    ) {
    }
}
