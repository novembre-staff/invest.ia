<?php

declare(strict_types=1);

namespace App\Strategy\Application\Command;

final readonly class RunBacktest
{
    public function __construct(
        public string $strategyId,
        public string $userId,
        public string $startDate, // ISO 8601
        public string $endDate, // ISO 8601
        public float $initialCapital = 10000.0
    ) {
    }
}
