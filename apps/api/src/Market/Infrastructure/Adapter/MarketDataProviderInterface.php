<?php

declare(strict_types=1);

namespace App\Market\Infrastructure\Adapter;

use App\Market\Domain\Model\MarketData;
use App\Market\Domain\ValueObject\Symbol;

/**
 * Interface for fetching market data from exchanges
 */
interface MarketDataProviderInterface
{
    /**
     * Get current market data for a specific symbol
     */
    public function getMarketData(Symbol $symbol): ?MarketData;

    /**
     * Get market data for multiple symbols
     * 
     * @param Symbol[] $symbols
     * @return MarketData[]
     */
    public function getMultipleMarketData(array $symbols): array;

    /**
     * Get top traded symbols by volume
     * 
     * @return MarketData[]
     */
    public function getTopMarkets(int $limit = 20): array;

    /**
     * Search symbols by name or base asset
     * 
     * @return Symbol[]
     */
    public function searchSymbols(string $query): array;
}
