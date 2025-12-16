<?php

declare(strict_types=1);

namespace App\Risk\Infrastructure\Service;

use App\Identity\Domain\ValueObject\UserId;
use App\Portfolio\Infrastructure\Adapter\PortfolioProviderInterface;
use App\Risk\Domain\Model\RiskAssessment;
use App\Risk\Domain\Service\RiskCalculatorInterface;
use Psr\Log\LoggerInterface;

class PortfolioRiskCalculator implements RiskCalculatorInterface
{
    public function __construct(
        private readonly PortfolioProviderInterface $portfolioProvider,
        private readonly LoggerInterface $logger
    ) {
    }

    public function calculateRiskAssessment(UserId $userId): RiskAssessment
    {
        // Get portfolio data
        $portfolio = $this->portfolioProvider->getPortfolio($userId->getValue());
        $balances = $portfolio['balances'] ?? [];
        $totalValue = $portfolio['totalValueUSDT'] ?? 0;

        // Get historical performance
        $returns = $this->calculateHistoricalReturns($userId, 30); // Last 30 days

        // Calculate metrics
        $volatility = $this->calculateVolatility($returns);
        $sharpeRatio = count($returns) > 0 ? $this->calculateSharpeRatio($returns) : null;
        $valueAtRisk = count($returns) > 0 ? $this->calculateVaR($returns) : null;
        $maxDrawdown = $this->calculateCurrentDrawdown($userId);
        $dailyPnL = $this->calculateDailyPnL($userId);
        $dailyPnLPercent = $totalValue > 0 ? ($dailyPnL / $totalValue) * 100 : 0;

        // Calculate exposure
        $totalExposure = $this->calculateTotalExposure($balances, $totalValue);
        $leverage = $totalValue > 0 ? $totalExposure / $totalValue : 1.0;

        return new RiskAssessment(
            userId: $userId,
            currentDrawdown: $maxDrawdown,
            dailyPnL: $dailyPnL,
            dailyPnLPercent: $dailyPnLPercent,
            volatility: $volatility,
            sharpeRatio: $sharpeRatio,
            valueAtRisk: $valueAtRisk,
            portfolioValue: $totalValue,
            totalExposure: $totalExposure,
            leverage: $leverage,
            metrics: [
                'returns_count' => count($returns),
                'positive_days' => count(array_filter($returns, fn($r) => $r > 0)),
                'negative_days' => count(array_filter($returns, fn($r) => $r < 0)),
            ],
            calculatedAt: new \DateTimeImmutable()
        );
    }

    public function calculateVaR(array $returns, float $confidenceLevel = 0.95): float
    {
        if (empty($returns)) {
            return 0.0;
        }

        // Sort returns in ascending order
        sort($returns);

        // Find the VaR at the confidence level
        $index = (int) floor((1 - $confidenceLevel) * count($returns));
        $index = max(0, min($index, count($returns) - 1));

        return abs($returns[$index]);
    }

    public function calculateSharpeRatio(array $returns, float $riskFreeRate = 0.02): float
    {
        if (empty($returns)) {
            return 0.0;
        }

        // Convert annual risk-free rate to daily
        $dailyRiskFreeRate = $riskFreeRate / 365;

        // Calculate excess returns
        $excessReturns = array_map(fn($r) => $r - $dailyRiskFreeRate, $returns);

        // Calculate average excess return
        $avgExcessReturn = array_sum($excessReturns) / count($excessReturns);

        // Calculate standard deviation of excess returns
        $variance = 0;
        foreach ($excessReturns as $return) {
            $variance += pow($return - $avgExcessReturn, 2);
        }
        $stdDev = sqrt($variance / count($excessReturns));

        if ($stdDev == 0) {
            return 0.0;
        }

        // Annualize Sharpe ratio
        return ($avgExcessReturn / $stdDev) * sqrt(365);
    }

    public function calculateVolatility(array $returns): float
    {
        if (empty($returns)) {
            return 0.0;
        }

        $mean = array_sum($returns) / count($returns);

        $variance = 0;
        foreach ($returns as $return) {
            $variance += pow($return - $mean, 2);
        }
        $variance /= count($returns);

        // Annualize volatility
        return sqrt($variance) * sqrt(365);
    }

    public function calculateMaxDrawdown(array $equityCurve): float
    {
        if (empty($equityCurve)) {
            return 0.0;
        }

        $maxDrawdown = 0;
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

    private function calculateHistoricalReturns(UserId $userId, int $days): array
    {
        // TODO: Fetch actual historical portfolio values
        // For now, return empty array
        // In production, query trade history to calculate daily returns
        return [];
    }

    private function calculateCurrentDrawdown(UserId $userId): float
    {
        // TODO: Calculate actual drawdown from peak
        // Requires historical portfolio values
        return 0.0;
    }

    private function calculateDailyPnL(UserId $userId): float
    {
        // TODO: Calculate actual daily P&L
        // Compare current portfolio value to value 24h ago
        return 0.0;
    }

    private function calculateTotalExposure(array $balances, float $portfolioValue): float
    {
        if ($portfolioValue == 0) {
            return 0.0;
        }

        $totalExposure = 0;
        foreach ($balances as $balance) {
            $totalExposure += $balance['valueUSDT'] ?? 0;
        }

        return $totalExposure;
    }
}
