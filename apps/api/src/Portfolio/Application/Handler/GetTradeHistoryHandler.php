<?php

declare(strict_types=1);

namespace App\Portfolio\Application\Handler;

use App\Exchange\Domain\Repository\ExchangeConnectionRepositoryInterface;
use App\Identity\Domain\ValueObject\UserId;
use App\Portfolio\Application\DTO\TradeDTO;
use App\Portfolio\Application\Query\GetTradeHistory;
use App\Portfolio\Infrastructure\Adapter\PortfolioProviderInterface;

final readonly class GetTradeHistoryHandler
{
    public function __construct(
        private ExchangeConnectionRepositoryInterface $exchangeConnectionRepository,
        private PortfolioProviderInterface $portfolioProvider
    ) {
    }

    /**
     * @return TradeDTO[]
     */
    public function __invoke(GetTradeHistory $query): array
    {
        $userId = UserId::fromString($query->userId);

        // Find active Binance connection for user
        $connection = $this->exchangeConnectionRepository->findByUserIdAndExchangeName(
            $userId,
            'binance'
        );

        if ($connection === null) {
            throw new \DomainException('No active exchange connection found');
        }

        $credentials = $connection->getCredentials();

        // Fetch trades
        if ($query->symbol !== null) {
            // Get trades for specific symbol
            $trades = $this->portfolioProvider->getTradeHistory(
                $connection->getExchangeName(),
                $credentials->getApiKey(),
                $credentials->getApiSecret(),
                $query->symbol,
                $query->limit
            );
        } else {
            // Get all recent trades
            $trades = $this->portfolioProvider->getAllRecentTrades(
                $connection->getExchangeName(),
                $credentials->getApiKey(),
                $credentials->getApiSecret(),
                $query->limit
            );
        }

        return array_map(
            fn($trade) => TradeDTO::fromDomain($trade),
            $trades
        );
    }
}
