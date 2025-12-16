<?php

declare(strict_types=1);

namespace App\Market\Application\Handler;

use App\Market\Application\DTO\MarketDataDTO;
use App\Market\Application\Query\GetDashboardData;
use App\Market\Infrastructure\Adapter\MarketDataProviderInterface;

final readonly class GetDashboardDataHandler
{
    public function __construct(
        private MarketDataProviderInterface $marketDataProvider
    ) {
    }

    /**
     * @return array{topMarkets: MarketDataDTO[], totalMarkets: int}
     */
    public function __invoke(GetDashboardData $query): array
    {
        // Get top 20 markets by volume
        $topMarkets = $this->marketDataProvider->getTopMarkets(20);

        $topMarketsDTO = array_map(
            fn($marketData) => MarketDataDTO::fromDomain($marketData),
            $topMarkets
        );

        return [
            'topMarkets' => $topMarketsDTO,
            'totalMarkets' => count($topMarketsDTO),
        ];
    }
}
