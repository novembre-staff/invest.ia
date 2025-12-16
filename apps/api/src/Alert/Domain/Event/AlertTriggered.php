<?php

declare(strict_types=1);

namespace App\Alert\Domain\Event;

use App\Alert\Domain\ValueObject\AlertType;
use App\Alert\Domain\ValueObject\PriceAlertId;
use App\Identity\Domain\ValueObject\UserId;

final readonly class AlertTriggered
{
    public function __construct(
        public PriceAlertId $alertId,
        public UserId $userId,
        public AlertType $type,
        public ?string $symbol,
        public float $triggerValue,
        public float $currentValue,
        public string $message,
        public \DateTimeImmutable $occurredAt
    ) {
    }

    public static function now(
        PriceAlertId $alertId,
        UserId $userId,
        AlertType $type,
        ?string $symbol,
        float $triggerValue,
        float $currentValue,
        string $message
    ): self {
        return new self(
            $alertId,
            $userId,
            $type,
            $symbol,
            $triggerValue,
            $currentValue,
            $message,
            new \DateTimeImmutable()
        );
    }
}
