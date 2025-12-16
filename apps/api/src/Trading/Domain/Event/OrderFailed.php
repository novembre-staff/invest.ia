<?php

declare(strict_types=1);

namespace App\Trading\Domain\Event;

use App\Trading\Domain\ValueObject\OrderId;

final readonly class OrderFailed
{
    public function __construct(
        public OrderId $orderId,
        public string $symbol,
        public string $userId,
        public string $errorCode,
        public string $errorMessage,
        public \DateTimeImmutable $occurredAt
    ) {
    }

    public static function now(
        OrderId $orderId,
        string $symbol,
        string $userId,
        string $errorCode,
        string $errorMessage
    ): self {
        return new self(
            orderId: $orderId,
            symbol: $symbol,
            userId: $userId,
            errorCode: $errorCode,
            errorMessage: $errorMessage,
            occurredAt: new \DateTimeImmutable()
        );
    }
}
