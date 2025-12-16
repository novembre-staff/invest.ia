<?php

declare(strict_types=1);

namespace App\Strategy\Infrastructure\Service;

use App\Strategy\Domain\Repository\TradingStrategyRepositoryInterface;
use App\Strategy\Domain\ValueObject\StrategyStatus;
use App\Trading\Application\Command\PlaceOrder;
use App\Shared\Application\MessageBusInterface;
use App\Strategy\Domain\Service\IndicatorCalculatorInterface;
use App\Market\Infrastructure\Adapter\MarketDataProviderInterface;
use Psr\Log\LoggerInterface;

/**
 * AutoTrader - Executes active strategies automatically
 * Should be run as a background service/cron job
 */
class AutoTrader
{
    public function __construct(
        private readonly TradingStrategyRepositoryInterface $strategyRepository,
        private readonly MarketDataProviderInterface $marketDataProvider,
        private readonly IndicatorCalculatorInterface $indicatorCalculator,
        private readonly MessageBusInterface $messageBus,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Process all active strategies
     */
    public function processActiveStrategies(): void
    {
        $this->logger->info('Starting AutoTrader processing');

        $activeStrategies = $this->strategyRepository->findByStatus(StrategyStatus::ACTIVE);

        $this->logger->info("Found {count} active strategies", [
            'count' => count($activeStrategies),
        ]);

        foreach ($activeStrategies as $strategy) {
            try {
                $this->processStrategy($strategy);
            } catch (\Exception $e) {
                $this->logger->error("Failed to process strategy {$strategy->getName()}", [
                    'strategy_id' => $strategy->getId()->getValue(),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->logger->info('AutoTrader processing completed');
    }

    private function processStrategy($strategy): void
    {
        foreach ($strategy->getSymbols() as $symbol) {
            try {
                $this->processSymbol($strategy, $symbol);
            } catch (\Exception $e) {
                $this->logger->error("Failed to process symbol {$symbol} for strategy", [
                    'strategy_id' => $strategy->getId()->getValue(),
                    'symbol' => $symbol,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function processSymbol($strategy, string $symbol): void
    {
        // Fetch recent market data
        $endDate = new \DateTimeImmutable();
        $startDate = $endDate->modify('-7 days'); // Get enough data for indicators

        $historicalData = $this->marketDataProvider->getHistoricalKlines(
            $symbol,
            $strategy->getTimeFrame()->toBinanceInterval(),
            $startDate,
            $endDate
        );

        if (empty($historicalData)) {
            return;
        }

        // Calculate indicators
        $indicatorValues = [];
        foreach ($strategy->getIndicators() as $indicatorConfig) {
            $indicator = $indicatorConfig['indicator'];
            $parameters = $indicatorConfig['parameters'];

            try {
                $values = $this->indicatorCalculator->calculate($indicator, $historicalData, $parameters);
                $indicatorValues[$indicator->value] = $values;
            } catch (\Exception $e) {
                $this->logger->error("Failed to calculate indicator", [
                    'indicator' => $indicator->value,
                    'error' => $e->getMessage(),
                ]);
                return;
            }
        }

        // Check if entry conditions are met
        $currentIndex = count($historicalData) - 1;
        $entrySignal = $this->checkEntryConditions($strategy, $indicatorValues, $currentIndex);

        if ($entrySignal) {
            $this->executeEntryOrder($strategy, $symbol, $historicalData[$currentIndex]['close']);
        }

        // TODO: Check existing positions for exit signals
    }

    private function checkEntryConditions($strategy, array $indicatorValues, int $index): bool
    {
        foreach ($strategy->getEntryRules() as $rule) {
            if (!$this->evaluateRule($rule, $indicatorValues, $index)) {
                return false;
            }
        }
        return true;
    }

    private function evaluateRule(array $rule, array $indicatorValues, int $index): bool
    {
        $field = $rule['field'];
        $operator = $rule['operator'];
        $value = $rule['value'];

        if (!isset($indicatorValues[$field]) || !isset($indicatorValues[$field][$index])) {
            return false;
        }

        $indicatorValue = $indicatorValues[$field][$index]['value'] ?? null;
        if ($indicatorValue === null) {
            return false;
        }

        return match ($operator) {
            '>' => $indicatorValue > $value,
            '<' => $indicatorValue < $value,
            '>=' => $indicatorValue >= $value,
            '<=' => $indicatorValue <= $value,
            '==' => $indicatorValue == $value,
            'crosses_above' => $this->checkCrossover($indicatorValues[$field], $index, $value, true),
            'crosses_below' => $this->checkCrossover($indicatorValues[$field], $index, $value, false),
            default => false,
        };
    }

    private function checkCrossover(array $values, int $index, float $threshold, bool $above): bool
    {
        if ($index < 1) {
            return false;
        }

        $current = $values[$index]['value'] ?? null;
        $previous = $values[$index - 1]['value'] ?? null;

        if ($current === null || $previous === null) {
            return false;
        }

        if ($above) {
            return $previous <= $threshold && $current > $threshold;
        } else {
            return $previous >= $threshold && $current < $threshold;
        }
    }

    private function executeEntryOrder($strategy, string $symbol, float $currentPrice): void
    {
        $this->logger->info("Entry signal detected for {$symbol}", [
            'strategy_id' => $strategy->getId()->getValue(),
            'strategy_name' => $strategy->getName(),
            'symbol' => $symbol,
            'price' => $currentPrice,
        ]);

        // TODO: Get user's exchange connection
        // TODO: Calculate position size based on strategy configuration
        // TODO: Place order via Trading bounded context
        
        // Example:
        // $command = new PlaceOrder(
        //     userId: $strategy->getUserId()->getValue(),
        //     exchangeConnectionId: $exchangeConnectionId,
        //     symbol: $symbol,
        //     type: 'market',
        //     side: 'buy',
        //     quantity: $quantity,
        //     stopLoss: $strategy->getStopLossPercent() ? $currentPrice * (1 - $strategy->getStopLossPercent() / 100) : null,
        //     takeProfit: $strategy->getTakeProfitPercent() ? $currentPrice * (1 + $strategy->getTakeProfitPercent() / 100) : null
        // );
        // $this->messageBus->dispatch($command);
    }
}
