<?php

declare(strict_types=1);

namespace App\Risk\Application\Handler;

use App\Identity\Domain\ValueObject\UserId;
use App\Risk\Application\Command\CreateRiskProfile;
use App\Risk\Application\DTO\RiskProfileDTO;
use App\Risk\Domain\Event\RiskProfileCreated;
use App\Risk\Domain\Model\RiskProfile;
use App\Risk\Domain\Repository\RiskProfileRepositoryInterface;
use App\Risk\Domain\ValueObject\RiskLevel;
use App\Shared\Application\MessageBusInterface;

final readonly class CreateRiskProfileHandler
{
    public function __construct(
        private RiskProfileRepositoryInterface $riskProfileRepository,
        private MessageBusInterface $messageBus
    ) {
    }

    public function __invoke(CreateRiskProfile $command): RiskProfileDTO
    {
        $userId = UserId::fromString($command->userId);

        // Check if profile already exists
        $existingProfile = $this->riskProfileRepository->findByUserId($userId);
        if ($existingProfile) {
            throw new \DomainException('Risk profile already exists for this user');
        }

        $riskLevel = RiskLevel::from($command->riskLevel);

        $profile = RiskProfile::create(
            userId: $userId,
            riskLevel: $riskLevel,
            maxPositionSizePercent: $command->maxPositionSizePercent,
            maxPortfolioExposurePercent: $command->maxPortfolioExposurePercent,
            maxDailyLossPercent: $command->maxDailyLossPercent,
            maxDrawdownPercent: $command->maxDrawdownPercent,
            maxLeverage: $command->maxLeverage,
            maxConcentrationPercent: $command->maxConcentrationPercent,
            maxTradesPerDay: $command->maxTradesPerDay,
            requireApprovalAboveLimit: $command->requireApprovalAboveLimit,
            notes: $command->notes
        );

        $this->riskProfileRepository->save($profile);

        $this->messageBus->dispatch(RiskProfileCreated::now(
            $profile->getId(),
            $profile->getUserId(),
            $profile->getRiskLevel()
        ));

        return RiskProfileDTO::fromDomain($profile);
    }
}
