<?php

declare(strict_types=1);

namespace App\Risk\Domain\Event;

use App\Identity\Domain\ValueObject\UserId;
use App\Risk\Domain\ValueObject\RiskProfileId;

final readonly class RiskLimitExceeded
{
    public function __construct(
        public RiskProfileId $profileId,
        public UserId $userId,
        public string $limitType,
        public float $currentValue,
        public float $limitValue,
        public string $details,
        public \DateTimeImmutable $occurredAt
    ) {
    }

    public static function now(
        RiskProfileId $profileId,
        UserId $userId,
        string $limitType,
        float $currentValue,
        float $limitValue,
        string $details
    ): self {
        return new self($profileId, $userId, $limitType, $currentValue, $limitValue, $details, new \DateTimeImmutable());
    }
}
