<?php

declare(strict_types=1);

namespace App\Analytics\Application\Query;

final readonly class GetPerformanceReport
{
    public function __construct(
        public string $reportId,
        public string $userId
    ) {
    }
}
