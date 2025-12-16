<?php

declare(strict_types=1);

namespace App\Market\Application\Command;

final readonly class RemoveFromWatchlist
{
    public function __construct(
        public string $watchlistId,
        public string $userId,
        public string $symbol
    ) {
    }
}
