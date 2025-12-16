<?php

declare(strict_types=1);

namespace App\Portfolio\Application\Handler;

use App\Exchange\Domain\Repository\ExchangeConnectionRepositoryInterface;
use App\Identity\Domain\ValueObject\UserId;
use App\Portfolio\Application\DTO\PortfolioDTO;
use App\Portfolio\Application\Query\GetPortfolio;
use App\Portfolio\Infrastructure\Adapter\PortfolioProviderInterface;

final readonly class GetPortfolioHandler
{
    public function __construct(
        private ExchangeConnectionRepositoryInterface $exchangeConnectionRepository,
        private PortfolioProviderInterface $portfolioProvider
    ) {
    }

    public function __invoke(GetPortfolio $query): PortfolioDTO
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

        // Fetch portfolio from Binance
        $credentials = $connection->getCredentials();
        $portfolio = $this->portfolioProvider->getPortfolio(
            $connection->getExchangeName(),
            $credentials->getApiKey(),
            $credentials->getApiSecret()
        );

        return PortfolioDTO::fromDomain($portfolio);
    }
}
