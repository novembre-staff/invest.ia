<?php

declare(strict_types=1);

namespace App\Market\Application\Handler;

use App\Market\Application\Command\AddToWatchlist;
use App\Market\Domain\Repository\WatchlistRepositoryInterface;
use App\Market\Domain\ValueObject\Symbol;
use App\Market\Domain\ValueObject\WatchlistId;
use App\Identity\Domain\ValueObject\UserId;

final readonly class AddToWatchlistHandler
{
    public function __construct(
        private WatchlistRepositoryInterface $watchlistRepository
    ) {
    }

    public function __invoke(AddToWatchlist $command): void
    {
        $watchlistId = WatchlistId::fromString($command->watchlistId);
        $userId = UserId::fromString($command->userId);
        $symbol = Symbol::fromString($command->symbol);

        $watchlist = $this->watchlistRepository->findById($watchlistId);

        if ($watchlist === null) {
            throw new \DomainException('Watchlist not found');
        }

        // Verify ownership
        if (!$watchlist->getUserId()->equals($userId)) {
            throw new \DomainException('Unauthorized access to watchlist');
        }

        $watchlist->addSymbol($symbol);

        $this->watchlistRepository->save($watchlist);
    }
}
