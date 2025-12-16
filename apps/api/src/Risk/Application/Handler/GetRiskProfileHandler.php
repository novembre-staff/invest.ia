<?php

declare(strict_types=1);

namespace App\Risk\Application\Handler;

use App\Identity\Domain\ValueObject\UserId;
use App\Risk\Application\DTO\RiskProfileDTO;
use App\Risk\Application\Query\GetRiskProfile;
use App\Risk\Domain\Repository\RiskProfileRepositoryInterface;

final readonly class GetRiskProfileHandler
{
    public function __construct(
        private RiskProfileRepositoryInterface $riskProfileRepository
    ) {
    }

    public function __invoke(GetRiskProfile $query): ?RiskProfileDTO
    {
        $profile = $this->riskProfileRepository->findByUserId(UserId::fromString($query->userId));

        if (!$profile) {
            return null;
        }

        return RiskProfileDTO::fromDomain($profile);
    }
}
