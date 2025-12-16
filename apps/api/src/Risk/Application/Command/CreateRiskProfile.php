<?php

declare(strict_types=1);

namespace App\Risk\Application\Command;

final readonly class CreateRiskProfile
{
    public function __construct(
        public string $userId,
        public string $riskLevel, // RiskLevel value
        public ?float $maxPositionSizePercent = null,
        public ?float $maxPortfolioExposurePercent = null,
        public ?float $maxDailyLossPercent = null,
        public ?float $maxDrawdownPercent = null,
        public ?float $maxLeverage = 1.0,
        public ?float $maxConcentrationPercent = null,
        public ?int $maxTradesPerDay = null,
        public bool $requireApprovalAboveLimit = true,
        public ?string $notes = null
    ) {
    }
}
