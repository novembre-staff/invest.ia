<?php

declare(strict_types=1);

namespace App\Trading\Application\Command;

final readonly class CancelOrder
{
    public function __construct(
        public string $orderId,
        public string $userId,
        public string $exchangeConnectionId
    ) {
    }
}
