<?php

declare(strict_types=1);

namespace App\Alert\Application\Command;

final readonly class CancelPriceAlert
{
    public function __construct(
        public string $alertId,
        public string $userId
    ) {
    }
}
