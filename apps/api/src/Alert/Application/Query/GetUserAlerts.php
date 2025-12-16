<?php

declare(strict_types=1);

namespace App\Alert\Application\Query;

final readonly class GetUserAlerts
{
    public function __construct(
        public string $userId,
        public bool $activeOnly = false
    ) {
    }
}
