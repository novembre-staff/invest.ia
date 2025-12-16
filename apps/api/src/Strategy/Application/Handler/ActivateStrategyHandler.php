<?php

declare(strict_types=1);

namespace App\Strategy\Application\Handler;

use App\Shared\Application\MessageBusInterface;
use App\Strategy\Application\Command\ActivateStrategy;
use App\Strategy\Application\DTO\TradingStrategyDTO;
use App\Strategy\Domain\Event\StrategyActivated;
use App\Strategy\Domain\Repository\TradingStrategyRepositoryInterface;
use App\Strategy\Domain\ValueObject\StrategyId;

final readonly class ActivateStrategyHandler
{
    public function __construct(
        private TradingStrategyRepositoryInterface $strategyRepository,
        private MessageBusInterface $messageBus
    ) {
    }

    public function __invoke(ActivateStrategy $command): TradingStrategyDTO
    {
        $strategy = $this->strategyRepository->findById(StrategyId::fromString($command->strategyId));

        if (!$strategy) {
            throw new \DomainException('Strategy not found');
        }

        // Verify ownership
        if ($strategy->getUserId()->getValue() !== $command->userId) {
            throw new \DomainException('Unauthorized access to strategy');
        }

        $strategy->activate();
        $this->strategyRepository->save($strategy);

        // Dispatch event
        $this->messageBus->dispatch(StrategyActivated::now(
            $strategy->getId(),
            $strategy->getUserId(),
            $strategy->getName(),
            $strategy->getSymbols()
        ));

        return TradingStrategyDTO::fromDomain($strategy);
    }
}
