<?php

declare(strict_types=1);

namespace App\Risk\Application\Handler;

use App\Risk\Application\Command\UpdateRiskLimits;
use App\Risk\Application\DTO\RiskProfileDTO;
use App\Risk\Domain\Repository\RiskProfileRepositoryInterface;
use App\Risk\Domain\ValueObject\RiskProfileId;

final readonly class UpdateRiskLimitsHandler
{
    public function __construct(
        private RiskProfileRepositoryInterface $riskProfileRepository
    ) {
    }

    public function __invoke(UpdateRiskLimits $command): RiskProfileDTO
    {
        $profile = $this->riskProfileRepository->findById(RiskProfileId::fromString($command->profileId));

        if (!$profile) {
            throw new \DomainException('Risk profile not found');
        }

        // Verify ownership
        if ($profile->getUserId()->getValue() !== $command->userId) {
            throw new \DomainException('Unauthorized access to risk profile');
        }

        $profile->updateLimits(
            maxPositionSizePercent: $command->maxPositionSizePercent,
            maxPortfolioExposurePercent: $command->maxPortfolioExposurePercent,
            maxDailyLossPercent: $command->maxDailyLossPercent,
            maxDrawdownPercent: $command->maxDrawdownPercent,
            maxLeverage: $command->maxLeverage,
            maxConcentrationPercent: $command->maxConcentrationPercent,
            maxTradesPerDay: $command->maxTradesPerDay
        );

        $this->riskProfileRepository->save($profile);

        return RiskProfileDTO::fromDomain($profile);
    }
}
