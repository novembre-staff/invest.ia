<?php

declare(strict_types=1);

namespace App\Market\Application\Query;

final readonly class GetDashboardData
{
    public function __construct(
        public string $userId
    ) {
    }
}
