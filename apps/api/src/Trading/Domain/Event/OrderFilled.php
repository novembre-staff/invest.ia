<?php

declare(strict_types=1);

namespace App\Trading\Domain\Event;

use App\Trading\Domain\ValueObject\OrderId;

final readonly class OrderFilled
{
    public function __construct(
        public OrderId $orderId,
        public string $userId,
        public string $symbol,
        public float $executedQuantity,
        public float $averagePrice,
        public float $totalValue,
        public \DateTimeImmutable $occurredAt
    ) {
    }

    public static function now(
        OrderId $orderId,
        string $userId,
        string $symbol,
        float $executedQuantity,
        float $averagePrice,
        float $totalValue
    ): self {
        return new self(
            $orderId,
            $userId,
            $symbol,
            $executedQuantity,
            $averagePrice,
            $totalValue,
            new \DateTimeImmutable()
        );
    }
}
