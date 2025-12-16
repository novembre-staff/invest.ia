<?php

declare(strict_types=1);

namespace App\Analytics\Application\Command;

final readonly class GenerateReport
{
    public function __construct(
        public string $userId,
        public string $type,
        public string $period = '30d',
        public ?string $startDate = null,
        public ?string $endDate = null
    ) {
    }
}
