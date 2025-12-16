<?php

declare(strict_types=1);

namespace App\Strategy\Application\Handler;

use App\Identity\Domain\ValueObject\UserId;
use App\Shared\Application\MessageBusInterface;
use App\Strategy\Application\Command\CreateStrategy;
use App\Strategy\Application\DTO\TradingStrategyDTO;
use App\Strategy\Domain\Event\StrategyCreated;
use App\Strategy\Domain\Model\TradingStrategy;
use App\Strategy\Domain\Repository\TradingStrategyRepositoryInterface;
use App\Strategy\Domain\ValueObject\Indicator;
use App\Strategy\Domain\ValueObject\StrategyType;
use App\Strategy\Domain\ValueObject\TimeFrame;

final readonly class CreateStrategyHandler
{
    public function __construct(
        private TradingStrategyRepositoryInterface $strategyRepository,
        private MessageBusInterface $messageBus
    ) {
    }

    public function __invoke(CreateStrategy $command): TradingStrategyDTO
    {
        // Parse enums
        $type = StrategyType::from($command->type);
        $timeFrame = TimeFrame::from($command->timeFrame);

        // Parse indicators
        $indicators = array_map(function ($config) {
            return [
                'indicator' => Indicator::from($config['indicator']),
                'parameters' => $config['parameters'],
            ];
        }, $command->indicators);

        // Create strategy
        $strategy = TradingStrategy::create(
            userId: UserId::fromString($command->userId),
            name: $command->name,
            description: $command->description,
            type: $type,
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

        // Dispatch event
        $this->messageBus->dispatch(StrategyCreated::now(
            $strategy->getId(),
            $strategy->getUserId(),
            $strategy->getName(),
            $strategy->getType(),
            $strategy->getSymbols()
        ));

        return TradingStrategyDTO::fromDomain($strategy);
    }
}
