<?php

declare(strict_types=1);

namespace App\Strategy\Application\Handler;

use App\Shared\Application\MessageBusInterface;
use App\Strategy\Application\Command\RunBacktest;
use App\Strategy\Application\DTO\TradingStrategyDTO;
use App\Strategy\Domain\Event\BacktestCompleted;
use App\Strategy\Domain\Repository\TradingStrategyRepositoryInterface;
use App\Strategy\Domain\Service\BacktestEngineInterface;
use App\Strategy\Domain\ValueObject\StrategyId;
use Psr\Log\LoggerInterface;

final readonly class RunBacktestHandler
{
    public function __construct(
        private TradingStrategyRepositoryInterface $strategyRepository,
        private BacktestEngineInterface $backtestEngine,
        private MessageBusInterface $messageBus,
        private LoggerInterface $logger
    ) {
    }

    public function __invoke(RunBacktest $command): TradingStrategyDTO
    {
        $strategy = $this->strategyRepository->findById(StrategyId::fromString($command->strategyId));

        if (!$strategy) {
            throw new \DomainException('Strategy not found');
        }

        // Verify ownership
        if ($strategy->getUserId()->getValue() !== $command->userId) {
            throw new \DomainException('Unauthorized access to strategy');
        }

        try {
            // Mark as backtesting
            $strategy->startBacktest();
            $this->strategyRepository->save($strategy);

            // Run backtest
            $startDate = new \DateTimeImmutable($command->startDate);
            $endDate = new \DateTimeImmutable($command->endDate);

            $results = $this->backtestEngine->runBacktest(
                $strategy,
                $startDate,
                $endDate,
                $command->initialCapital
            );

            // Complete backtest with results
            $strategy->completeBacktest($results);
            $this->strategyRepository->save($strategy);

            // Dispatch event
            $passed = $strategy->getStatus()->value === 'backtest_passed';
            $this->messageBus->dispatch(BacktestCompleted::now(
                $strategy->getId(),
                $strategy->getUserId(),
                $passed,
                $results
            ));

        } catch (\Exception $e) {
            $this->logger->error('Backtest failed', [
                'strategy_id' => $command->strategyId,
                'error' => $e->getMessage(),
            ]);

            // Mark backtest as failed
            $strategy->completeBacktest([
                'error' => $e->getMessage(),
                'totalTrades' => 0,
                'winRate' => 0,
                'profitability' => -100,
                'maxDrawdown' => 100,
            ]);
            $this->strategyRepository->save($strategy);

            throw $e;
        }

        return TradingStrategyDTO::fromDomain($strategy);
    }
}
