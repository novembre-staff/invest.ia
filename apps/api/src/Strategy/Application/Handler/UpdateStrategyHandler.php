<?php

declare(strict_types=1);

namespace App\Strategy\Application\Handler;

use App\Strategy\Application\Command\UpdateStrategy;
use App\Strategy\Application\DTO\TradingStrategyDTO;
use App\Strategy\Domain\Repository\TradingStrategyRepositoryInterface;
use App\Strategy\Domain\ValueObject\Indicator;
use App\Strategy\Domain\ValueObject\StrategyId;
use App\Strategy\Domain\ValueObject\TimeFrame;

final readonly class UpdateStrategyHandler
{
    public function __construct(
        private TradingStrategyRepositoryInterface $strategyRepository
    ) {
    }

    public function __invoke(UpdateStrategy $command): TradingStrategyDTO
    {
        $strategy = $this->strategyRepository->findById(StrategyId::fromString($command->strategyId));

        if (!$strategy) {
            throw new \DomainException('Strategy not found');
        }

        // Verify ownership
        if ($strategy->getUserId()->getValue() !== $command->userId) {
            throw new \DomainException('Unauthorized access to strategy');
        }

        // Parse timeframe and indicators
        $timeFrame = TimeFrame::from($command->timeFrame);
        $indicators = array_map(function ($config) {
            return [
                'indicator' => Indicator::from($config['indicator']),
                'parameters' => $config['parameters'],
            ];
        }, $command->indicators);

        // Update configuration
        $strategy->updateConfiguration(
            name: $command->name,
            description: $command->description,
            symbols: $command->symbols,
            timeFrame: $timeFrame,
            indicators: $indicators,
            entryRules: $command->entryRules,
            exitRules: $command->exitRules,
            positionSizePercent: $command->positionSizePercent,
            maxDrawdownPercent: $command->maxDrawdownPercent,
            stopLossPercent: $command->stopLossPercent,
            takeProfitPercent: $command->takeProfitPercent
        );

        $this->strategyRepository->save($strategy);

        return TradingStrategyDTO::fromDomain($strategy);
    }
}
