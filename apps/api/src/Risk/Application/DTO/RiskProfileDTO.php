<?php

declare(strict_types=1);

namespace App\Risk\Application\DTO;

use App\Risk\Domain\Model\RiskProfile;

final readonly class RiskProfileDTO
{
    public function __construct(
        public string $id,
        public string $userId,
        public string $riskLevel,
        public ?float $maxPositionSizePercent,
        public ?float $maxPortfolioExposurePercent,
        public ?float $maxDailyLossPercent,
        public ?float $maxDrawdownPercent,
        public ?float $maxLeverage,
        public ?float $maxConcentrationPercent,
        public ?int $maxTradesPerDay,
        public bool $requireApprovalAboveLimit,
        public ?string $notes,
        public string $createdAt,
        public string $updatedAt
    ) {
    }

    public static function fromDomain(RiskProfile $profile): self
    {
        return new self(
            id: $profile->getId()->getValue(),
            userId: $profile->getUserId()->getValue(),
            riskLevel: $profile->getRiskLevel()->value,
            maxPositionSizePercent: $profile->getMaxPositionSizePercent(),
            maxPortfolioExposurePercent: $profile->getMaxPortfolioExposurePercent(),
            maxDailyLossPercent: $profile->getMaxDailyLossPercent(),
            maxDrawdownPercent: $profile->getMaxDrawdownPercent(),
            maxLeverage: $profile->getMaxLeverage(),
            maxConcentrationPercent: $profile->getMaxConcentrationPercent(),
            maxTradesPerDay: $profile->getMaxTradesPerDay(),
            requireApprovalAboveLimit: $profile->isRequireApprovalAboveLimit(),
            notes: $profile->getNotes(),
            createdAt: $profile->getCreatedAt()->format('c'),
            updatedAt: $profile->getUpdatedAt()->format('c')
        );
    }
}
