<?php

declare(strict_types=1);

namespace App\Analytics\Application\Query;

final readonly class GetPortfolioStatistics
{
    public function __construct(
        public string $userId,
        public string $period = '30d'
    ) {
    }
}
