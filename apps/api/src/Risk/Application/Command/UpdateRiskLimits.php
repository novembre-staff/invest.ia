<?php

declare(strict_types=1);

namespace App\Risk\Application\Command;

final readonly class UpdateRiskLimits
{
    public function __construct(
        public string $profileId,
        public string $userId,
        public ?float $maxPositionSizePercent,
        public ?float $maxPortfolioExposurePercent,
        public ?float $maxDailyLossPercent,
        public ?float $maxDrawdownPercent,
        public ?float $maxLeverage,
        public ?float $maxConcentrationPercent,
        public ?int $maxTradesPerDay
    ) {
    }
}
