<?php

declare(strict_types=1);

namespace App\Strategy\Domain\Service;

use App\Strategy\Domain\Model\TradingStrategy;

interface BacktestEngineInterface
{
    /**
     * Run backtest for a strategy
     * 
     * @param TradingStrategy $strategy The strategy to backtest
     * @param \DateTimeImmutable $startDate Backtest start date
     * @param \DateTimeImmutable $endDate Backtest end date
     * @param float $initialCapital Initial capital for backtest
     * @return array Backtest results with metrics
     */
    public function runBacktest(
        TradingStrategy $strategy,
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate,
        float $initialCapital
    ): array;
}
