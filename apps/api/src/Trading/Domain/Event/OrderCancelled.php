<?php

declare(strict_types=1);

namespace App\Trading\Domain\Event;

use App\Trading\Domain\ValueObject\OrderId;

final readonly class OrderCancelled
{
    public function __construct(
        public OrderId $orderId,
        public string $userId,
        public string $symbol,
        public \DateTimeImmutable $occurredAt
    ) {
    }

    public static function now(
        OrderId $orderId,
        string $userId,
        string $symbol
    ): self {
        return new self(
            $orderId,
            $userId,
            $symbol,
            new \DateTimeImmutable()
        );
    }
}
