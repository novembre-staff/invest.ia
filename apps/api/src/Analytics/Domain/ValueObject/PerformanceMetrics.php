<?php

declare(strict_types=1);

namespace App\Analytics\Domain\ValueObject;

final readonly class PerformanceMetrics
{
    public function __construct(
        private float $totalReturn,
        private float $totalReturnPercent,
        private float $dailyReturn,
        private float $weeklyReturn,
        private float $monthlyReturn,
        private float $yearlyReturn,
        private float $sharpeRatio,
        private float $sortinoRatio,
        private float $maxDrawdown,
        private float $volatility,
        private int $winningTrades,
        private int $losingTrades,
        private float $winRate,
        private float $averageWin,
        private float $averageLoss,
        private float $profitFactor
    ) {
    }

    public function getTotalReturn(): float
    {
        return $this->totalReturn;
    }

    public function getTotalReturnPercent(): float
    {
        return $this->totalReturnPercent;
    }

    public function getDailyReturn(): float
    {
        return $this->dailyReturn;
    }

    public function getWeeklyReturn(): float
    {
        return $this->weeklyReturn;
    }

    public function getMonthlyReturn(): float
    {
        return $this->monthlyReturn;
    }

    public function getYearlyReturn(): float
    {
        return $this->yearlyReturn;
    }

    public function getSharpeRatio(): float
    {
        return $this->sharpeRatio;
    }

    public function getSortinoRatio(): float
    {
        return $this->sortinoRatio;
    }

    public function getMaxDrawdown(): float
    {
        return $this->maxDrawdown;
    }

    public function getVolatility(): float
    {
        return $this->volatility;
    }

    public function getWinningTrades(): int
    {
        return $this->winningTrades;
    }

    public function getLosingTrades(): int
    {
        return $this->losingTrades;
    }

    public function getTotalTrades(): int
    {
        return $this->winningTrades + $this->losingTrades;
    }

    public function getWinRate(): float
    {
        return $this->winRate;
    }

    public function getAverageWin(): float
    {
        return $this->averageWin;
    }

    public function getAverageLoss(): float
    {
        return $this->averageLoss;
    }

    public function getProfitFactor(): float
    {
        return $this->profitFactor;
    }

    public function toArray(): array
    {
        return [
            'total_return' => $this->totalReturn,
            'total_return_percent' => $this->totalReturnPercent,
            'daily_return' => $this->dailyReturn,
            'weekly_return' => $this->weeklyReturn,
            'monthly_return' => $this->monthlyReturn,
            'yearly_return' => $this->yearlyReturn,
            'sharpe_ratio' => $this->sharpeRatio,
            'sortino_ratio' => $this->sortinoRatio,
            'max_drawdown' => $this->maxDrawdown,
            'volatility' => $this->volatility,
            'winning_trades' => $this->winningTrades,
            'losing_trades' => $this->losingTrades,
            'total_trades' => $this->getTotalTrades(),
            'win_rate' => $this->winRate,
            'average_win' => $this->averageWin,
            'average_loss' => $this->averageLoss,
            'profit_factor' => $this->profitFactor,
        ];
    }
}
