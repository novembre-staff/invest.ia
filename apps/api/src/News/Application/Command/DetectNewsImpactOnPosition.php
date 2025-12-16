<?php

declare(strict_types=1);

namespace App\News\Application\Command;

final readonly class DetectNewsImpactOnPosition
{
    public function __construct(
        public string $positionId,
        public string $symbol,
        public int $hoursBack = 24
    ) {
    }
}
