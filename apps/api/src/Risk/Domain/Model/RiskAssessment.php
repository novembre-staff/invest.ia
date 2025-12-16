<?php

declare(strict_types=1);

namespace App\Risk\Domain\Model;

use App\Identity\Domain\ValueObject\UserId;

/**
 * RiskAssessment - Read model for current risk metrics
 */
class RiskAssessment
{
    public function __construct(
        private UserId $userId,
        private float $currentDrawdown,
        private float $dailyPnL,
        private float $dailyPnLPercent,
        private float $volatility,
        private ?float $sharpeRatio,
        private ?float $valueAtRisk,
        private float $portfolioValue,
        private float $totalExposure,
        private float $leverage,
        private array $metrics, // Additional metrics
        private \DateTimeImmutable $calculatedAt
    ) {
    }

    public function getUserId(): UserId
    {
        return $this->userId;
    }

    public function getCurrentDrawdown(): float
    {
        return $this->currentDrawdown;
    }

    public function getDailyPnL(): float
    {
        return $this->dailyPnL;
    }

    public function getDailyPnLPercent(): float
    {
        return $this->dailyPnLPercent;
    }

    public function getVolatility(): float
    {
        return $this->volatility;
    }

    public function getSharpeRatio(): ?float
    {
        return $this->sharpeRatio;
    }

    public function getValueAtRisk(): ?float
    {
        return $this->valueAtRisk;
    }

    public function getPortfolioValue(): float
    {
        return $this->portfolioValue;
    }

    public function getTotalExposure(): float
    {
        return $this->totalExposure;
    }

    public function getLeverage(): float
    {
        return $this->leverage;
    }

    public function getMetrics(): array
    {
        return $this->metrics;
    }

    public function getCalculatedAt(): \DateTimeImmutable
    {
        return $this->calculatedAt;
    }

    public function isHighRisk(float $maxDrawdown, float $maxDailyLoss): bool
    {
        return $this->currentDrawdown > $maxDrawdown 
            || abs($this->dailyPnLPercent) > $maxDailyLoss;
    }
}
