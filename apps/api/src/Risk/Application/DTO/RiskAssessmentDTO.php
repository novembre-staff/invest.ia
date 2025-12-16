<?php

declare(strict_types=1);

namespace App\Risk\Application\DTO;

use App\Risk\Domain\Model\RiskAssessment;

final readonly class RiskAssessmentDTO
{
    public function __construct(
        public string $userId,
        public float $currentDrawdown,
        public float $dailyPnL,
        public float $dailyPnLPercent,
        public float $volatility,
        public ?float $sharpeRatio,
        public ?float $valueAtRisk,
        public float $portfolioValue,
        public float $totalExposure,
        public float $leverage,
        public array $metrics,
        public string $calculatedAt
    ) {
    }

    public static function fromDomain(RiskAssessment $assessment): self
    {
        return new self(
            userId: $assessment->getUserId()->getValue(),
            currentDrawdown: $assessment->getCurrentDrawdown(),
            dailyPnL: $assessment->getDailyPnL(),
            dailyPnLPercent: $assessment->getDailyPnLPercent(),
            volatility: $assessment->getVolatility(),
            sharpeRatio: $assessment->getSharpeRatio(),
            valueAtRisk: $assessment->getValueAtRisk(),
            portfolioValue: $assessment->getPortfolioValue(),
            totalExposure: $assessment->getTotalExposure(),
            leverage: $assessment->getLeverage(),
            metrics: $assessment->getMetrics(),
            calculatedAt: $assessment->getCalculatedAt()->format('c')
        );
    }
}
