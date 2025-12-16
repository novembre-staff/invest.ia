<?php

declare(strict_types=1);

namespace App\Analytics\Infrastructure\Service;

use App\Analytics\Domain\Service\PerformanceCalculatorInterface;
use App\Analytics\Domain\ValueObject\PerformanceMetrics;
use App\Identity\Domain\ValueObject\UserId;
use App\Portfolio\Infrastructure\Adapter\PortfolioProviderInterface;
use Psr\Log\LoggerInterface;

class SimplePerformanceCalculator implements PerformanceCalculatorInterface
{
    public function __construct(
        private PortfolioProviderInterface $portfolioProvider,
        private LoggerInterface $logger
    ) {
    }

    public function calculateMetrics(
        UserId $userId,
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate
    ): PerformanceMetrics {
        // Get portfolio data
        $portfolio = $this->portfolioProvider->getPortfolio($userId->toString());
        
        // Calculate returns
        $returns = $this->calculateReturns($userId, $startDate, $endDate);
        
        // Calculate metrics
        $totalReturn = $returns['total_return'] ?? 0.0;
        $initialValue = $returns['initial_value'] ?? 1000.0;
        $totalReturnPercent = $initialValue > 0 ? ($totalReturn / $initialValue) * 100 : 0.0;

        // Get trade statistics
        $tradeStats = $this->calculateTradeStatistics($userId, $startDate, $endDate);

        return new PerformanceMetrics(
            totalReturn: $totalReturn,
            totalReturnPercent: $totalReturnPercent,
            dailyReturn: $returns['daily_return'] ?? 0.0,
            weeklyReturn: $returns['weekly_return'] ?? 0.0,
            monthlyReturn: $returns['monthly_return'] ?? 0.0,
            yearlyReturn: $returns['yearly_return'] ?? 0.0,
            sharpeRatio: $this->calculateSharpeRatio($returns['returns_array'] ?? []),
            sortinoRatio: $this->calculateSortinoRatio($returns['returns_array'] ?? []),
            maxDrawdown: $this->calculateMaxDrawdown($returns['equity_curve'] ?? []),
            volatility: $this->calculateVolatility($returns['returns_array'] ?? []),
            winningTrades: $tradeStats['winning_trades'],
            losingTrades: $tradeStats['losing_trades'],
            winRate: $tradeStats['win_rate'],
            averageWin: $tradeStats['average_win'],
            averageLoss: $tradeStats['average_loss'],
            profitFactor: $tradeStats['profit_factor']
        );
    }

    public function calculateReturns(
        UserId $userId,
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate
    ): array {
        // TODO: Implement actual historical data retrieval
        // For now, return sample data structure
        
        return [
            'initial_value' => 10000.0,
            'final_value' => 11500.0,
            'total_return' => 1500.0,
            'daily_return' => 0.5,
            'weekly_return' => 3.2,
            'monthly_return' => 12.5,
            'yearly_return' => 15.0,
            'returns_array' => [],
            'equity_curve' => []
        ];
    }

    private function calculateTradeStatistics(
        UserId $userId,
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate
    ): array {
        // TODO: Retrieve actual trade data from repository
        
        return [
            'winning_trades' => 0,
            'losing_trades' => 0,
            'win_rate' => 0.0,
            'average_win' => 0.0,
            'average_loss' => 0.0,
            'profit_factor' => 0.0
        ];
    }

    private function calculateSharpeRatio(array $returns, float $riskFreeRate = 0.02): float
    {
        if (empty($returns)) {
            return 0.0;
        }

        $avgReturn = array_sum($returns) / count($returns);
        $excessReturn = $avgReturn - ($riskFreeRate / 365);
        
        $variance = 0.0;
        foreach ($returns as $return) {
            $variance += pow($return - $avgReturn, 2);
        }
        $stdDev = sqrt($variance / count($returns));

        if ($stdDev == 0) {
            return 0.0;
        }

        return ($excessReturn / $stdDev) * sqrt(365);
    }

    private function calculateSortinoRatio(array $returns, float $targetReturn = 0.0): float
    {
        if (empty($returns)) {
            return 0.0;
        }

        $avgReturn = array_sum($returns) / count($returns);
        
        // Calculate downside deviation (only negative returns)
        $downsideReturns = array_filter($returns, fn($r) => $r < $targetReturn);
        
        if (empty($downsideReturns)) {
            return 0.0;
        }

        $downsideVariance = 0.0;
        foreach ($downsideReturns as $return) {
            $downsideVariance += pow($return - $targetReturn, 2);
        }
        $downsideDeviation = sqrt($downsideVariance / count($downsideReturns));

        if ($downsideDeviation == 0) {
            return 0.0;
        }

        return (($avgReturn - $targetReturn) / $downsideDeviation) * sqrt(365);
    }

    private function calculateMaxDrawdown(array $equityCurve): float
    {
        if (empty($equityCurve)) {
            return 0.0;
        }

        $maxDrawdown = 0.0;
        $peak = $equityCurve[0];

        foreach ($equityCurve as $value) {
            if ($value > $peak) {
                $peak = $value;
            }

            $drawdown = (($peak - $value) / $peak) * 100;
            if ($drawdown > $maxDrawdown) {
                $maxDrawdown = $drawdown;
            }
        }

        return $maxDrawdown;
    }

    private function calculateVolatility(array $returns): float
    {
        if (empty($returns)) {
            return 0.0;
        }

        $mean = array_sum($returns) / count($returns);
        
        $variance = 0.0;
        foreach ($returns as $return) {
            $variance += pow($return - $mean, 2);
        }
        $variance /= count($returns);

        return sqrt($variance) * sqrt(365); // Annualized
    }
}
