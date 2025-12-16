<?php

declare(strict_types=1);

namespace App\Strategy\Infrastructure\Service;

use App\Market\Infrastructure\Adapter\MarketDataProviderInterface;
use App\Strategy\Domain\Model\TradingStrategy;
use App\Strategy\Domain\Service\BacktestEngineInterface;
use App\Strategy\Domain\Service\IndicatorCalculatorInterface;
use Psr\Log\LoggerInterface;

class SimpleBacktestEngine implements BacktestEngineInterface
{
    public function __construct(
        private readonly MarketDataProviderInterface $marketDataProvider,
        private readonly IndicatorCalculatorInterface $indicatorCalculator,
        private readonly LoggerInterface $logger
    ) {
    }

    public function runBacktest(
        TradingStrategy $strategy,
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate,
        float $initialCapital
    ): array {
        $this->logger->info("Starting backtest for strategy {$strategy->getName()}", [
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'initial_capital' => $initialCapital,
        ]);

        $results = [
            'initialCapital' => $initialCapital,
            'finalCapital' => $initialCapital,
            'totalTrades' => 0,
            'winningTrades' => 0,
            'losingTrades' => 0,
            'winRate' => 0.0,
            'profitability' => 0.0,
            'maxDrawdown' => 0.0,
            'sharpeRatio' => 0.0,
            'trades' => [],
        ];

        foreach ($strategy->getSymbols() as $symbol) {
            $this->backtestSymbol($strategy, $symbol, $startDate, $endDate, $results);
        }

        // Calculate final metrics
        if ($results['totalTrades'] > 0) {
            $results['winRate'] = ($results['winningTrades'] / $results['totalTrades']) * 100;
        }

        $results['profitability'] = (($results['finalCapital'] - $initialCapital) / $initialCapital) * 100;

        $this->logger->info("Backtest completed", [
            'total_trades' => $results['totalTrades'],
            'win_rate' => $results['winRate'],
            'profitability' => $results['profitability'],
        ]);

        return $results;
    }

    private function backtestSymbol(
        TradingStrategy $strategy,
        string $symbol,
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate,
        array &$results
    ): void {
        // Fetch historical data
        $historicalData = $this->marketDataProvider->getHistoricalKlines(
            $symbol,
            $strategy->getTimeFrame()->toBinanceInterval(),
            $startDate,
            $endDate
        );

        if (empty($historicalData)) {
            $this->logger->warning("No historical data for symbol {$symbol}");
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
                $this->logger->error("Failed to calculate indicator {$indicator->value}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Simulate trading
        $position = null;
        $peakCapital = $results['finalCapital'];

        for ($i = 0; $i < count($historicalData); $i++) {
            $candle = $historicalData[$i];
            $currentPrice = $candle['close'];

            // Check if we should enter position
            if ($position === null && $this->checkEntryConditions($strategy, $indicatorValues, $i)) {
                $positionSize = ($results['finalCapital'] * ($strategy->getPositionSizePercent() ?? 10)) / 100;
                $quantity = $positionSize / $currentPrice;

                $position = [
                    'symbol' => $symbol,
                    'entryPrice' => $currentPrice,
                    'quantity' => $quantity,
                    'entryTime' => $candle['time'],
                    'stopLoss' => $strategy->getStopLossPercent() 
                        ? $currentPrice * (1 - $strategy->getStopLossPercent() / 100) 
                        : null,
                    'takeProfit' => $strategy->getTakeProfitPercent()
                        ? $currentPrice * (1 + $strategy->getTakeProfitPercent() / 100)
                        : null,
                ];

                $results['finalCapital'] -= $positionSize;
                continue;
            }

            // Check if we should exit position
            if ($position !== null) {
                $shouldExit = false;
                $exitReason = null;

                // Check stop loss
                if ($position['stopLoss'] && $currentPrice <= $position['stopLoss']) {
                    $shouldExit = true;
                    $exitReason = 'stop_loss';
                }

                // Check take profit
                if ($position['takeProfit'] && $currentPrice >= $position['takeProfit']) {
                    $shouldExit = true;
                    $exitReason = 'take_profit';
                }

                // Check exit conditions
                if (!$shouldExit && $this->checkExitConditions($strategy, $indicatorValues, $i)) {
                    $shouldExit = true;
                    $exitReason = 'exit_signal';
                }

                if ($shouldExit) {
                    $exitValue = $position['quantity'] * $currentPrice;
                    $results['finalCapital'] += $exitValue;

                    $profit = $exitValue - ($position['quantity'] * $position['entryPrice']);
                    $profitPercent = ($profit / ($position['quantity'] * $position['entryPrice'])) * 100;

                    $results['totalTrades']++;
                    if ($profit > 0) {
                        $results['winningTrades']++;
                    } else {
                        $results['losingTrades']++;
                    }

                    $results['trades'][] = [
                        'symbol' => $symbol,
                        'entryPrice' => $position['entryPrice'],
                        'exitPrice' => $currentPrice,
                        'quantity' => $position['quantity'],
                        'profit' => $profit,
                        'profitPercent' => $profitPercent,
                        'entryTime' => $position['entryTime'],
                        'exitTime' => $candle['time'],
                        'exitReason' => $exitReason,
                    ];

                    // Update max drawdown
                    if ($results['finalCapital'] > $peakCapital) {
                        $peakCapital = $results['finalCapital'];
                    }
                    $drawdown = (($peakCapital - $results['finalCapital']) / $peakCapital) * 100;
                    if ($drawdown > $results['maxDrawdown']) {
                        $results['maxDrawdown'] = $drawdown;
                    }

                    $position = null;
                }
            }
        }

        // Close any remaining position
        if ($position !== null) {
            $lastCandle = end($historicalData);
            $exitValue = $position['quantity'] * $lastCandle['close'];
            $results['finalCapital'] += $exitValue;

            $profit = $exitValue - ($position['quantity'] * $position['entryPrice']);
            $results['totalTrades']++;
            if ($profit > 0) {
                $results['winningTrades']++;
            } else {
                $results['losingTrades']++;
            }
        }
    }

    private function checkEntryConditions(TradingStrategy $strategy, array $indicatorValues, int $index): bool
    {
        foreach ($strategy->getEntryRules() as $rule) {
            if (!$this->evaluateRule($rule, $indicatorValues, $index)) {
                return false;
            }
        }
        return true;
    }

    private function checkExitConditions(TradingStrategy $strategy, array $indicatorValues, int $index): bool
    {
        foreach ($strategy->getExitRules() as $rule) {
            if ($this->evaluateRule($rule, $indicatorValues, $index)) {
                return true;
            }
        }
        return false;
    }

    private function evaluateRule(array $rule, array $indicatorValues, int $index): bool
    {
        $field = $rule['field'];
        $operator = $rule['operator'];
        $value = $rule['value'];

        // Get indicator value at current index
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
}
