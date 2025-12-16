<?php

declare(strict_types=1);

namespace App\Market\Domain\Repository;

use App\Market\Domain\Model\Watchlist;
use App\Market\Domain\ValueObject\WatchlistId;
use App\Identity\Domain\ValueObject\UserId;

interface WatchlistRepositoryInterface
{
    public function save(Watchlist $watchlist): void;

    public function findById(WatchlistId $id): ?Watchlist;

    /**
     * Find all watchlists for a specific user
     * 
     * @return Watchlist[]
     */
    public function findByUserId(UserId $userId): array;

    /**
     * Find a specific watchlist by user and name
     */
    public function findByUserIdAndName(UserId $userId, string $name): ?Watchlist;

    /**
     * Find all watchlists containing a specific symbol
     * 
     * @return Watchlist[]
     */
    public function findBySymbol(string $symbol): array;

    public function delete(Watchlist $watchlist): void;
}
