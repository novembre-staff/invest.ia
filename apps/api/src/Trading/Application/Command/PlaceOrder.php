<?php

declare(strict_types=1);

namespace App\Trading\Application\Command;

final readonly class PlaceOrder
{
    public function __construct(
        public string $userId,
        public string $exchangeConnectionId,
        public string $symbol,
        public string $type,
        public string $side,
        public float $quantity,
        public ?float $price = null,
        public ?float $stopPrice = null,
        public string $timeInForce = 'GTC'
    ) {
    }
}
