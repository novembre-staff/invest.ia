<?php

declare(strict_types=1);

namespace App\Alert\Application\Query;

final readonly class GetAlertById
{
    public function __construct(
        public string $alertId,
        public string $userId
    ) {
    }
}
