<?php

declare(strict_types=1);

namespace App\Market\Application\Query;

final readonly class GetMarketData
{
    public function __construct(
        public string $symbol
    ) {
    }
}
