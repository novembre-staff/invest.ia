<?php

declare(strict_types=1);

namespace App\Strategy\Application\Query;

final readonly class GetStrategyById
{
    public function __construct(
        public string $strategyId,
        public string $userId
    ) {
    }
}
