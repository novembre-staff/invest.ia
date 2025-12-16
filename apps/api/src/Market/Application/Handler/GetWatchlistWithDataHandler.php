<?php

declare(strict_types=1);

namespace App\Market\Application\Handler;

use App\Market\Application\DTO\MarketDataDTO;
use App\Market\Application\DTO\WatchlistDTO;
use App\Market\Application\Query\GetWatchlistWithData;
use App\Market\Domain\Repository\WatchlistRepositoryInterface;
use App\Market\Domain\ValueObject\WatchlistId;
use App\Market\Infrastructure\Adapter\MarketDataProviderInterface;
use App\Identity\Domain\ValueObject\UserId;

final readonly class GetWatchlistWithDataHandler
{
    public function __construct(
        private WatchlistRepositoryInterface $watchlistRepository,
        private MarketDataProviderInterface $marketDataProvider
    ) {
    }

    /**
     * @return array{watchlist: WatchlistDTO, marketData: MarketDataDTO[]}
     */
    public function __invoke(GetWatchlistWithData $query): array
    {
        $watchlistId = WatchlistId::fromString($query->watchlistId);
        $userId = UserId::fromString($query->userId);

        $watchlist = $this->watchlistRepository->findById($watchlistId);

        if ($watchlist === null) {
            throw new \DomainException('Watchlist not found');
        }

        // Verify ownership
        if (!$watchlist->getUserId()->equals($userId)) {
            throw new \DomainException('Unauthorized access to watchlist');
        }

        // Get market data for all symbols in watchlist
        $symbols = $watchlist->getSymbols();
        $marketDataList = $this->marketDataProvider->getMultipleMarketData($symbols);

        $marketDataDTO = array_map(
            fn($marketData) => MarketDataDTO::fromDomain($marketData),
            $marketDataList
        );

        return [
            'watchlist' => WatchlistDTO::fromDomain($watchlist),
            'marketData' => $marketDataDTO,
        ];
    }
}
