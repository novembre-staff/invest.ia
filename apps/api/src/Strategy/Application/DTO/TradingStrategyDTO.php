<?php

declare(strict_types=1);

namespace App\Strategy\Application\DTO;

use App\Strategy\Domain\Model\TradingStrategy;
use App\Strategy\Domain\ValueObject\Indicator;

final readonly class TradingStrategyDTO
{
    public function __construct(
        public string $id,
        public string $userId,
        public string $name,
        public ?string $description,
        public string $type,
        public string $status,
        public array $symbols,
        public string $timeFrame,
        public array $indicators,
        public array $entryRules,
        public array $exitRules,
        public ?float $positionSizePercent,
        public ?float $maxDrawdownPercent,
        public ?float $stopLossPercent,
        public ?float $takeProfitPercent,
        public ?array $backtestResults,
        public ?string $lastBacktestedAt,
        public ?string $activatedAt,
        public ?string $stoppedAt,
        public string $createdAt,
        public string $updatedAt
    ) {
    }

    public static function fromDomain(TradingStrategy $strategy): self
    {
        return new self(
            id: $strategy->getId()->getValue(),
            userId: $strategy->getUserId()->getValue(),
            name: $strategy->getName(),
            description: $strategy->getDescription(),
            type: $strategy->getType()->value,
            status: $strategy->getStatus()->value,
            symbols: $strategy->getSymbols(),
            timeFrame: $strategy->getTimeFrame()->value,
            indicators: array_map(
                fn($config) => [
                    'indicator' => $config['indicator']->value,
                    'parameters' => $config['parameters'],
                ],
                $strategy->getIndicators()
            ),
            entryRules: $strategy->getEntryRules(),
            exitRules: $strategy->getExitRules(),
            positionSizePercent: $strategy->getPositionSizePercent(),
            maxDrawdownPercent: $strategy->getMaxDrawdownPercent(),
            stopLossPercent: $strategy->getStopLossPercent(),
            takeProfitPercent: $strategy->getTakeProfitPercent(),
            backtestResults: $strategy->getBacktestResults(),
            lastBacktestedAt: $strategy->getLastBacktestedAt()?->format('c'),
            activatedAt: $strategy->getActivatedAt()?->format('c'),
            stoppedAt: $strategy->getStoppedAt()?->format('c'),
            createdAt: $strategy->getCreatedAt()->format('c'),
            updatedAt: $strategy->getUpdatedAt()->format('c')
        );
    }
}
