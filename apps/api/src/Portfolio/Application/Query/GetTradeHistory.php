<?php

declare(strict_types=1);

namespace App\Portfolio\Application\Query;

final readonly class GetTradeHistory
{
    public function __construct(
        public string $userId,
        public ?string $symbol = null,
        public int $limit = 50
    ) {
    }
}
