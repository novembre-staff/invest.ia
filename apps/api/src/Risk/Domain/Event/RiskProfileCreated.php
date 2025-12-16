<?php

declare(strict_types=1);

namespace App\Risk\Domain\Event;

use App\Identity\Domain\ValueObject\UserId;
use App\Risk\Domain\ValueObject\RiskProfileId;
use App\Risk\Domain\ValueObject\RiskLevel;

final readonly class RiskProfileCreated
{
    public function __construct(
        public RiskProfileId $profileId,
        public UserId $userId,
        public RiskLevel $riskLevel,
        public \DateTimeImmutable $occurredAt
    ) {
    }

    public static function now(
        RiskProfileId $profileId,
        UserId $userId,
        RiskLevel $riskLevel
    ): self {
        return new self($profileId, $userId, $riskLevel, new \DateTimeImmutable());
    }
}
