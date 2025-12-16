<?php

declare(strict_types=1);

namespace App\Strategy\Application\Query;

final readonly class GetUserStrategies
{
    public function __construct(
        public string $userId,
        public ?bool $activeOnly = false,
        public int $limit = 50
    ) {
    }
}
