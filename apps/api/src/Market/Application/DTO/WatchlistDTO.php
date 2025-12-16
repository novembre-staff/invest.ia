<?php

declare(strict_types=1);

namespace App\Market\Application\DTO;

use App\Market\Domain\Model\Watchlist;

final readonly class WatchlistDTO
{
    /**
     * @param string[] $symbols
     */
    private function __construct(
        public string $id,
        public string $name,
        public array $symbols,
        public int $symbolCount,
        public string $createdAt,
        public string $updatedAt
    ) {
    }

    public static function fromDomain(Watchlist $watchlist): self
    {
        $symbols = array_map(
            fn($symbol) => $symbol->getValue(),
            $watchlist->getSymbols()
        );

        return new self(
            id: $watchlist->getId()->getValue(),
            name: $watchlist->getName(),
            symbols: $symbols,
            symbolCount: $watchlist->getSymbolCount(),
            createdAt: $watchlist->getCreatedAt()->format(\DateTimeInterface::ATOM),
            updatedAt: $watchlist->getUpdatedAt()->format(\DateTimeInterface::ATOM)
        );
    }
}
