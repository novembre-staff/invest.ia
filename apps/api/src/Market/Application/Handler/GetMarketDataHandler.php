<?php

declare(strict_types=1);

namespace App\Market\Application\Handler;

use App\Market\Application\DTO\MarketDataDTO;
use App\Market\Application\Query\GetMarketData;
use App\Market\Domain\ValueObject\Symbol;
use App\Market\Infrastructure\Adapter\MarketDataProviderInterface;

final readonly class GetMarketDataHandler
{
    public function __construct(
        private MarketDataProviderInterface $marketDataProvider
    ) {
    }

    public function __invoke(GetMarketData $query): ?MarketDataDTO
    {
        $symbol = Symbol::fromString($query->symbol);
        
        $marketData = $this->marketDataProvider->getMarketData($symbol);

        if ($marketData === null) {
            return null;
        }

        return MarketDataDTO::fromDomain($marketData);
    }
}
