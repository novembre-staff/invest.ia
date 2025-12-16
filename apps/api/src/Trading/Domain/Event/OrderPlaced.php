<?php

declare(strict_types=1);

namespace App\Trading\Domain\Event;

use App\Trading\Domain\ValueObject\OrderId;
use App\Trading\Domain\ValueObject\OrderSide;
use App\Trading\Domain\ValueObject\OrderType;

final readonly class OrderPlaced
{
    public function __construct(
        public OrderId $orderId,
        public string $userId,
        public string $symbol,
        public OrderType $type,
        public OrderSide $side,
        public float $quantity,
        public ?float $price,
        public \DateTimeImmutable $occurredAt
    ) {
    }

    public static function now(
        OrderId $orderId,
        string $userId,
        string $symbol,
        OrderType $type,
        OrderSide $side,
        float $quantity,
        ?float $price
    ): self {
        return new self(
            $orderId,
            $userId,
            $symbol,
            $type,
            $side,
            $quantity,
            $price,
            new \DateTimeImmutable()
        );
    }
}
