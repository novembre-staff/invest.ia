<?php

declare(strict_types=1);

namespace App\Exchange\Application\Command;

final readonly class ConnectExchange
{
    public function __construct(
        public string $userId,
        public string $exchangeName,
        public string $apiKey,
        public string $apiSecret,
        public ?string $label = null
    ) {
    }
}
