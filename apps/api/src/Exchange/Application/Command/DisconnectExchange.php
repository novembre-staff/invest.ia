<?php

declare(strict_types=1);

namespace App\Exchange\Application\Command;

final readonly class DisconnectExchange
{
    public function __construct(
        public string $connectionId,
        public string $userId
    ) {
    }
}
