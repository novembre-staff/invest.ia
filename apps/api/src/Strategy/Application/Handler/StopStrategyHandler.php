<?php

declare(strict_types=1);

namespace App\Strategy\Application\Handler;

use App\Strategy\Application\Command\StopStrategy;
use App\Strategy\Application\DTO\TradingStrategyDTO;
use App\Strategy\Domain\Repository\TradingStrategyRepositoryInterface;
use App\Strategy\Domain\ValueObject\StrategyId;

final readonly class StopStrategyHandler
{
    public function __construct(
        private TradingStrategyRepositoryInterface $strategyRepository
    ) {
    }

    public function __invoke(StopStrategy $command): TradingStrategyDTO
    {
        $strategy = $this->strategyRepository->findById(StrategyId::fromString($command->strategyId));

        if (!$strategy) {
            throw new \DomainException('Strategy not found');
        }

        // Verify ownership
        if ($strategy->getUserId()->getValue() !== $command->userId) {
            throw new \DomainException('Unauthorized access to strategy');
        }

        $strategy->stop();
        $this->strategyRepository->save($strategy);

        return TradingStrategyDTO::fromDomain($strategy);
    }
}
