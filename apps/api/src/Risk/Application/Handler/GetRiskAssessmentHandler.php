<?php

declare(strict_types=1);

namespace App\Risk\Application\Handler;

use App\Identity\Domain\ValueObject\UserId;
use App\Risk\Application\DTO\RiskAssessmentDTO;
use App\Risk\Application\Query\GetRiskAssessment;
use App\Risk\Domain\Service\RiskCalculatorInterface;

final readonly class GetRiskAssessmentHandler
{
    public function __construct(
        private RiskCalculatorInterface $riskCalculator
    ) {
    }

    public function __invoke(GetRiskAssessment $query): RiskAssessmentDTO
    {
        $assessment = $this->riskCalculator->calculateRiskAssessment(
            UserId::fromString($query->userId)
        );

        return RiskAssessmentDTO::fromDomain($assessment);
    }
}
