<?php

declare(strict_types=1);

namespace App\Trading\Application\Query;

final readonly class GetUserOrders
{
    public function __construct(
        public string $userId,
        public ?string $symbol = null,
        public bool $activeOnly = false,
        public ?int $limit = 50
    ) {
    }
}
