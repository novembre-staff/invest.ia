<?php

declare(strict_types=1);

namespace App\Alert\Application\DTO;

use App\Alert\Domain\Model\PriceAlert;

final readonly class PriceAlertDTO
{
    public function __construct(
        public string $id,
        public string $userId,
        public string $type,
        public ?string $symbol,
        public float $targetValue,
        public string $status,
        public array $notificationChannels,
        public ?string $message,
        public ?string $triggeredAt,
        public ?string $expiresAt,
        public string $createdAt
    ) {
    }

    public static function fromDomain(PriceAlert $alert): self
    {
        return new self(
            $alert->getId()->getValue(),
            $alert->getUserId()->getValue(),
            $alert->getType()->value,
            $alert->getSymbol(),
            $alert->getCondition()->getTargetValue(),
            $alert->getStatus()->value,
            array_map(fn($channel) => $channel->value, $alert->getNotificationChannels()),
            $alert->getMessage(),
            $alert->getTriggeredAt()?->format(\DateTimeInterface::ATOM),
            $alert->getExpiresAt()?->format(\DateTimeInterface::ATOM),
            $alert->getCreatedAt()->format(\DateTimeInterface::ATOM)
        );
    }
}
