<?php

declare(strict_types=1);

namespace App\Trading\Application\Query;

final readonly class GetOrderById
{
    public function __construct(
        public string $orderId,
        public string $userId
    ) {
    }
}
