<?php

declare(strict_types=1);

namespace App\Market\Application\Handler;

use App\Market\Application\Command\CreateWatchlist;
use App\Market\Application\DTO\WatchlistDTO;
use App\Market\Domain\Model\Watchlist;
use App\Market\Domain\Repository\WatchlistRepositoryInterface;
use App\Market\Domain\ValueObject\Symbol;
use App\Market\Domain\ValueObject\WatchlistId;
use App\Identity\Domain\ValueObject\UserId;

final readonly class CreateWatchlistHandler
{
    public function __construct(
        private WatchlistRepositoryInterface $watchlistRepository
    ) {
    }

    public function __invoke(CreateWatchlist $command): WatchlistDTO
    {
        $userId = UserId::fromString($command->userId);

        // Check if watchlist with same name already exists
        $existing = $this->watchlistRepository->findByUserIdAndName($userId, $command->name);
        if ($existing !== null) {
            throw new \DomainException('Watchlist with this name already exists');
        }

        // Convert symbol strings to Symbol ValueObjects
        $symbols = array_map(
            fn(string $symbolString) => Symbol::fromString($symbolString),
            $command->initialSymbols
        );

        $watchlist = new Watchlist(
            id: WatchlistId::generate(),
            userId: $userId,
            name: $command->name,
            initialSymbols: $symbols
        );

        $this->watchlistRepository->save($watchlist);

        return WatchlistDTO::fromDomain($watchlist);
    }
}
