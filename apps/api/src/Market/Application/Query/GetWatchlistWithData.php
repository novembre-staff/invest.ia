<?php

declare(strict_types=1);

namespace App\Market\Application\Query;

final readonly class GetWatchlistWithData
{
    public function __construct(
        public string $watchlistId,
        public string $userId
    ) {
    }
}
