<?php

declare(strict_types=1);

namespace App\Portfolio\Application\Query;

final readonly class GetPortfolio
{
    public function __construct(
        public string $userId
    ) {
    }
}
