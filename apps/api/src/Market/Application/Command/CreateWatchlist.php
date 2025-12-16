<?php

declare(strict_types=1);

namespace App\Market\Application\Command;

final readonly class CreateWatchlist
{
    /**
     * @param string[] $initialSymbols
     */
    public function __construct(
        public string $userId,
        public string $name,
        public array $initialSymbols = []
    ) {
    }
}
